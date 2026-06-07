<?php

use App\Http\Controllers\Master\ClassController;
use Illuminate\Support\Facades\Route;

// Public
Route::get('/classes', [ClassController::class, 'index']);
Route::get('/classes/{id}', [ClassController::class, 'show'])->whereNumber('id');


