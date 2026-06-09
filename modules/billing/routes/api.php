<?php

use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Support\Facades\Route;
use Modules\Billing\Http\Controllers\WebhookController;

Route::middleware('api')->group(function (): void {
    Route::post('/billing/webhooks/{provider}', WebhookController::class)
        ->name('billing.webhooks')
        ->withoutMiddleware([PreventRequestForgery::class]);
});
