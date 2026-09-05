<?php

use Illuminate\Support\Facades\Route;
use Modules\Brand\Controllers\BrandController;

// ------------------------------------------------------------------ admin
Route::middleware(['auth:sanctum', 'admin', 'throttle:admin-write'])
    ->prefix('admin')
    ->group(function () {
        // Lookup by id lives on its own path so it never collides with the
        // resource's slug-based show route.
        Route::get('brands/id/{id}', [BrandController::class, 'findById'])->name('admin.brands.find');

        Route::apiResource('brands', BrandController::class)
            ->except(['show'])
            ->names('admin.brands');
    });

// ----------------------------------------------------------------- public
Route::middleware('throttle:public-read')->group(function () {
    Route::get('brands', [BrandController::class, 'index'])->name('brands.index');
    Route::get('brands/{slug}', [BrandController::class, 'show'])->name('brands.show');
});
