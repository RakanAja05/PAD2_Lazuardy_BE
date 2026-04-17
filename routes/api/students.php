<?php

use App\Http\Controllers\Students\ReviewController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::middleware('role:student')->group(function () {
        Route::get('/student/review', [ReviewController::class, 'index']);
        Route::get('/student/review/{tutorId}', [ReviewController::class, 'show'])
            ->whereNumber('tutorId');
        Route::post('/student/review', [ReviewController::class, 'storeOrUpdate']);
        Route::patch('/student/review', [ReviewController::class, 'storeOrUpdate']);

        Route::patch('/reviews/{id}', [ReviewController::class, 'update'])
            ->whereNumber('id');
    });
});
