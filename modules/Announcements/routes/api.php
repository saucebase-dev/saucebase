<?php

use Illuminate\Support\Facades\Route;

Route::middleware('api')->group(function (): void {
    Route::prefix('v1')->group(function (): void {
        //
    });
});
