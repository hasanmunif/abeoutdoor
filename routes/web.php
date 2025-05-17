<?php

use App\Http\Controllers\FrontController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

// Nonaktifkan semua rute auth bawaan Laravel
// Hapus atau komentari baris ini jika ada:
// Auth::routes();

// Semua rute login redirect ke filament customer panel
Route::redirect('/login', '/customer/login');
Route::redirect('/register', '/customer/register');
Route::redirect('/password/reset', '/customer/password-reset');
Route::redirect('/forgot-password', '/customer/password-reset');
Route::redirect('/email/verify', '/customer/email-verification');
Route::redirect('/home', '/customer');

// Pastikan rute ini ada di bagian paling atas file routes/web.php
// untuk menangkap semua permintaan login sebelum rute lainnya

// Rute logout
Route::post('/logout', function (Request $request) {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect('/');
})->name('logout');

Route::get('/', [FrontController::class, 'index'])->name('front.index');

Route::get('/transactions', [FrontController::class, 'transactions'])->name('front.transactions');

Route::post('/transactions/details', [FrontController::class, 'transactions_details'])->name('front.transactions.details');

Route::get('/details/{product:slug}', [FrontController::class, 'details'])->name('front.details');

Route::get('/success-booking/{transaction}', [FrontController::class, 'success_booking'])->name('front.success.booking');

// Route::post('/checkout/finish', [FrontController::class, 'checkout_store'])->name('front.checkout.store');

// Route::get('/checkout/{product:slug}/payment', [FrontController::class, 'checkout'])->name('front.checkout');


Route::get('/category/{category:slug}', [FrontController::class, 'category'])->name('front.category');

Route::get('/brand/{brand:slug}/products', [FrontController::class, 'brand'])->name('front.brand');

// Route::get('/booking/check', [FrontController::class, 'my_booking'])->name('front.my-booking');


Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Cart Routes
Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart/add/{product:slug}', [CartController::class, 'add'])->name('cart.add');
Route::post('/cart/update/{id}', [CartController::class, 'update'])->name('cart.update');
Route::delete('/cart/remove/{id}', [CartController::class, 'remove'])->name('cart.remove');
Route::post('/cart/clear', [CartController::class, 'clear'])->name('cart.clear');
Route::post('/cart/checkout', [CartController::class, 'checkout'])->name('cart.checkout');


// Route checkout baru
Route::middleware(['auth'])->group(function () {
    Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
    Route::post('/checkout/store', [CheckoutController::class, 'store'])->name('checkout.store');
    Route::get('/checkout/success/{id}/{group_id?}', [CheckoutController::class, 'success'])->name('checkout.success');
    Route::get('/checkout/cancel', [CheckoutController::class, 'cancel'])->name('checkout.cancel');
});

// Tambahkan route baru untuk checkout single item
Route::get('/checkout/single/{product:slug}', [CheckoutController::class, 'single'])->name('checkout.single');

// Tambahkan route baru untuk checkout langsung dari detail produk
Route::get('/checkout/direct/{product:slug}', [CheckoutController::class, 'direct'])->name('checkout.direct');


Route::post('/checkout/update/{productId}', [CheckoutController::class, 'updateItem'])->name('checkout.update');
Route::delete('/checkout/remove/{productId}', [CheckoutController::class, 'removeItem'])->name('checkout.remove');

Route::post('/checkout/midtrans', [CheckoutController::class, 'processMidtrans'])->name('checkout.midtrans');
Route::post('/checkout/notification', [CheckoutController::class, 'midtransCallback'])->name('checkout.notification');
Route::get('/checkout/pending/{id}/{group_id?}', [CheckoutController::class, 'pending'])->name('checkout.pending');

// Route untuk melakukan pembayaran ulang Midtrans
Route::get('/checkout/repay/{transaction}', [CheckoutController::class, 'repayMidtrans'])->name('checkout.repay');

require __DIR__.'/auth.php';