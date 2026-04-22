<?php

use App\Http\Controllers\Customer\CustomerOrderController;
use App\Http\Controllers\Customer\CustomerPaymentController;
use App\Support\DemoAdminMode;
use Illuminate\Support\Facades\Route;

if (DemoAdminMode::enabled()) {
	return;
}

// Customer order store (called via axios from Cart page)
Route::post('/order', [CustomerOrderController::class, 'store'])->name('customer.order.store');

// Payment method selection
Route::post('/order/{order}/pay/cash', [CustomerPaymentController::class, 'chooseCash'])->name('customer.payment.cash');
Route::post('/order/{order}/pay/qris', [CustomerPaymentController::class, 'chooseQris'])->name('customer.payment.qris-init');
Route::post('/order/{order}/qris-proof', [CustomerPaymentController::class, 'uploadQrisProof'])->name('customer.payment.qris-proof');
