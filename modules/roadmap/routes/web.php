<?php

use Illuminate\Support\Facades\Route;
use Modules\Roadmap\Http\Controllers\RoadmapController;
use Modules\Roadmap\Http\Controllers\RoadmapVoteController;

Route::middleware('web')->group(function (): void {
    Route::middleware('auth')->group(function (): void {
        Route::get('/roadmap', [RoadmapController::class, 'index'])->name('roadmap.index');
        Route::post('/roadmap', [RoadmapController::class, 'store'])->middleware('throttle:10,1')->name('roadmap.store');
        Route::post('/roadmap/{item}/vote', [RoadmapVoteController::class, 'store'])->middleware('throttle:30,1')->name('roadmap.vote');
    });
});
