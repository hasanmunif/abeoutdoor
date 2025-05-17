<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class CartController extends Controller
{
    /**
     * Display cart items
     */
    public function index()
    {
        $cart = session('cart', []);
        $items = [];
        $total = 0;

        foreach ($cart as $id => $details) {
            $product = Product::find($id);
            if ($product) {
                $items[] = [
                    'product' => $product,
                    'quantity' => $details['quantity'],
                    'days' => $details['days'] ?? 3, // Default 3 hari
                    'subtotal' => $product->calculateRentalPrice($details['days'] ?? 3) * $details['quantity']
                ];

                $total += $product->calculateRentalPrice($details['days'] ?? 3) * $details['quantity'];
            }
        }

        return view('front.cart', compact('items', 'total'));
    }

    /**
     * Add product to cart
     */
    public function add(Request $request, Product $product)
    {
        // Cek ketersediaan stok
        if ($product->stock <= 0) {
            return redirect()->back()->with('error', 'Stok produk tidak tersedia');
        }

        // Default quantity dan days
        $quantity = 1;
        $days = 3; // Minimal 3 hari

        // Validasi jika produk tidak bisa multi-quantity, maka cek dulu di cart
        if (!$product->can_multi_quantity) {
            $cart = session('cart', []);
            if (isset($cart[$product->id])) {
                return redirect()->back()->with('error', 'Produk ini hanya bisa disewa 1 unit');
            }
        }

        // Validasi jika request quantity lebih dari stok yang tersedia
        if ($request->has('quantity')) {
            $quantity = (int)$request->input('quantity', 1);
            if ($quantity > $product->stock) {
                return redirect()->back()->with('error', 'Jumlah melebihi stok yang tersedia');
            }
        }

        // Validasi jika hari tidak kelipatan 3
        if ($request->has('days')) {
            $days = (int)$request->input('days', 3);
            if ($days < 3 || $days % 3 !== 0) {
                return redirect()->back()->with('error', 'Durasi sewa harus minimal 3 hari dan kelipatan 3');
            }
        }

        // Ambil cart dari session
        $cart = session()->get('cart', []);

        // Jika produk sudah ada di cart
        if (isset($cart[$product->id])) {
            // Jika produk bisa multi-quantity, tambah quantity
            if ($product->can_multi_quantity) {
                $newQuantity = $cart[$product->id]['quantity'] + $quantity;

                // Cek apakah total quantity melebihi stok
                if ($newQuantity > $product->stock) {
                    return redirect()->back()->with('error', 'Total jumlah melebihi stok yang tersedia');
                }

                $cart[$product->id]['quantity'] = $newQuantity;
                $cart[$product->id]['days'] = $days; // Update days jika diubah
            } else {
                return redirect()->route('cart.index')->with('info', 'Produk sudah ada di keranjang');
            }
        } else {
            // Tambahkan produk baru ke cart
            $cart[$product->id] = [
                'quantity' => $quantity,
                'days' => $days,
                'added_at' => now()->timestamp
            ];
        }

        // Simpan kembali cart ke session
        session()->put('cart', $cart);

        return redirect()->route('cart.index')->with('success', 'Produk berhasil ditambahkan ke keranjang');
    }

    /**
     * Update cart item (AJAX version)
     */
    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);
        $cart = session()->get('cart', []);

        if (!isset($cart[$id])) {
            return response()->json([
                'success' => false,
                'message' => 'Produk tidak ditemukan di keranjang'
            ]);
        }

        $updated = false;

        // Update quantity if provided
        if ($request->has('quantity')) {
            $quantity = (int)$request->input('quantity');

            // Validate can_multi_quantity
            if (!$product->can_multi_quantity && $quantity > 1) {
                return response()->json([
                    'success' => false,
                    'message' => 'Produk ini hanya bisa disewa 1 unit'
                ]);
            }

            // Validate stock
            if ($quantity > $product->stock) {
                return response()->json([
                    'success' => false,
                    'message' => 'Jumlah melebihi stok yang tersedia'
                ]);
            }

            if ($quantity <= 0) {
                unset($cart[$id]);
                session()->put('cart', $cart);

                // Calculate new total
                $total = $this->calculateCartTotal();
                $itemCount = count($cart);

                return response()->json([
                    'success' => true,
                    'message' => 'Item dihapus dari keranjang',
                    'total' => $total,
                    'itemCount' => $itemCount
                ]);
            } else {
                $cart[$id]['quantity'] = $quantity;
                $updated = true;
            }
        }

        // Update days if provided
        if ($request->has('days')) {
            $days = (int)$request->input('days');

            // Validate minimum 3 days and multiple of 3
            if ($days < 3 || $days % 3 !== 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Durasi sewa harus minimal 3 hari dan kelipatan 3'
                ]);
            }

            $cart[$id]['days'] = $days;
            $updated = true;
        }

        if ($updated) {
            // Calculate subtotal for this item
            $periods = ceil($cart[$id]['days'] / 3);
            $cart[$id]['subtotal'] = $product->price * $periods * $cart[$id]['quantity'];

            session()->put('cart', $cart);

            // Calculate new total
            $total = $this->calculateCartTotal();

            return response()->json([
                'success' => true,
                'message' => 'Keranjang berhasil diperbarui',
                'item' => [
                    'id' => $id,
                    'quantity' => $cart[$id]['quantity'],
                    'days' => $cart[$id]['days'],
                    'subtotal' => $cart[$id]['subtotal']
                ],
                'total' => $total
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Tidak ada perubahan'
        ]);
    }

    /**
     * Remove item from cart (AJAX version)
     */
    public function remove($id)
    {
        $cart = session()->get('cart', []);

        if (isset($cart[$id])) {
            unset($cart[$id]);
            session()->put('cart', $cart);

            // Calculate new total
            $total = $this->calculateCartTotal();
            $itemCount = count($cart);

            return response()->json([
                'success' => true,
                'message' => 'Item berhasil dihapus dari keranjang',
                'total' => $total,
                'itemCount' => $itemCount
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Item tidak ditemukan'
        ]);
    }

    /**
     * Clear the entire cart (AJAX version)
     */
    public function clear()
    {
        session()->forget('cart');

        return response()->json([
            'success' => true,
            'message' => 'Keranjang berhasil dikosongkan'
        ]);
    }

    /**
     * Process checkout for selected items
     */
    public function checkout(Request $request)
    {
        // Validasi data yang masuk
        $request->validate([
            'items' => 'required|array',
            'items.*' => 'exists:products,id',
        ]);

        // Ambil item yang dipilih dari session keranjang
        $cartSession = session('cart', []);
        $selectedItems = [];
        $subTotal = 0;

        foreach ($request->items as $productId) {
            if (isset($cartSession[$productId])) {
                $selectedItems[$productId] = $cartSession[$productId];
                $subTotal += $cartSession[$productId]['subtotal'];
            }
        }

        // Jika tidak ada item yang dipilih, kembalikan ke halaman keranjang
        if (empty($selectedItems)) {
            return redirect()->route('cart.index')->with('error', 'Tidak ada produk yang dipilih');
        }

        // Simpan item yang dipilih dalam session untuk digunakan di halaman checkout
        session(['checkout_items' => $selectedItems]);
        session(['checkout_subtotal' => $subTotal]);

        return redirect()->route('front.checkout');
    }

    /**
     * Calculate cart total
     */
    private function calculateCartTotal()
    {
        $cart = session()->get('cart', []);
        $total = 0;

        foreach ($cart as $id => $details) {
            $product = Product::find($id);
            if ($product) {
                $periods = ceil($details['days'] / 3);
                $subtotal = $product->price * $periods * $details['quantity'];
                $total += $subtotal;
            }
        }

        return $total;
    }
}