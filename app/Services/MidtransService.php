<?php

namespace App\Services;

use App\Services\Interfaces\MidtransServiceInterface;
use App\Repositories\Interfaces\ProductRepositoryInterface;
use App\Repositories\Interfaces\TransactionRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Midtrans\Config;
use Midtrans\Snap;
use Midtrans\Notification;

class MidtransService implements MidtransServiceInterface
{
    protected $productRepository;
    protected $transactionRepository;

    public function __construct(
        ProductRepositoryInterface $productRepository,
        TransactionRepositoryInterface $transactionRepository
    ) {
        $this->productRepository = $productRepository;
        $this->transactionRepository = $transactionRepository;

        // Set Midtrans configuration
        Config::$serverKey = config('midtrans.server_key');
        Config::$isProduction = config('midtrans.is_production');
        Config::$isSanitized = config('midtrans.is_sanitized');
        Config::$is3ds = config('midtrans.is_3ds');
    }

    public function processMidtransPayment(Request $request)
    {
        // Ambil data checkout dari session
        $checkoutItems = session('checkout_items', []);
        $subtotal = session('checkout_subtotal', 0);

        if (empty($checkoutItems)) {
            return [
                'status' => false,
                'message' => 'Tidak ada item untuk checkout'
            ];
        }

        // Ganti dengan:
        $grandTotal = $subtotal;

        // Buat transaksi dengan status pending
        $transactions = [];
        $orderIds = [];

        DB::transaction(function() use ($request, $checkoutItems, &$transactions, &$orderIds) {
            foreach ($checkoutItems as $productId => $item) {
                $product = $this->productRepository->find($productId);

                if (!$product || $product->stock < $item['quantity']) {
                    continue;
                }

                // Tanggal mulai dan selesai
                $startedDate = Carbon::parse($request->started_at);
                $days = $item['days'];
                $endedDate = $startedDate->copy()->addDays($days);

                // Buat trx_id unik
                $trxId = $this->transactionRepository->generateUniqueTrxId();
                $orderIds[] = $trxId;

                // Hitung biaya per item
                $periods = ceil($days / 3);
                $subtotal = $product->price * $periods * $item['quantity'];
                $totalAmount = $subtotal;

                // Simpan transaksi dengan status pending
                $transaction = $this->transactionRepository->create([
                    'user_id' => auth()->id(),
                    'name' => $request->name,
                    'phone_number' => $request->phone_number,
                    'trx_id' => $trxId,
                    'address' => $request->delivery_type === 'delivery' ? $request->address : 'Pickup di toko',
                    'total_amount' => $totalAmount,
                    'duration' => $days,
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'store_id' => $request->store_id,
                    'started_at' => $startedDate,
                    'ended_at' => $endedDate,
                    'delivery_type' => $request->delivery_type,
                    'proof' => null, // Tidak perlu bukti untuk midtrans
                    'payment_method' => 'midtrans',
                    'is_paid' => false,
                    'status' => 'menunggu pembayaran'
                ]);

                $transactions[] = $transaction;
            }
        });

        if (empty($transactions)) {
            return [
                'status' => false,
                'message' => 'Gagal membuat transaksi'
            ];
        }

        // Prepare customer details
        $customerDetails = [
            'first_name' => $request->name,
            'email' => auth()->user()->email,
            'phone' => $request->phone_number,
        ];

        // Prepare transaction details
        $params = [
            'transaction_details' => [
                'order_id' => implode('-', $orderIds),
                'gross_amount' => (int)$grandTotal,
            ],
            'customer_details' => $customerDetails,
            'callbacks' => [
                'finish' => route('checkout.success', $transactions[0]->id),
            ]
        ];

        try {
            // Get Snap Token
            $snapToken = $this->generateSnapToken($params);

            // Simpan token di session
            session(['snap_token' => $snapToken]);
            session(['midtrans_order_id' => implode('-', $orderIds)]);

            return [
                'status' => true,
                'data' => [
                    'snap_token' => $snapToken,
                    'success_url' => route('checkout.success', $transactions[0]->id),
                    'pending_url' => route('checkout.pending', $transactions[0]->id),
                ]
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'message' => 'Error: ' . $e->getMessage()
            ];
        }
    }

    public function handleMidtransCallback(Request $request)
    {
        try {
            $notification = new Notification();

            $orderId = $notification->order_id;
            $statusCode = $notification->status_code;
            $grossAmount = $notification->gross_amount;
            $serverKey = config('midtrans.server_key');
            $transactionStatus = $notification->transaction_status;
            $type = $notification->payment_type;
            $fraudStatus = !empty($notification->fraud_status) ? $notification->fraud_status : null;

            // Memisahkan order_id menjadi array trx_id
            $trxIds = explode('-', $orderId);

            // Log untuk debugging
            Log::info('Midtrans Notification: ' . json_encode($notification));

            // Handle status pembayaran
            if ($transactionStatus == 'capture') {
                if ($fraudStatus == 'challenge') {
                    $status = 'challenge';
                } else if ($fraudStatus == 'accept') {
                    $status = 'success';
                }
            } else if ($transactionStatus == 'settlement') {
                $status = 'success';
            } else if (in_array($transactionStatus, ['cancel', 'deny', 'expire'])) {
                $status = 'failure';
            } else if ($transactionStatus == 'pending') {
                $status = 'pending';
            }

            // Update status transaksi di database
            foreach ($trxIds as $trxId) {
                $transaction = $this->transactionRepository->updatePaymentStatus(
                    $trxId,
                    $status,
                    $status == 'success'
                );

                if ($transaction && $status == 'success') {
                    // Kurangi stok jika belum
                    $product = $this->productRepository->find($transaction->product_id);
                    if ($product) {
                        $this->productRepository->decreaseStock($product, $transaction->quantity);
                    }
                }
            }

            return [
                'status' => true
            ];
        } catch (\Exception $e) {
            Log::error('Midtrans Callback Error: ' . $e->getMessage());
            return [
                'status' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    public function generateSnapToken(array $params)
    {
        return Snap::getSnapToken($params);
    }
}