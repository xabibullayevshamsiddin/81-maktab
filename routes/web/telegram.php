<?php

use App\Http\Controllers\TelegramWebhookController;
use Illuminate\Support\Facades\Route;

Route::post('telegram/webhook/{secret}', [TelegramWebhookController::class, 'handle'])
    ->name('telegram.webhook');

Route::get('telegram/status/{token}', [TelegramWebhookController::class, 'status'])
    ->name('telegram.status')
    ->middleware('throttle:30,1');

Route::get('telegram/complete/{token}', [TelegramWebhookController::class, 'complete'])
    ->name('telegram.complete');
