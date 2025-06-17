<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\Interfaces\CheckoutServiceInterface;
use App\Services\Interfaces\MidtransServiceInterface;
use App\Repositories\Interfaces\StoreRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Transaction;

class CheckoutController extends Controller
{
    protected $checkoutService;
    protected $midtransService;
    protected $storeRepository;

    public function __construct(
        CheckoutServiceInterface $checkoutService,
        MidtransServiceInterface $midtransService,
        StoreRepositoryInterface $storeRepository
    ) {
        $this->checkoutService = $checkoutService;
        $this->midtransService = $midtransService;
        $this->storeRepository = $storeRepository;
    }

    /**
     * Tampilkan halaman checkout dari keranjang atau direct checkout
     */
    public function index(Request $request)
    {
        // Cek auth
        if (!auth()->check()) {
            return redirect()->route('login')
                ->with('error', 'Silakan login terlebih dahulu untuk melanjutkan checkout');
        }

        // Jika request berasal dari direct checkout, gunakan checkout_items yang sudah ada
        if ($request->has('from_direct')) {
            // Pastikan checkout_items sudah ada di session
            if (!session()->has('checkout_items') || empty(session('checkout_items'))) {
                return redirect()->route('front.index')
                    ->with('error', 'Tidak ada item untuk checkout');
            }

            // Gunakan checkout_items yang sudah diatur oleh metode direct()
            $items = session('checkout_items');
            $totals = $this->checkoutService->calculateTotals($items);
            $stores = $this->storeRepository->all();

            return view('front.checkout', compact(
                'items',
                'stores'
            ))->with($totals);
        }

        // Jika bukan dari direct checkout, ambil dari cart seperti biasa
        $cart = session('cart', []);
        $result = $this->checkoutService->prepareCheckout($cart);

        if (!$result['status']) {
            return redirect()->route('cart.index')
                ->with('error', $result['message']);
        }

        $data = $result['data'];
        return view('front.checkout', $data);
    }

    /**
     * Proses checkout
     */
    public function store(Request $request)
    {
        // Validasi data
        $validationRules = [
            'name' => 'required|string|max:255',
            'phone_number' => 'required|numeric',
            'store_id' => 'required|exists:stores,id',
            'delivery_type' => 'required|in:pickup,delivery',
            'address' => 'required_if:delivery_type,delivery',
            'started_at' => 'required|date|after_or_equal:today',
            'payment_method' => 'required|in:manual,midtrans',
        ];

        // Tambahkan validasi file hanya jika metode pembayaran manual
        if ($request->payment_method === 'manual') {
            $validationRules['proof'] = 'required|image|max:5120'; // max 5MB
            $validationRules['confirm'] = 'required';
        }

        $request->validate($validationRules);

        $result = $this->checkoutService->processCheckout($request);

        if (!$result['status']) {
            return redirect()->route('cart.index')
                ->with('error', $result['message'] ?? 'Terjadi kesalahan saat checkout. Silakan coba lagi.');
        }

        // Redirect ke halaman sukses
        return redirect()->route('front.success.booking', $result['transaction_id'])
            ->with('success', 'Pesanan berhasil dibuat. Terima kasih telah berbelanja!');
    }

    /**
     * Handle success payment
     */
    public function success($id, $group_id = null)
    {
        // Dapatkan semua transaksi dalam group yang sama jika group_id disediakan
        if ($group_id) {
            $transactions = Transaction::where('transaction_group_id', $group_id)->get();

            // Update semua transaksi dalam group
            foreach ($transactions as $transaction) {
                if ($transaction->payment_method === 'midtrans' && !$transaction->is_paid) {
                    // Update transaksi
                    $transaction->update([
                        'is_paid' => true,
                        'status' => 'selesai'
                    ]);

                    // Kurangi stok produk
                    $product = $transaction->product;
                    if ($product && $product->stock >= $transaction->quantity) {
                        $product->decrement('stock', $transaction->quantity);
                    }
                }
            }

            // Ambil transaksi pertama untuk ditampilkan di halaman sukses
            $transaction = $transactions->first();
        } else {
            // Jika tidak ada group_id, ambil transaksi tunggal
            $transaction = $this->checkoutService->getTransaction($id);

            if ($transaction->payment_method === 'midtrans' && !$transaction->is_paid) {
                // Update transaksi
                $transaction->update([
                    'is_paid' => true,
                    'status' => 'selesai'
                ]);

                // Kurangi stok produk
                $product = $transaction->product;
                if ($product && $product->stock >= $transaction->quantity) {
                    $product->decrement('stock', $transaction->quantity);
                }
            }
        }

        // Hapus session checkout
        session()->forget(['checkout_items', 'checkout_subtotal', 'snap_token', 'midtrans_order_id', 'transaction_group_id']);

        // Redirect ke halaman success booking
        return view('front.success_booking', compact('transaction'));
    }

