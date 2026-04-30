<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::get('login', [AuthController::class, 'login'])->name('login');
Route::post('authenticate', [AuthController::class, 'authenticate'])->name('authenticate');
Route::get('login/verify-code', [AuthController::class, 'showLoginVerify'])->name('login.verify.form');
Route::post('login/verify-code', [AuthController::class, 'verifyLoginCode'])->name('login.verify');
Route::post('login/verify-code/resend', [AuthController::class, 'resendLoginCode'])->name('login.verify.resend');

Route::get('register', [AuthController::class, 'register'])->name('register');
Route::post('register', [AuthController::class, 'registerStore'])->name('register.store');
Route::get('register/verify-code', [AuthController::class, 'showRegisterVerify'])->name('register.verify.form');
Route::post('register/verify-code', [AuthController::class, 'verifyRegisterCode'])->name('register.verify');
Route::post('register/verify-code/resend', [AuthController::class, 'resendRegisterCode'])->name('register.verify.resend');

Route::get('forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.forgot.form');
Route::post('forgot-password', [AuthController::class, 'sendPasswordResetCode'])->name('password.forgot.send');
Route::get('reset-password', [AuthController::class, 'showPasswordResetForm'])->name('password.reset.form');
Route::post('reset-password', [AuthController::class, 'resetPassword'])->name('password.reset');
Route::post('reset-password/resend', [AuthController::class, 'resendPasswordResetCode'])->name('password.reset.resend');

Route::post('logout', [AuthController::class, 'logout'])->name('logout');
