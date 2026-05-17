<?php

use App\Http\Controllers\Parents\ParentController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::middleware('role:parent')->group(function () {
        Route::get('/parent/schedule', [ParentController::class, 'schedules']);
        Route::get('/parent/learning-results', [ParentController::class, 'learningResults']);
    });
});
