<?php

use Illuminate\Support\Facades\Route;
use Modules\Category\Controllers\CategoryController;

// ------------------------------------------------------------------ admin
Route::middleware(['auth:sanctum', 'admin', 'throttle:admin-write'])
    ->prefix('admin')
    ->group(function () {
        // Static segments are declared before the {id} routes so "tree" and
        // "options" are never swallowed by the resource's show route.
        Route::get('categories/tree', [CategoryController::class, 'tree'])->name('admin.categories.tree');
        Route::get('categories/options', [CategoryController::class, 'options'])->name('admin.categories.options');
        Route::get('categories/{id}', [CategoryController::class, 'findById'])->name('admin.categories.find');

        // The route parameter is named {id} rather than the default
        // {category}: the controller and UpdateCategoryRequest both read it
        // as "id", and a mismatch would silently hand null to the uniqueness
        // rules and to the tree cycle guard.
        Route::apiResource('categories', CategoryController::class)
            ->except(['show'])
            ->parameters(['categories' => 'id'])
            ->names('admin.categories');
    });

// ----------------------------------------------------------------- public
Route::middleware('throttle:public-read')->group(function () {
    Route::get('categories/tree', [CategoryController::class, 'tree'])->name('categories.tree');
    Route::get('categories', [CategoryController::class, 'index'])->name('categories.index');
    Route::get('categories/{slug}/{childSlug?}', [CategoryController::class, 'show'])->name('categories.show');
});
