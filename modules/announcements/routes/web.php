<?php

use Illuminate\Support\Facades\Route;
use Modules\Announcements\Http\Controllers\DismissAnnouncementController;

Route::middleware('web')->group(function (): void {
    Route::post('/announcements/{announcement}/dismiss', DismissAnnouncementController::class)
        ->name('announcements.dismiss');
});
