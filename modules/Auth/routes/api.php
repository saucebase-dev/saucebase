<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::middleware('api')->group(function (): void {
    Route::middleware(['auth:sanctum'])->prefix('api/v1/auth')->group(function (): void {
        Route::get('me', fn () => response()->json(Auth::user()));
    });
});
