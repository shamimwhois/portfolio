<?php

use App\Http\Controllers\PaymentController;
use Illuminate\Support\Facades\Route;

Route::middleware('web')->group(function () {

/*
|--------------------------------------------------------------------------
| Payment gateway endpoints (success / cancel / callback / webhook)
|--------------------------------------------------------------------------
*/
Route::get('/pay/{invoiceNo}/success', [PaymentController::class, 'success'])->name('payment.success');
Route::get('/pay/{invoiceNo}/cancel', [PaymentController::class, 'cancel'])->name('payment.cancel');
Route::match(['get', 'post'], '/pay/{invoiceNo}/callback/{gateway}', [PaymentController::class, 'callback'])->name('payment.callback');
Route::post('/pay/webhook/{gateway}', [PaymentController::class, 'webhook'])->name('payment.webhook');

});