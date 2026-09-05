<?php

use App\Http\Controllers\FileController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Application-level API routes
|--------------------------------------------------------------------------
|
| Domain routes live in each module's routes/api.php and are registered by
| that module's RouteServiceProvider. Only cross-cutting endpoints belong
| here.
|
*/

// Admin media endpoints. "uploads" is a tighter limiter than admin-write
// because each call writes to disk.
Route::middleware(['auth:sanctum', 'admin', 'throttle:uploads'])
    ->prefix('admin')
    ->group(function () {
        Route::post('upload', [FileController::class, 'upload'])->name('file.upload');
        Route::post('update', [FileController::class, 'update'])->name('file.update');
    });
