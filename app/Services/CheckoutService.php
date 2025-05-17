<?php

namespace App\Services;

use App\Services\Interfaces\CheckoutServiceInterface;
use App\Repositories\Interfaces\ProductRepositoryInterface;
use App\Repositories\Interfaces\TransactionRepositoryInterface;
use App\Repositories\Interfaces\StoreRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\Transaction;
use App\Models\Product;

class CheckoutService implements CheckoutServiceInterface
{
    protected $productRepository;
    protected $transactionRepository;
    protected $storeRepository;

    public function __construct(
        ProductRepositoryInterface $productRepository,
        TransactionRepositoryInterface $transactionRepository,
        StoreRepositoryInterface $storeRepository
    ) {
        $this->productRepository = $productRepository;
        $this->transactionRepository = $transactionRepository;
        $this->storeRepository = $storeRepository;
    }

    public function prepareCheckout(array $cart = [])
    {
        if (empty($cart)) {
            return [
                'status' => false,
                'message' => 'Keranjang belanja Anda kosong',
                'data' => null
            ];
        }

        // Hitung subtotal
        $items = [];
        $subTotal = 0;

        foreach ($cart as $id => $details) {
            $product = $this->productRepository->find($id);
            if ($product) {
                // Hitung subtotal berdasarkan price, period, dan quantity
                $days = $details['days'] ?? 3;
                $periods = ceil($days / 3);
                $quantity = $details['quantity'] ?? 1;
                $itemSubtotal = $product->price * $periods * $quantity;

                $items[$id] = [
                    'product' => $product,
                    'quantity' => $quantity,
                    'days' => $days,
                    'subtotal' => $itemSubtotal
                ];

                $subTotal += $itemSubtotal;
            }
        }

        // Tambahan data yang dibutuhkan
        $stores = $this->storeRepository->all();

        // Gunakan subtotal sebagai grand total
        $grandTotal = $subTotal;

        // Simpan ke session untuk digunakan di checkout page
        session(['checkout_items' => $items]);
        session(['checkout_subtotal' => $subTotal]);

        return [
            'status' => true,
            'data' => [
                'items' => $items,
                'subTotal' => $subTotal,
                'grandTotal' => $grandTotal, // Gunakan subtotal sebagai grand total
                'stores' => $stores
            ]
        ];
    }

    public function prepareDirectCheckout($product)
    {
        if (!$product || $product->stock <= 0) {
            return [
                'status' => false,
                'message' => 'Maaf, stok produk ini sudah habis',
                'data' => null
            ];
        }

        // Default setup untuk checkout
        $days = 3; // Default 3 hari
        $quantity = 1; // Default quantity 1

        // Hitung subtotal: price * periods * quantity
        $periods = ceil($days / 3);
        $subtotal = $product->price * $periods * $quantity;

        // Set session checkout items baru hanya dengan produk yang dipilih
        $items = [
            $product->id => [
                'product' => $product,
                'quantity' => $quantity,
                'days' => $days,
                'subtotal' => $subtotal
            ]
        ];

        session(['checkout_items' => $items]);
        session(['checkout_subtotal' => $subtotal]);

        return [
            'status' => true,
            'data' => [
                'product_slug' => $product->slug
            ]
        ];
    }

