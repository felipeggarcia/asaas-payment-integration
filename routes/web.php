<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\CheckoutController; 
use App\Http\Controllers\Api\PaymentController; 

Route::get('/', [CheckoutController::class, 'showCheckoutForm'])->name('checkout.form');

Route::post('/checkout/process', [CheckoutController::class, 'processCheckout'])->name('checkout.process');
Route::post('/thank-you', [CheckoutController::class, 'showThankYou'])->name('thank-you');



