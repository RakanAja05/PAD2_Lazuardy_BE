<?php

use App\Http\Controllers\Payments\PaymentController;
use App\Http\Controllers\Payments\XenditWebhookController;
use Illuminate\Support\Facades\Route;

Route::post('/xendit/webhook', [XenditWebhookController::class, 'handle']);

Route::middleware('auth:sanctum')->group(function () {
    Route::middleware('role:student')->group(function () {
        Route::get('/package/order/{id}', [PaymentController::class, 'showPaymentPackage']);
        Route::post('/package/order', [PaymentController::class, 'storeOrderPackage']);
        Route::get('/payment/history', [PaymentController::class, 'showHistory']);
        Route::get('/payment/history/detail', [PaymentController::class, 'showDetail']);
    });
});
