<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::get('login', [AuthController::class, 'login'])->name('login');
Route::post('authenticate', [AuthController::class, 'authenticate'])->middleware('throttle:10,1')->name('authenticate');
Route::get('login/verify-code', [AuthController::class, 'showLoginVerify'])->name('login.verify.form');
Route::post('login/verify-code', [AuthController::class, 'verifyLoginCode'])->middleware('throttle:15,1')->name('login.verify');
Route::post('login/verify-code/resend', [AuthController::class, 'resendLoginCode'])->middleware('throttle:6,1')->name('login.verify.resend');

Route::get('register', [AuthController::class, 'register'])->name('register');
Route::post('register', [AuthController::class, 'registerStore'])->middleware('throttle:6,1')->name('register.store');
Route::get('register/verify-code', [AuthController::class, 'showRegisterVerify'])->name('register.verify.form');
Route::post('register/verify-code', [AuthController::class, 'verifyRegisterCode'])->middleware('throttle:15,1')->name('register.verify');
Route::post('register/verify-code/resend', [AuthController::class, 'resendRegisterCode'])->middleware('throttle:6,1')->name('register.verify.resend');
Route::get('register/verify-telegram', [AuthController::class, 'showTelegramRegisterVerify'])->name('register.telegram.form');
Route::get('register/verify-telegram/status', [AuthController::class, 'telegramRegisterStatus'])->middleware('throttle:30,1')->name('register.telegram.status');
Route::post('register/verify-telegram/complete', [AuthController::class, 'completeTelegramRegister'])->middleware('throttle:10,1')->name('register.telegram.complete');

Route::get('forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.forgot.form');
Route::post('forgot-password', [AuthController::class, 'sendPasswordResetCode'])->middleware('throttle:6,1')->name('password.forgot.send');
Route::get('reset-password', [AuthController::class, 'showPasswordResetForm'])->name('password.reset.form');
Route::post('reset-password', [AuthController::class, 'resetPassword'])->middleware('throttle:15,1')->name('password.reset');
Route::post('reset-password/resend', [AuthController::class, 'resendPasswordResetCode'])->middleware('throttle:6,1')->name('password.reset.resend');

Route::post('logout', [AuthController::class, 'logout'])->name('logout');
