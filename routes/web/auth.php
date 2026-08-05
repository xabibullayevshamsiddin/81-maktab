<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::get('login', [AuthController::class, 'login'])->name('login');
Route::post('authenticate', [AuthController::class, 'authenticate'])->middleware('throttle:10,1')->name('authenticate');

// Telegram tasdiqlash — login va register uchun umumiy
Route::get('telegram-verify/{token}', [AuthController::class, 'showTelegramVerify'])->name('telegram.verify');

Route::get('register', [AuthController::class, 'register'])->name('register');
Route::post('register', [AuthController::class, 'registerStore'])->middleware('throttle:6,1')->name('register.store');

Route::get('forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.forgot.form');
Route::post('forgot-password', [AuthController::class, 'sendPasswordResetCode'])->middleware('throttle:6,1')->name('password.forgot.send');
Route::get('reset-password', [AuthController::class, 'showPasswordResetForm'])->name('password.reset.form');
Route::post('reset-password', [AuthController::class, 'resetPassword'])->middleware('throttle:15,1')->name('password.reset');
Route::post('reset-password/resend', [AuthController::class, 'resendPasswordResetCode'])->middleware('throttle:6,1')->name('password.reset.resend');

Route::post('logout', [AuthController::class, 'logout'])->name('logout');
