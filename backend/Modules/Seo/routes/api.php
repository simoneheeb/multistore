<?php

use Illuminate\Support\Facades\Route;
use Modules\Seo\Controllers\RobotsController;
use Modules\Seo\Controllers\SeoController;
use Modules\Seo\Controllers\SitemapController;

// All three endpoints are public and cached; the read limiter is enough.
Route::middleware('throttle:public-read')->group(function () {
    Route::get('sitemap.xml', [SitemapController::class, 'index'])->name('seo.sitemap');
    Route::get('robots.txt', [RobotsController::class, 'index'])->name('seo.robots');
    Route::get('seo', [SeoController::class, 'index'])->name('seo.index');
});
