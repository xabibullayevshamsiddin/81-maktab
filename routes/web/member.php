<?php

use App\Http\Controllers\ChatController;
use App\Http\Controllers\ExamController;
use App\Http\Controllers\FeatureRequestController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ResultController;
use App\Http\Controllers\TeacherEnrollmentController;
use App\Http\Controllers\TeacherExamController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::get('chat/messages', [ChatController::class, 'messages'])->name('chat.messages');
    Route::get('chat/user/{user}/preview', [ChatController::class, 'userPreview'])
        ->middleware('throttle:60,1')
        ->name('chat.user.preview');
    Route::post('chat/user/{user}/deactivate', [ChatController::class, 'superAdminDeactivateUser'])
        ->middleware('throttle:30,1')
        ->name('chat.user.deactivate');
    Route::post('chat/user/{user}/activate', [ChatController::class, 'superAdminActivateUser'])
        ->middleware('throttle:30,1')
        ->name('chat.user.activate');
    Route::post('chat/send', [ChatController::class, 'send'])->middleware(['throttle:chat-send', 'active'])->name('chat.send');
    Route::delete('chat/{chatMessage}', [ChatController::class, 'destroy'])->middleware(['active'])->name('chat.destroy');
    Route::post('chat/block/{user}', [ChatController::class, 'blockUser'])->middleware(['active'])->name('chat.block');

    Route::get('profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::put('profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('profile/natijalar/export', [ProfileController::class, 'exportResults'])->name('profile.results.export');
    Route::get('profile/results/export', [ProfileController::class, 'exportResults']);
    Route::post('profile/email/request', [ProfileController::class, 'requestEmailChange'])->name('profile.email.request');
    Route::post('profile/email/verify', [ProfileController::class, 'verifyEmailChange'])->name('profile.email.verify');
    Route::post('profile/email/resend', [ProfileController::class, 'resendEmailChange'])->name('profile.email.resend');
    Route::post('profile/email/cancel', [ProfileController::class, 'cancelEmailChange'])->name('profile.email.cancel');
    Route::post('profile/password/confirm', [ProfileController::class, 'confirmPasswordChange'])->name('profile.password.confirm');
    Route::post('profile/password/update', [ProfileController::class, 'updatePassword'])->name('profile.password.update');

    Route::post('feature-requests', [FeatureRequestController::class, 'store'])->name('feature-requests.store');
    Route::post('feature-requests/{featureRequest}/vote', [FeatureRequestController::class, 'vote'])->name('feature-requests.vote');
    Route::post('feature-requests/{featureRequest}/replies', [FeatureRequestController::class, 'storeReply'])
        ->middleware('role:super_admin,admin,moderator')
        ->name('feature-requests.replies.store');
    Route::delete('feature-request-replies/{reply}', [FeatureRequestController::class, 'destroyReply'])
        ->name('feature-requests.replies.destroy');
    Route::delete('feature-requests/{featureRequest}', [FeatureRequestController::class, 'destroy'])->name('feature-requests.destroy');

    Route::get('profile/exams/results/{result}', [TeacherExamController::class, 'showResult'])
        ->whereNumber('result')
        ->name('profile.exams.results.show');
    Route::get('profile/exams/results/export', [TeacherExamController::class, 'exportResults'])->name('profile.exams.results.export');
    Route::get('profile/exams/result/export', [TeacherExamController::class, 'exportResults']);

    Route::middleware(['role:super_admin,admin,editor,moderator,teacher'])->group(function () {
        Route::get('profile/exams', [TeacherExamController::class, 'index'])->name('profile.exams.index');
        Route::get('profile/exams/create', [TeacherExamController::class, 'create'])->name('profile.exams.create');
        Route::post('profile/exams', [TeacherExamController::class, 'store'])->name('profile.exams.store');
        Route::get('profile/exams/{exam}/edit', [TeacherExamController::class, 'edit'])->name('profile.exams.edit');
        Route::put('profile/exams/{exam}', [TeacherExamController::class, 'update'])->name('profile.exams.update');
        Route::delete('profile/exams/{exam}', [TeacherExamController::class, 'destroy'])->name('profile.exams.destroy');

        Route::get('profile/exams/{exam}/questions', [TeacherExamController::class, 'questionsIndex'])->name('profile.exams.questions.index');
        Route::get('profile/exams/{exam}/questions/create', [TeacherExamController::class, 'questionCreate'])->name('profile.exams.questions.create');
        Route::post('profile/exams/{exam}/questions', [TeacherExamController::class, 'questionStore'])->name('profile.exams.questions.store');
        Route::get('profile/exams/{exam}/questions/{question}/edit', [TeacherExamController::class, 'questionEdit'])->name('profile.exams.questions.edit');
        Route::put('profile/exams/{exam}/questions/{question}', [TeacherExamController::class, 'questionUpdate'])->name('profile.exams.questions.update');
        Route::delete('profile/exams/{exam}/questions/{question}', [TeacherExamController::class, 'questionDestroy'])->name('profile.exams.questions.destroy');

        Route::get('profile/exams/results', [TeacherExamController::class, 'results'])->name('profile.exams.results');
        Route::post('profile/exams/results/{result}/grade/{answer}', [TeacherExamController::class, 'gradeTextAnswer'])
            ->whereNumber('result')
            ->whereNumber('answer')
            ->name('profile.exams.grade');
    });

    Route::get('profile/kurs-arizalari', [TeacherEnrollmentController::class, 'index'])->name('teacher.enrollments.index');
    Route::post('profile/kurs-arizalari/{enrollment}/tasdiqlash', [TeacherEnrollmentController::class, 'approve'])->name('teacher.enrollments.approve');
    Route::post('profile/kurs-arizalari/{enrollment}/rad-etish', [TeacherEnrollmentController::class, 'reject'])->name('teacher.enrollments.reject');
    Route::delete('profile/kurs-arizalari/{enrollment}', [TeacherEnrollmentController::class, 'destroy'])->name('teacher.enrollments.destroy');

    Route::get('exams', [ExamController::class, 'index'])->name('exam.index');
    Route::get('exams/{exam}', [ExamController::class, 'startPage'])->name('exam.start.page');
    Route::post('exams/{exam}/start', [ExamController::class, 'start'])->name('exam.start');
    Route::get('exam-session/{result}', [ExamController::class, 'session'])->name('exam.session');
    Route::post('exam-session/{result}/answer', [ExamController::class, 'answer'])->name('exam.answer');
    Route::post('exam-session/{result}/violation', [ExamController::class, 'reportViolation'])
        ->middleware('throttle:90,1')
        ->name('exam.violation');
    Route::post('exam-session/{result}/submit', [ExamController::class, 'submit'])->name('exam.submit');
    Route::get('exam-results/{result}', [ResultController::class, 'show'])->name('exam.result.show');
});