    public function processCheckout(Request $request)
    {
        // Ambil items dari session
        $checkoutItems = session('checkout_items', []);
        $subTotal = session('checkout_subtotal', 0);

        if (empty($checkoutItems)) {
            return [
                'status' => false,
                'message' => 'Sesi checkout telah berakhir'
            ];
        }

        // Upload bukti pembayaran
        $proofPath = $request->file('proof')->store('proofs', 'public');

        // Tanggal mulai dan durasi
        $startedDate = Carbon::parse($request->started_at);

        // Buat group ID untuk semua transaksi dalam checkout ini
        $transactionGroupId = 'GRP-' . uniqid();

        // Buat array untuk menyimpan ID transaksi
        $transactionIds = [];

        // Proses checkout dalam transaction DB untuk rollback jika ada error
        DB::transaction(function() use ($request, $checkoutItems, $proofPath, $startedDate, &$transactionIds, $transactionGroupId) {
            // Iterasi semua item untuk diproses
            foreach ($checkoutItems as $productId => $item) {
                $product = $this->productRepository->find($productId);

                // Skip jika produk tidak ditemukan atau stok tidak cukup
                if (!$product || $product->stock < $item['quantity']) {
                    continue;
                }

                // Hitung durasi dan periods
                $days = $item['days'];
                $periods = ceil($days / 3);

                // Hitung biaya
                $subtotal = $product->price * $periods * $item['quantity'];
                $totalAmount = $subtotal;

                // Tanggal selesai
                $endedDate = $startedDate->copy()->addDays($days);

                // Buat transaksi dengan transaction_group_id
                $transaction = $this->transactionRepository->create([
                    'user_id' => auth()->id(),
                    'name' => $request->name,
                    'phone_number' => $request->phone_number,
                    'trx_id' => $this->transactionRepository->generateUniqueTrxId(),
                    'address' => $request->delivery_type === 'delivery' ? $request->address : 'Pickup di toko',
                    'total_amount' => $totalAmount,
                    'duration' => $days,
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'store_id' => $request->store_id,
                    'started_at' => $startedDate,
                    'ended_at' => $endedDate,
                    'delivery_type' => $request->delivery_type,
                    'proof' => $proofPath,
                    'payment_method' => $request->payment_method,
                    'is_paid' => false, // Admin akan mengubah status pembayaran
                    'status' => 'menunggu konfirmasi',
                    'transaction_group_id' => $transactionGroupId
                ]);

                // Kurangi stok produk
                $this->productRepository->decreaseStock($product, $item['quantity']);

                // Simpan ID transaksi
                $transactionIds[] = $transaction->id;
            }

            // Hapus item yang sudah dibeli dari keranjang
            $cart = session('cart', []);
            foreach ($checkoutItems as $productId => $item) {
                unset($cart[$productId]);
            }
            session(['cart' => $cart]);

            // Hapus session checkout
            session()->forget(['checkout_items', 'checkout_subtotal']);
        });

        return [
            'status' => !empty($transactionIds),
            'transaction_id' => !empty($transactionIds) ? $transactionIds[0] : null
        ];
    }

    public function updateCheckoutItem(Request $request, $productId)
    {
        // Ambil data checkout dari session
        $checkoutItems = session('checkout_items', []);

        // Pastikan item ada di checkout
        if (!isset($checkoutItems[$productId])) {
            return [
                'status' => false,
                'message' => 'Item tidak ditemukan dalam checkout'
            ];
        }

        // Update quantity jika ada
        if ($request->has('quantity')) {
            $checkoutItems[$productId]['quantity'] = $request->quantity;
        }

        // Update durasi jika ada
        if ($request->has('days')) {
            $checkoutItems[$productId]['days'] = $request->days;
        }

        // Hitung ulang subtotal
        $product = $checkoutItems[$productId]['product'];
        $quantity = $checkoutItems[$productId]['quantity'];
        $days = $checkoutItems[$productId]['days'];
        $periods = ceil($days / 3);
        $subtotal = $quantity * $product->price * $periods;
        $checkoutItems[$productId]['subtotal'] = $subtotal;

        // Simpan kembali ke session
        session(['checkout_items' => $checkoutItems]);

        // Hitung total baru
        $total = 0;
        foreach ($checkoutItems as $item) {
            $total += $item['subtotal'];
        }
        session(['checkout_subtotal' => $total]);

        return [
            'status' => true,
            'data' => [
                'item' => [
                    'id' => $productId,
                    'quantity' => $checkoutItems[$productId]['quantity'],
                    'days' => $checkoutItems[$productId]['days'],
                    'subtotal' => $subtotal,
                ],
                'total' => $total,
            ]
        ];
    }

    public function removeCheckoutItem($productId)
    {
        // Ambil data checkout dari session
        $checkoutItems = session('checkout_items', []);

        // Pastikan item ada di checkout
        if (!isset($checkoutItems[$productId])) {
            return [
                'status' => false,
                'message' => 'Item tidak ditemukan dalam checkout'
            ];
        }

        // Hapus item dari array
        unset($checkoutItems[$productId]);

        // Simpan kembali ke session
        session(['checkout_items' => $checkoutItems]);

        // Hitung total baru
        $total = 0;
        foreach ($checkoutItems as $item) {
            $total += $item['subtotal'];
        }
        session(['checkout_subtotal' => $total]);

        return [
            'status' => true,
            'message' => 'Item berhasil dihapus',
            'data' => [
                'itemCount' => count($checkoutItems),
                'total' => $total,
            ]
        ];
    }

    public function calculateTotals(array $items)
    {
        $subtotal = 0;
        foreach ($items as $item) {
            $subtotal += $item['subtotal'];
        }

        // Tidak lagi menambahkan PPN dan asuransi
        $grandTotal = $subtotal;

        return [
            'subtotal' => $subtotal,
            'grandTotal' => $grandTotal,
        ];
    }

    /**
     * Get transaction by ID
     *
     * @param int $id Transaction ID
     * @return \App\Models\Transaction|null
     */
    public function getTransaction($id)
    {
        return Transaction::with(['product', 'store'])->findOrFail($id);
    }
}