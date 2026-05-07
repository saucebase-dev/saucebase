<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LocalizationController;
use App\Http\Controllers\PrivacyController;
use App\Http\Controllers\TermsController;
use App\Http\Middleware\BenchmarkMiddleware;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Modules\Demo\Http\Controllers\DemoController;

Route::middleware(BenchmarkMiddleware::class)->group(function () {
    Route::get('/benchmark/bare', fn () => response('ok'));
    Route::get('/benchmark/data', function (Request $request) {
        $request->validate(['page' => ['integer', 'min:1']]);

        return response()->json(User::paginate(15));
    });
});

Route::get('/', DemoController::class)->name('index');

Route::get('/privacy', PrivacyController::class)->name('privacy');
Route::get('/terms', TermsController::class)->name('terms');

Route::post('/locale/{locale}', LocalizationController::class)->name('locale');

Route::middleware(['auth', 'verified', 'role:admin|user'])->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
});
