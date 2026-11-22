<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\SocialAuth\SocialAuthController;
use L5Swagger\Http\Controllers\SwaggerController;
use L5Swagger\Http\Controllers\SwaggerAssetController;

Route::get('api/documentation', [SwaggerController::class, 'api'])->name('l5-swagger.default.api');
Route::get('api/docs', [SwaggerController::class, 'docs'])->name('l5-swagger.default.docs');
Route::get('api/oauth2-callback', [SwaggerController::class, 'oauth2Callback'])->name('l5-swagger.default.oauth2_callback');
// Google OAuth Routes (butuh session untuk state verification)
// Route::get('auth/google', [GoogleController::class, 'redirectToGoogle'])->name('google.redirect');
// Route::get('auth/google/callback', [GoogleController::class, 'handleGoogleCallback'])->name('google.callback');

Route::get('/', function () {
    return ['message' => 'API Backend Laravel - PAD Lazuardy'];
});

Route::get('/__debug/db', function () {
    return [
        'database' => Illuminate\Support\Facades\DB::connection()->getDatabaseName(),
        'users_has_role' => Illuminate\Support\Facades\Schema::hasColumn('users', 'role'),
    ];
});

// Social Auth Routes (support multiple providers: google, facebook, dll)
Route::name('social.')->group(function(){
    Route::get('auth/{provider}', [SocialAuthController::class, 'redirectToProvider'])->name('login');
    Route::get('auth/{provider}/callback', [SocialAuthController::class, 'handleProviderCallback'])->name('callback');
});
