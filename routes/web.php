<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\IndexController;
use Illuminate\Support\Facades\Route;
use Saucebase\Core\Http\Controllers\HomeController;
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

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::get('/settings', SettingsController::class)->name('settings');
});

// Where a signed-in user lands; the app chooses with Home::using(). Outside `tenant`:
// the destination, not this redirect, decides whether a workspace is needed.
Route::get('/home', HomeController::class)->middleware(['auth', 'verified'])->name('home');
