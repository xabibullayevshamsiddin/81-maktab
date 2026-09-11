<?php

use App\Http\Controllers\IeltsController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->prefix('ielts')->name('ielts.')->group(function () {
    Route::get('/', [IeltsController::class, 'index'])->name('index');
    Route::post('/{ieltsTest}/start', [IeltsController::class, 'start'])->name('start');
    Route::get('/attempt/{attempt}', [IeltsController::class, 'take'])->name('take');
    Route::post('/attempt/{attempt}/answer', [IeltsController::class, 'saveAnswer'])->name('answer');
    Route::post('/attempt/{attempt}/submit', [IeltsController::class, 'submit'])->name('submit');
    Route::get('/result/{result}', [IeltsController::class, 'result'])->name('result');
});
