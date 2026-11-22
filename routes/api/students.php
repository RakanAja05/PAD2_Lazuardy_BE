<?php

use App\Http\Controllers\Students\ReviewController;
use App\Http\Controllers\Students\StudentRequestController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::middleware('role:student')->group(function () {
        Route::prefix('student')->group(function () {
            Route::get('/review', [ReviewController::class, 'index']);
            Route::get('/review/{tutorId}', [ReviewController::class, 'show'])->whereNumber('tutorId');
            Route::post('/review', [ReviewController::class, 'storeOrUpdate']);
            Route::patch('/review', [ReviewController::class, 'storeOrUpdate']);

            Route::get('/request', [StudentRequestController::class, 'index']);
            Route::post('/request', [StudentRequestController::class, 'store']);
            Route::delete('/request/{id}', [StudentRequestController::class, 'cancel'])->whereNumber('id');
        });

        Route::patch('/reviews/{id}', [ReviewController::class, 'update'])->whereNumber('id');
    });
});
