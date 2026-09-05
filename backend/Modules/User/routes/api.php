<?php

use Illuminate\Support\Facades\Route;
use Modules\User\Controllers\UserController;

// Login is deliberately outside the auth group and carries the strictest
// limiter in the app (5 attempts/minute per IP and per email).
Route::post('auth/login', [UserController::class, 'login'])
    ->middleware('throttle:auth')
    ->name('auth.login');

Route::middleware(['auth:sanctum', 'throttle:admin-write'])->group(function () {
    Route::post('auth/logout', [UserController::class, 'logout'])->name('auth.logout');
    Route::get('auth/me', [UserController::class, 'me'])->name('auth.me');
});
