<?php

use Illuminate\Support\Facades\Route;
use Modules\Settings\Controllers\PublicSettingsController;
use Modules\Settings\Controllers\SettingsController;

// ----------------------------------------------------------------- public
Route::middleware('throttle:public-read')->group(function () {
    Route::get('settings', [PublicSettingsController::class, 'index'])->name('settings.index');
});

// ------------------------------------------------------------------ admin
Route::middleware(['auth:sanctum', 'admin', 'throttle:admin-write'])
    ->prefix('admin')
    ->group(function () {
        Route::get('settings', [SettingsController::class, 'index'])->name('admin.settings.index');
        Route::post('settings', [SettingsController::class, 'store'])->name('admin.settings.store');
        Route::post('settings/bulk', [SettingsController::class, 'bulk'])->name('admin.settings.bulk');
        Route::post('settings/{key}/reset', [SettingsController::class, 'reset'])->name('admin.settings.reset');
        Route::get('settings/{key}', [SettingsController::class, 'show'])->name('admin.settings.show');
        Route::put('settings/{key}', [SettingsController::class, 'update'])->name('admin.settings.update');
        Route::delete('settings/{key}', [SettingsController::class, 'destroy'])->name('admin.settings.destroy');
    });
