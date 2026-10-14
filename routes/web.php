<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\IndexController;
use Illuminate\Support\Facades\Route;

Route::get('/', [IndexController::class, 'index'])->name('index');

Route::get('/privacy', [IndexController::class, 'privacy'])->name('privacy');
Route::get('/terms', [IndexController::class, 'terms'])->name('terms');

Route::get('/dashboard', DashboardController::class)
    ->middleware(['auth', 'verified'])
    ->name('dashboard');
