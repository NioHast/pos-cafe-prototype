<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Customer\CustomerAuthController;
use App\Http\Controllers\Customer\CustomerMenuController;
use App\Http\Controllers\Customer\CustomerOrderController;
use App\Http\Controllers\Customer\CustomerPaymentController;
use App\Http\Controllers\Cashier\CashierDashboardController;
use App\Http\Controllers\Cashier\CashierOrderController;
use App\Http\Controllers\Cashier\CashierPesananAktifController;
use App\Http\Controllers\Cashier\CashierPesananBaruController;
use App\Http\Controllers\Cashier\CashierRiwayatController;
use App\Http\Controllers\Cashier\CashierVerifikasiController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', fn() => redirect()->route('login'));

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.attempt');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

Route::prefix('cashier')->middleware(['auth', 'role:cashier,admin'])->group(function () {
    Route::get('/dashboard',     [CashierDashboardController::class, 'index'])->name('cashier.dashboard');
    Route::get('/pesanan-baru',  [CashierPesananBaruController::class, 'index'])->name('cashier.pesanan-baru');
    Route::post('/pesanan-baru', [CashierPesananBaruController::class, 'store'])->name('cashier.pesanan-baru.store');
    Route::get('/pesanan-aktif', [CashierPesananAktifController::class, 'index'])->name('cashier.pesanan-aktif');
    Route::get('/riwayat',                [CashierRiwayatController::class, 'index'])->name('cashier.riwayat');
    Route::get('/order/{order}',          [CashierOrderController::class,  'show'])->name('cashier.order.show');
    Route::patch('/order/{order}/status', [CashierOrderController::class,  'updateStatus'])->name('cashier.order.status');
    Route::get('/verifikasi',                    [CashierVerifikasiController::class, 'index'])  ->name('cashier.verifikasi');
    Route::patch('/verifikasi/{user}/approve',   [CashierVerifikasiController::class, 'approve'])->name('cashier.verifikasi.approve');
    Route::patch('/verifikasi/{user}/reject',    [CashierVerifikasiController::class, 'reject']) ->name('cashier.verifikasi.reject');
    Route::get('/profil', fn() => Inertia::render('Cashier/Profil', ['user' => auth()->user()]))->name('cashier.profil');
});

Route::prefix('customer')->group(function () {
    Route::get('/login',  [CustomerAuthController::class, 'showLogin'])->name('customer.auth.login');
    Route::post('/login', [CustomerAuthController::class, 'login'])->name('customer.auth.attempt');

    Route::get('/menu', [CustomerMenuController::class, 'index'])->name('customer.menu');
    Route::get('/cart', fn() => Inertia::render('Customer/Cart/Index', []))->name('customer.cart');

    Route::post('/order',        [CustomerOrderController::class, 'store'])->name('customer.order.store');
    Route::get('/riwayat',       [CustomerOrderController::class, 'riwayat'])->name('customer.riwayat');
    Route::get('/order/{order}', [CustomerOrderController::class, 'show'])->name('customer.order.detail');

    Route::get('/order/{code}/status',    [CustomerOrderController::class,  'status'])->name('customer.order.status');
    Route::post('/order/{order}/payment', [CustomerPaymentController::class, 'initiate'])->name('customer.payment.initiate');
});
