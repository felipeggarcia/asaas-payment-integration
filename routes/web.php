<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\CheckoutController; 

Route::get('/', [CheckoutController::class, 'showCheckoutForm'])->name('checkout.form');
Route::post('/checkout/process', [CheckoutController::class, 'processCheckout'])->name('checkout.process');

// Route::prefix('api')->group(function () {
//     Route::post('payment/process', [PaymentController::class, 'processPayment'])->name('api.payment.process');

// });

