<?php

use ___MODULE_NAMESPACE___\___Module___\Http\Controllers\___Module___Controller;
use Illuminate\Support\Facades\Route;

Route::middleware('web')->group(function () {
    Route::get('{module-}', ___Module___Controller::class)->name('{module-}.index');
});
