<?php

use App\Http\Controllers\Students\ProfileController;
use App\Http\Controllers\Tutors\FindTutorController;
use App\Http\Controllers\Tutors\PresenceController;
use App\Http\Controllers\Tutors\TutorApplicationController;
use App\Http\Controllers\Tutors\TutorProfileController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    // Find tutors (search + detail)
    Route::get('/tutor/find', [FindTutorController::class, 'search']);
    Route::get('/tutor/find/{tutorId}', [FindTutorController::class, 'show'])->whereNumber('tutorId');

    // Tutor public profile
    Route::get('/tutor/profile/{tutorId}', [TutorProfileController::class, 'show'])->whereNumber('tutorId');
    Route::get('/tutor/{tutorId}/available-slots', [TutorProfileController::class, 'availableSlots'])->whereNumber('tutorId');

    Route::middleware('role:tutor')->group(function () {
        Route::get('/tutor/apply', [TutorApplicationController::class, 'index']);
        Route::post('/tutor/apply', [TutorApplicationController::class, 'store']);
        Route::patch('/tutor/lesson-formulir', [ProfileController::class, 'updateTutorLessonMethod']);

        Route::get('/tutor/presence', [PresenceController::class, 'index']);
        Route::post('/tutor/presence', [PresenceController::class, 'store']);
    });
});