    /**
     * Batalkan checkout (hapus session checkout dan kembali ke cart)
     */
    public function cancel()
    {
        session()->forget(['checkout_items', 'checkout_subtotal']);
        return redirect()->route('cart.index')->with('info', 'Checkout dibatalkan');
    }

    /**
     * Direct checkout from product detail page
     */
    public function direct(Product $product)
    {
        // Cek auth
        if (!auth()->check()) {
            return redirect()->route('login')
                ->with('error', 'Silakan login terlebih dahulu untuk melanjutkan checkout');
        }

        $result = $this->checkoutService->prepareDirectCheckout($product);

        if (!$result['status']) {
            return redirect()->route('front.details', $product->slug)
                ->with('error', $result['message']);
        }

        // Redirect ke halaman checkout dengan parameter yang jelas
        return redirect()->route('checkout.index', [
            'from_direct' => 'true',
            'product_slug' => $result['data']['product_slug'],
            'ts' => time() // Tambahkan timestamp untuk menghindari cache
        ]);
    }

    public function updateItem(Request $request, $productId)
    {
        // Validasi request
        $request->validate([
            'quantity' => 'sometimes|integer|min:1',
            'days' => 'sometimes|integer|min:3|max:30'
        ]);

        $result = $this->checkoutService->updateCheckoutItem($request, $productId);

        return response()->json($result['status'] ? $result['data'] : [
            'success' => false,
            'message' => $result['message']
        ]);
    }

    public function removeItem($productId)
    {
        $result = $this->checkoutService->removeCheckoutItem($productId);

        return response()->json($result['status'] ? [
            'success' => true,
            'message' => $result['message'],
            'itemCount' => $result['data']['itemCount'],
            'total' => $result['data']['total'],
        ] : [
            'success' => false,
            'message' => $result['message']
        ]);
    }

