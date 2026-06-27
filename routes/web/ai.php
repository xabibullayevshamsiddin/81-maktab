<?php

use App\Http\Controllers\SiteAiController;
use Illuminate\Support\Facades\Route;

Route::post('ai-chat', [SiteAiController::class, 'generate'])
    ->middleware(['auth', 'throttle:ai-chat', 'active'])
    ->name('ai.chat');

Route::get('ai-chat/status', [SiteAiController::class, 'status'])
    ->middleware(['auth'])
    ->name('ai.chat.status');

Route::post('ai-chat/feedback', [SiteAiController::class, 'feedback'])
    ->middleware(['auth', 'throttle:ai-feedback', 'active'])
    ->name('ai.chat.feedback');
