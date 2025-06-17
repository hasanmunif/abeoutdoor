<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Store;
use App\Http\Requests\StoreBookingRequest;
use App\Http\Requests\StorePaymentRequest;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FrontController extends Controller
{
    public function index(){
        $categories = Category::all();
        $latest_product = Product::latest()->take(4)->get();
        $random_product = Product::inRandomOrder()->take(4)->get();
        return view('front.index', compact('categories', 'latest_product', 'random_product'));
    }

    public function category(Category $category){
        session()->put('category_id', $category->id);
        return view('front.brands', compact('category'));
    }

    public function brand(Brand $brand)
    {
        // Dapatkan category_id dari session
        $category_id = session()->get('category_id');

        // Variabel untuk menyimpan nama kategori
        $categoryName = null;

        // Query awal untuk semua produk brand
        $allProducts = Product::where('brand_id', $brand->id)
            ->latest()
            ->get();

        // Filter produk berdasarkan kategori jika ada category_id
        if ($category_id) {
            $products = $allProducts->where('category_id', $category_id);
            // Ambil nama kategori untuk ditampilkan
            $category = Category::find($category_id);
            $categoryName = $category ? $category->name : null;
        } else {
            $products = $allProducts;
        }

        // Pastikan $products adalah collection
        $products = collect($products);

        return view('front.gadgets', compact('brand', 'products', 'allProducts', 'categoryName'));
    }

    public function details(Product $product){
        return view('front.details', compact('product'));
    }

    public function success_booking(Transaction $transaction){
        return view('front.success_booking', compact('transaction'));
    }

    public function transactions(){
        return view('front.transactions');
    }

    public function transactions_details(Request $request){
        $request->validate([
            'trx_id' => ['required', 'string', 'max:255'],
            'phone_number' => ['required', 'numeric', 'digits_between:8,12'],
        ]);

        $trx_id = $request->input('trx_id');
        $phone_number = $request->input('phone_number');

        $details = Transaction::with(['store', 'product'])
        ->where('trx_id', $trx_id)
        ->where('phone_number', $phone_number)
        ->first();

        if(!$details) {
            return redirect()->back()->withErrors(['error' => 'Transaction not found.']);
        }

        $duration = $details->duration;
        $periods = ceil($duration / 3);
        $subTotal = $details->product->price * $periods;

        return view('front.transaction_details', compact('details', 'subTotal'));
    }

    /**
     * Checkout for multiple items from cart
     */
    public function checkoutMultiple()
    {
        $checkoutItems = session('checkout_items', []);

        if (empty($checkoutItems)) {
            return redirect()->route('cart.index')->with('error', 'Tidak ada item yang dipilih untuk checkout');
        }

        $items = [];
        $totalPrice = 0;

        foreach ($checkoutItems as $id => $details) {
            $product = Product::find($id);

            if (!$product) {
                continue;
            }

            // Hitung periods (kelipatan 3 hari)
            $days = $details['days'] ?? 3;
            $periods = ceil($days / 3);

            // Hitung subtotal
            $subtotal = $product->price * $periods * $details['quantity'];

            $items[] = [
                'product' => $product,
                'quantity' => $details['quantity'],
                'days' => $days,
                'subtotal' => $subtotal
            ];

            $totalPrice += $subtotal;
        }

        // HAPUS perhitungan pajak dan asuransi
        // $ppn = $totalPrice * 0.11;
        // $insurance = 900000;
        // $grandTotal = $totalPrice + $ppn + $insurance;

        $grandTotal = $totalPrice; // Total harga = total price

        // return view('front.checkout_multiple', compact('items', 'totalPrice', 'ppn', 'insurance', 'grandTotal'));
        return view('front.checkout_multiple', compact('items', 'totalPrice', 'grandTotal'));
    }

    /**
     * Process multiple checkout
     */
    public function checkoutMultipleStore(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone_number' => 'required|numeric|regex:/^[0-9]+$/|digits_between:8,12',
            'store_id' => 'required|exists:stores,id',
            'started_at' => 'required|date|after_or_equal:today',
            'proof' => 'required|image|max:2048',
        ]);

        $checkoutItems = session('checkout_items', []);

        if (empty($checkoutItems)) {
            return redirect()->route('cart.index')->with('error', 'Tidak ada item yang dipilih untuk checkout');
        }

        $transactions = [];

        DB::transaction(function() use ($request, $checkoutItems, &$transactions) {
            foreach ($checkoutItems as $id => $details) {
                $product = Product::find($id);

                if (!$product || $product->stock < $details['quantity']) {
                    continue;
                }

                $days = $details['days'] ?? 3;
                $periods = ceil($days / 3);
                $subtotal = $product->price * $periods * $details['quantity'];

                // HAPUS perhitungan pajak dan asuransi
                // $ppn = $subtotal * 0.11;
                // $insurance = 900000;
                // $totalAmount = $subtotal + $ppn + $insurance;

                $totalAmount = $subtotal; // Total amount = subtotal

                $startedDate = Carbon::parse($request->started_at);
                $endedDate = $startedDate->copy()->addDays($days);

                // Upload bukti pembayaran
                $proofPath = $request->file('proof')->store('proofs', 'public');

                // Buat transaksi
                $transaction = Transaction::create([
                    'name' => $request->name,
                    'phone_number' => $request->phone_number,
                    'trx_id' => Transaction::generateUniqueTrxId(),
                    'address' => $request->address ?? 'Pickup di toko',
                    'total_amount' => $totalAmount,
                    'duration' => $days,
                    'product_id' => $product->id,
                    'quantity' => $details['quantity'],
                    'store_id' => $request->store_id,
                    'started_at' => $startedDate,
                    'ended_at' => $endedDate,
                    'delivery_type' => $request->delivery_type ?? 'pickup',
                    'proof' => $proofPath,
                    'is_paid' => false,
                ]);

                // Kurangi stok
                $product->decreaseStock($details['quantity']);

                $transactions[] = $transaction->id;

                // Hapus item dari cart
                $cart = session('cart', []);
                if (isset($cart[$id])) {
                    unset($cart[$id]);
                }
                session()->put('cart', $cart);
            }
        });

        // Hapus checkout items dari session
        session()->forget('checkout_items');

        // Redirect ke halaman sukses dengan ID transaksi pertama
        if (!empty($transactions)) {
            return redirect()->route('front.success.booking', $transactions[0])
                ->with('success', 'Pesanan berhasil dibuat. Terima kasih!');
        }

        return redirect()->route('cart.index')->with('error', 'Terjadi kesalahan saat checkout');
    }
}