    public function processMidtrans(Request $request)
    {
        // Validasi request
        $request->validate([
            'name' => 'required|string',
            'phone_number' => 'required|numeric',
            'store_id' => 'required|exists:stores,id',
            'started_at' => 'required|date|after_or_equal:today',
            'delivery_type' => 'required|in:pickup,delivery',
            'address' => 'required_if:delivery_type,delivery',
        ]);

        // Ambil data checkout dari session
        $checkoutItems = session('checkout_items', []);
        $subtotal = session('checkout_subtotal', 0);

        if (empty($checkoutItems)) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada item untuk checkout'
            ]);
        }

        // Hitung total tanpa PPN dan asuransi
        $grandTotal = $subtotal;

        // Buat Transaction Group ID unik untuk semua transaksi dalam checkout ini
        $transactionGroupId = 'GRP-' . uniqid();

        // Buat transaksi dengan status pending
        $transactions = [];
        $orderIds = [];

        DB::transaction(function() use ($request, $checkoutItems, $transactionGroupId, &$transactions, &$orderIds) {
            foreach ($checkoutItems as $productId => $item) {
                $product = Product::find($productId);

                if (!$product || $product->stock < $item['quantity']) {
                    continue;
                }

                // Tanggal mulai dan selesai
                $startedDate = Carbon::parse($request->started_at);
                $days = $item['days'];
                $endedDate = $startedDate->copy()->addDays($days);

                // Buat trx_id unik
                $trxId = Transaction::generateUniqueTrxId();
                $orderIds[] = $trxId;

                // Hitung biaya per item
                $periods = ceil($days / 3);
                $subtotal = $product->price * $periods * $item['quantity'];
                $totalAmount = $subtotal;

                // Simpan transaksi dengan status menunggu pembayaran
                $transaction = Transaction::create([
                    'user_id' => auth()->id(),
                    'name' => $request->name,
                    'phone_number' => $request->phone_number,
                    'trx_id' => $trxId,
                    'transaction_group_id' => $transactionGroupId, // Gunakan group ID yang sama untuk semua transaksi
                    'address' => $request->delivery_type === 'delivery' ? $request->address : 'Pickup di toko',
                    'total_amount' => $totalAmount,
                    'duration' => $days,
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'store_id' => $request->store_id,
                    'started_at' => $startedDate,
                    'ended_at' => $endedDate,
                    'delivery_type' => $request->delivery_type,
                    'proof' => null,
                    'payment_method' => 'midtrans',
                    'is_paid' => false,
                    'status' => 'menunggu pembayaran'
                ]);

                $transactions[] = $transaction;
            }
        });

        if (empty($transactions)) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat transaksi'
            ]);
        }

        // Setup midtrans configuration
        \Midtrans\Config::$serverKey = config('midtrans.server_key');
        \Midtrans\Config::$isProduction = config('midtrans.is_production');
        \Midtrans\Config::$isSanitized = config('midtrans.is_sanitized');
        \Midtrans\Config::$is3ds = config('midtrans.is_3ds');

        // Prepare customer details
        $customerDetails = [
            'first_name' => $request->name,
            'email' => auth()->user()->email,
            'phone' => $request->phone_number,
        ];

        // Prepare transaction details - tambahkan transaction_group_id ke dalam order_id
        $orderId = $transactionGroupId; // Gunakan group ID sebagai order ID Midtrans
        $params = [
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => (int)$grandTotal,
            ],
            'customer_details' => $customerDetails,
            'callbacks' => [
                'finish' => route('checkout.success', ['id' => $transactions[0]->id, 'group_id' => $transactionGroupId]),
            ]
        ];

        try {
            // Get Snap Token
            $snapToken = \Midtrans\Snap::getSnapToken($params);

            // Simpan token di session beserta group ID
            session(['snap_token' => $snapToken]);
            session(['midtrans_order_id' => $orderId]);
            session(['transaction_group_id' => $transactionGroupId]);

            return response()->json([
                'success' => true,
                'snap_token' => $snapToken,
                'success_url' => route('checkout.success', ['id' => $transactions[0]->id, 'group_id' => $transactionGroupId]),
                'pending_url' => route('checkout.pending', ['id' => $transactions[0]->id, 'group_id' => $transactionGroupId]),
            ]);
        } catch (\Exception $e) {
            \Log::error('Midtrans Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Handle Midtrans notification callback
     */
    public function midtransCallback(Request $request)
    {
        $serverKey = config('midtrans.server_key');
        $hashed = hash("sha512", $request->order_id . $request->status_code . $request->gross_amount . $serverKey);

        if ($hashed != $request->signature_key) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid signature'
            ], 400);
        }

        // Proses callback
        $orderId = $request->order_id;
        $transactionStatus = $request->transaction_status;
        $fraudStatus = $request->fraud_status;

        // Cari semua transaksi dengan transaction_group_id yang sesuai
        // Perhatikan bahwa kita menggunakan order_id dari Midtrans sebagai transaction_group_id
        $transactions = Transaction::where('transaction_group_id', $orderId)->get();

        if ($transactions->isEmpty()) {
            // Coba cara lama (untuk backward compatibility)
            $trxIds = explode('-', $orderId);
            $oldTransactions = [];

            foreach ($trxIds as $trxId) {
                if (strpos($trxId, 'TRX') === 0) {
                    $transaction = Transaction::where('trx_id', $trxId)->first();
                    if ($transaction) {
                        $oldTransactions[] = $transaction;
                    }
                }
            }

            if (!empty($oldTransactions)) {
                $transactions = collect($oldTransactions);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Transaction not found'
                ], 404);
            }
        }

        // Handle based on transaction status
        if ($transactionStatus == 'capture') {
            if ($fraudStatus == 'challenge') {
                // Set transaksi sebagai pending
                foreach ($transactions as $transaction) {
                    $transaction->update([
                        'status' => 'menunggu pembayaran'
                    ]);
                }
            } else if ($fraudStatus == 'accept') {
                // Set transaksi sebagai selesai & paid
                foreach ($transactions as $transaction) {
                    // Update status
                    $transaction->update([
                        'is_paid' => true,
                        'status' => 'selesai'
                    ]);

                    // Kurangi stok
                    $product = $transaction->product;
                    if ($product && $product->stock >= $transaction->quantity) {
                        $product->decrement('stock', $transaction->quantity);
                    }
                }
            }
        } else if ($transactionStatus == 'settlement') {
            // Set transaksi sebagai selesai & paid
            foreach ($transactions as $transaction) {
                // Update status
                $transaction->update([
                    'is_paid' => true,
                    'status' => 'selesai'
                ]);

                // Kurangi stok
                $product = $transaction->product;
                if ($product && $product->stock >= $transaction->quantity) {
                    $product->decrement('stock', $transaction->quantity);
                }
            }
        } else if ($transactionStatus == 'pending') {
            // Set transaksi sebagai pending
            foreach ($transactions as $transaction) {
                $transaction->update([
                    'status' => 'menunggu pembayaran'
                ]);
            }
        } else if (in_array($transactionStatus, ['deny', 'expire', 'cancel'])) {
            // Set transaksi sebagai dibatalkan
            foreach ($transactions as $transaction) {
                $transaction->update([
                    'status' => 'dibatalkan'
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Notification processed successfully'
        ]);
    }

    /**
     * Handle pending payment
     */
    public function pending($id, $group_id = null)
    {
        // Jika ada group_id, ambil semua transaksi dalam group
        if ($group_id) {
            $transactions = Transaction::where('transaction_group_id', $group_id)->get();

            // Update status semua transaksi menjadi menunggu pembayaran
            foreach ($transactions as $transaction) {
                if ($transaction->status !== 'menunggu pembayaran') {
                    $transaction->update(['status' => 'menunggu pembayaran']);
                }
            }

            // Ambil transaksi pertama untuk ditampilkan
            $transaction = $transactions->first();
        } else {
            // Jika tidak ada group_id, ambil transaksi tunggal
            $transaction = $this->checkoutService->getTransaction($id);

            // Verifikasi bahwa transaksi memang dalam status pending
            if ($transaction->status !== 'menunggu pembayaran') {
                $transaction->update(['status' => 'menunggu pembayaran']);
            }
        }

        return view('front.transaction_pending', compact('transaction'));
    }

    /**
     * Re-open Midtrans payment for unpaid transactions
     */
    public function repayMidtrans(Transaction $transaction)
    {
        // Pastikan ini adalah transaksi milik user yang login
        if ($transaction->user_id !== auth()->id()) {
            abort(403, 'Unauthorized');
        }

        // Pastikan transaksi dalam status menunggu pembayaran dan belum dibayar
        if ($transaction->status !== 'menunggu pembayaran' || $transaction->is_paid) {
            return redirect()->route('filament.customer.resources.transactions.index')
                ->with('error', 'Transaksi ini tidak dapat dibayar ulang');
        }

        // Setup midtrans configuration
        \Midtrans\Config::$serverKey = config('midtrans.server_key');
        \Midtrans\Config::$isProduction = config('midtrans.is_production');
        \Midtrans\Config::$isSanitized = config('midtrans.is_sanitized');
        \Midtrans\Config::$is3ds = config('midtrans.is_3ds');

        // Get all transactions in the same group if exists
        $transactions = [$transaction];
        $totalAmount = $transaction->total_amount;

        if ($transaction->transaction_group_id) {
            $groupTransactions = Transaction::where('transaction_group_id', $transaction->transaction_group_id)
                ->where('status', 'menunggu pembayaran')
                ->where('is_paid', false)
                ->get();

            if ($groupTransactions->count() > 1) {
                $transactions = $groupTransactions->all();
                $totalAmount = $groupTransactions->sum('total_amount');
            }
        }

        // Prepare customer details
        $customerDetails = [
            'first_name' => $transaction->name,
            'email' => auth()->user()->email,
            'phone' => $transaction->phone_number,
        ];

        // Prepare transaction details
        $orderId = $transaction->transaction_group_id ?? $transaction->trx_id;
        $params = [
            'transaction_details' => [
                'order_id' => $orderId . '-' . time(), // Add timestamp to make it unique
                'gross_amount' => (int)$totalAmount,
            ],
            'customer_details' => $customerDetails,
            'callbacks' => [
                'finish' => route('checkout.success', ['id' => $transaction->id, 'group_id' => $transaction->transaction_group_id]),
            ]
        ];

        try {
            // Get Snap Token
            $snapToken = \Midtrans\Snap::getSnapToken($params);

            // Render halaman Midtrans Payment
            return view('front.repay_midtrans', [
                'snap_token' => $snapToken,
                'transaction' => $transaction,
                'total_amount' => $totalAmount,
                'success_url' => route('checkout.success', ['id' => $transaction->id, 'group_id' => $transaction->transaction_group_id]),
                'pending_url' => route('checkout.pending', ['id' => $transaction->id, 'group_id' => $transaction->transaction_group_id]),
            ]);
        } catch (\Exception $e) {
            \Log::error('Midtrans Error: ' . $e->getMessage());
            return redirect()->route('filament.customer.resources.transactions.index')
                ->with('error', 'Gagal memproses pembayaran: ' . $e->getMessage());
        }
    }
}