<?php

use Illuminate\Support\Facades\Route;
use Modules\Product\Controllers\ProductController;

// ------------------------------------------------------------------ admin
Route::middleware(['auth:sanctum', 'admin', 'throttle:admin-write'])
    ->prefix('admin')
    ->group(function () {
        Route::apiResource('products', ProductController::class)->names('admin.products');
    });

// ----------------------------------------------------------------- public
Route::middleware('throttle:public-read')->group(function () {
    Route::get('products', [ProductController::class, 'index'])->name('products.index');
    Route::get('products/{slug}', [ProductController::class, 'findBySlug'])->name('products.show');
});
