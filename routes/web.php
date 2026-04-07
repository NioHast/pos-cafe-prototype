<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Customer\CustomerMenuController;
use App\Http\Controllers\Customer\CustomerOrderController;
use App\Http\Controllers\Customer\CustomerPaymentController;
use App\Http\Controllers\Cashier\CashierDashboardController;
use App\Http\Controllers\Cashier\CashierOrderController;
use App\Http\Controllers\Cashier\CashierPesananAktifController;
use App\Http\Controllers\Cashier\CashierPesananBaruController;
use App\Http\Controllers\Cashier\CashierRiwayatController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', fn() => redirect()->route('login'));

// Kasir auth
Route::get('/login',  [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.attempt');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Kasir pages
Route::prefix('cashier')->middleware(['auth', 'role:cashier,admin'])->group(function () {
    Route::get('/dashboard',     [CashierDashboardController::class, 'index'])->name('cashier.dashboard');
    Route::get('/pesanan-baru',  [CashierPesananBaruController::class, 'index'])->name('cashier.pesanan-baru');
    Route::post('/pesanan-baru', [CashierPesananBaruController::class, 'store'])->name('cashier.pesanan-baru.store');
    Route::get('/pesanan-aktif', [CashierPesananAktifController::class, 'index'])->name('cashier.pesanan-aktif');
    Route::get('/riwayat',       [CashierRiwayatController::class, 'index'])->name('cashier.riwayat');
    Route::get('/order/{order}',              [CashierOrderController::class, 'show'])->name('cashier.order.show');
    Route::patch('/order/{order}/status',     [CashierOrderController::class, 'updateStatus'])->name('cashier.order.status');
    Route::patch('/order/{order}/confirm-cash',    [CashierOrderController::class, 'confirmCash'])->name('cashier.order.confirm-cash');
    Route::patch('/order/{order}/confirm-payment', [CashierOrderController::class, 'confirmPayment'])->name('cashier.order.confirm-payment');
    Route::patch('/order/{order}/confirm-qris', [CashierOrderController::class, 'confirmQris'])->name('cashier.order.confirm-qris');
    Route::patch('/order/{order}/reject-qris',  [CashierOrderController::class, 'rejectQris'])->name('cashier.order.reject-qris');
    Route::get('/profil', fn() => Inertia::render('Cashier/Profil', ['user' => auth()->user()]))->name('cashier.profil');
    Route::get('/pending-count', fn() => response()->json(['count' => \App\Models\Order::where('status', \App\Models\Order::STATUS_PENDING)->where(function ($q) { $q->where('order_type', 'cashier')->orWhere(fn($q2) => $q2->where('order_type', 'qr')->where(fn($q3) => $q3->where('payment_method', 'cash')->orWhere(fn($q4) => $q4->where('payment_method', 'qris')->whereNotNull('payment_proof')))); })->count()]))->name('cashier.pending-count');
});

// Customer — entry point via QR scan
Route::get('/order', [CustomerMenuController::class, 'showIdentitas'])->name('customer.identitas');

// Customer pages
Route::prefix('customer')->group(function () {
    Route::get('/menu',    [CustomerMenuController::class, 'index'])->name('customer.menu');
    Route::get('/cart',    fn() => Inertia::render('Customer/Cart/Index', []))->name('customer.cart');
    Route::get('/riwayat', [CustomerOrderController::class, 'riwayat'])->name('customer.riwayat');
    Route::get('/order/{code}/status', [CustomerOrderController::class, 'status'])->name('customer.order.status');

    // Payment flow
    Route::get('/payment/{orderCode}/choose',      [CustomerPaymentController::class, 'showChoose'])->name('customer.payment.choose');
    Route::get('/payment/{orderCode}/cash-status', [CustomerPaymentController::class, 'showCashStatus'])->name('customer.payment.cash-status');
    Route::get('/payment/{orderCode}/qris',        [CustomerPaymentController::class, 'showQrisUpload'])->name('customer.payment.qris-upload');
    Route::get('/payment/{orderCode}/qris-status', [CustomerPaymentController::class, 'showQrisStatus'])->name('customer.payment.qris-status');
});
