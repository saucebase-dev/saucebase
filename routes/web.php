<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\IndexController;
use Illuminate\Support\Facades\Route;
use Saucebase\Core\Http\Controllers\LocalizationController;
use Saucebase\Core\Http\Controllers\RobotsController;
use Saucebase\Core\Http\Controllers\SettingsController;
use Saucebase\Core\Http\Controllers\SitemapController;

Route::get('/', [IndexController::class, 'index'])->name('index');

Route::get('/privacy', [IndexController::class, 'privacy'])->name('privacy');
Route::get('/terms', [IndexController::class, 'terms'])->name('terms');

Route::get('/sitemap.xml', SitemapController::class)->name('sitemap');
Route::get('/robots.txt', RobotsController::class)->name('robots');

Route::post('/locale/{locale}', LocalizationController::class)->name('locale');

Route::middleware(['auth', 'verified', 'role:admin|user'])->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::get('/settings', SettingsController::class)->name('settings');
});
