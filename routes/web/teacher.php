<?php

use App\Http\Controllers\TeacherCourseController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:teacher'])->group(function () {
    Route::post('course-open/request', [TeacherCourseController::class, 'requestAccess'])->name('teacher.courses.request');
});

Route::middleware(['auth', 'role:teacher,super_admin,admin'])->group(function () {
    Route::get('course-open', [TeacherCourseController::class, 'create'])->name('teacher.courses.create');
    Route::post('course-open', [TeacherCourseController::class, 'store'])->name('teacher.courses.store');
    Route::get('course-open/{course}/edit', [TeacherCourseController::class, 'edit'])->name('teacher.courses.edit');
    Route::put('course-open/{course}', [TeacherCourseController::class, 'update'])->name('teacher.courses.update');
    Route::delete('course-open/{course}', [TeacherCourseController::class, 'destroy'])->name('teacher.courses.destroy');
    Route::get('course-open/{course}/verify', [TeacherCourseController::class, 'verifyForm'])->name('teacher.courses.verify.form');
    Route::post('course-open/{course}/verify', [TeacherCourseController::class, 'verifyCode'])->name('teacher.courses.verify');
    Route::post('course-open/{course}/resend', [TeacherCourseController::class, 'resendCode'])->name('teacher.courses.verify.resend');
});
