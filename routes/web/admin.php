<?php

use App\Http\Controllers\AdminAiKnowledgeController;
use App\Http\Controllers\AdminAiReviewController;
use App\Http\Controllers\AdminCalendarEventController;
use App\Http\Controllers\AdminCommentController;
use App\Http\Controllers\AdminContactMessageController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminCourseController;
use App\Http\Controllers\AdminCourseEnrollmentController;
use App\Http\Controllers\AdminExamController;
use App\Http\Controllers\AdminQuestionController;
use App\Http\Controllers\AdminSettingsController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\FeatureRequestController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\TeacherCourseController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:super_admin,admin,editor,moderator'])->group(function () {
    Route::get('dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
});

Route::middleware(['auth', 'role:super_admin,admin'])->group(function () {
    Route::get('user', [AdminController::class, 'user'])->name('user');
    Route::put('user/{user}', [AdminController::class, 'updateUser'])->name('user.update');
    Route::delete('user/{user}', [AdminController::class, 'destroyUser'])->name('user.destroy');
    Route::post('feature-requests/{featureRequest}/status', [FeatureRequestController::class, 'updateStatus'])->name('feature-requests.status');
    Route::post('user/{user}/password-reset', [AuthController::class, 'adminSendPasswordReset'])->name('user.password-reset.send');
    Route::post('user/{user}/course-open/approve', [AdminController::class, 'approveCourseOpenRequest'])->name('user.course-open.approve');
    Route::post('user/{user}/course-open/reject', [AdminController::class, 'rejectCourseOpenRequest'])->name('user.course-open.reject');
});

Route::prefix('admin')->middleware(['auth', 'role:super_admin,admin,editor,moderator'])->group(function () {
    Route::middleware('role:super_admin,admin,editor')->group(function () {
        Route::resource('posts', PostController::class);
        Route::resource('categories', CategoryController::class)->except('show');
        Route::resource('calendar-events', AdminCalendarEventController::class)->except(['show']);
    });

    Route::middleware('role:super_admin,admin,moderator')->group(function () {
        Route::get('contact-messages', [AdminContactMessageController::class, 'index'])->name('admin.contact-messages.index');
        Route::get('contact-messages/{contactMessage}', [AdminContactMessageController::class, 'show'])->name('admin.contact-messages.show');
        Route::post('contact-messages/{contactMessage}/read', [AdminContactMessageController::class, 'markRead'])->name('admin.contact-messages.read');
        Route::post('contact-messages/{contactMessage}/block', [AdminContactMessageController::class, 'block'])->name('admin.contact-messages.block');
        Route::post('contact-messages/{contactMessage}/unblock', [AdminContactMessageController::class, 'unblock'])->name('admin.contact-messages.unblock');
        Route::delete('contact-messages/{contactMessage}', [AdminContactMessageController::class, 'destroy'])->name('admin.contact-messages.destroy');

        Route::get('ai-reviews', [AdminAiReviewController::class, 'index'])->name('admin.ai-reviews.index');
        Route::delete('ai-reviews/{interaction}', [AdminAiReviewController::class, 'destroy'])->name('admin.ai-reviews.destroy');
        Route::delete('ai-reviews', [AdminAiReviewController::class, 'destroyUnhelpful'])->name('admin.ai-reviews.destroy-unhelpful');
    });

    Route::middleware('role:super_admin,admin,moderator')->group(function () {
        Route::get('comments', [AdminCommentController::class, 'index'])->name('admin.comments.index');
        Route::get('comments/{type}/{id}/edit', [AdminCommentController::class, 'edit'])
            ->where(['type' => 'post|teacher', 'id' => '[0-9]+'])
            ->name('admin.comments.edit');
        Route::put('comments/{type}/{id}', [AdminCommentController::class, 'update'])
            ->where(['type' => 'post|teacher', 'id' => '[0-9]+'])
            ->name('admin.comments.update');
        Route::delete('comments/{type}/{id}', [AdminCommentController::class, 'destroy'])
            ->where(['type' => 'post|teacher', 'id' => '[0-9]+'])
            ->name('admin.comments.destroy');
    });

    Route::middleware('role:super_admin,admin')->group(function () {
        Route::post('comments/users/{user}/block', [AdminCommentController::class, 'blockUser'])->name('admin.comments.block-user');
    });

    Route::middleware('role:super_admin,admin,editor')->group(function () {
        Route::resource('teachers', TeacherController::class);
    });

    Route::middleware('role:super_admin,admin')->group(function () {
        Route::get('exams', [AdminExamController::class, 'index'])->name('admin.exams.index');
        Route::get('exams/create', [AdminExamController::class, 'create'])->name('admin.exams.create');
        Route::post('exams', [AdminExamController::class, 'store'])->name('admin.exams.store');
        Route::get('exams/{exam}/edit', [AdminExamController::class, 'edit'])->name('admin.exams.edit');
        Route::put('exams/{exam}', [AdminExamController::class, 'update'])->name('admin.exams.update');
        Route::delete('exams/{exam}', [AdminExamController::class, 'destroy'])->name('admin.exams.destroy');

        Route::get('exams/{exam}/questions', [AdminQuestionController::class, 'index'])->name('admin.exams.questions.index');
        Route::get('exams/{exam}/questions/create', [AdminQuestionController::class, 'create'])->name('admin.exams.questions.create');
        Route::post('exams/{exam}/questions', [AdminQuestionController::class, 'store'])->name('admin.exams.questions.store');
        Route::get('exams/{exam}/questions/{question}/edit', [AdminQuestionController::class, 'edit'])->name('admin.exams.questions.edit');
        Route::put('exams/{exam}/questions/{question}', [AdminQuestionController::class, 'update'])->name('admin.exams.questions.update');
        Route::delete('exams/{exam}/questions/{question}', [AdminQuestionController::class, 'destroy'])->name('admin.exams.questions.destroy');
        Route::get('exam-results', [AdminExamController::class, 'results'])->name('admin.exams.results');
        Route::get('exam-results/export', [AdminExamController::class, 'exportResults'])->name('admin.exams.results.export');
        Route::get('exams/results/export', [AdminExamController::class, 'exportResults']);
        Route::get('exam-results/{result}', [AdminExamController::class, 'showResult'])
            ->whereNumber('result')
            ->name('admin.exams.results.show');
        Route::post('exam-results/{result}/grade/{answer}', [AdminExamController::class, 'gradeTextAnswer'])
            ->whereNumber('result')
            ->whereNumber('answer')
            ->name('admin.exams.results.grade');
        Route::delete('exam-results/{result}', [AdminExamController::class, 'destroyResult'])
            ->whereNumber('result')
            ->name('admin.exams.results.destroy');
    });

    Route::middleware('role:super_admin,admin')->group(function () {
        Route::get('course-requests', [AdminCourseController::class, 'requests'])->name('admin.courses.requests');
        Route::get('courses', [AdminCourseController::class, 'index'])->name('admin.courses.index');
        Route::put('courses/{course}/status', [AdminCourseController::class, 'updateStatus'])->name('admin.courses.status');
        Route::get('courses/{course}/edit', [TeacherCourseController::class, 'edit'])->name('admin.courses.edit');
        Route::put('courses/{course}', [TeacherCourseController::class, 'update'])->name('admin.courses.update');
        Route::delete('courses/{course}', [TeacherCourseController::class, 'destroy'])->name('admin.courses.destroy');
        Route::get('courses/{course}/enrollments', [AdminCourseEnrollmentController::class, 'index'])->name('admin.courses.enrollments');
        Route::post('courses/{course}/enrollments/{enrollment}/approve', [AdminCourseEnrollmentController::class, 'approve'])->name('admin.courses.enrollments.approve');
        Route::post('courses/{course}/enrollments/{enrollment}/reject', [AdminCourseEnrollmentController::class, 'reject'])->name('admin.courses.enrollments.reject');
        Route::delete('courses/{course}/enrollments/{enrollment}', [AdminCourseEnrollmentController::class, 'destroy'])->name('admin.courses.enrollments.destroy');
    });

    Route::middleware('role:super_admin,admin')->group(function () {
        Route::get('course-enrollments', [AdminCourseEnrollmentController::class, 'indexAll'])->name('admin.course-enrollments.index');
    });

    Route::middleware('role:super_admin')->group(function () {
        Route::resource('ai-knowledges', AdminAiKnowledgeController::class)->except(['show']);
        Route::get('settings', [AdminSettingsController::class, 'index'])->name('admin.settings.index');
        Route::put('settings', [AdminSettingsController::class, 'update'])->name('admin.settings.update');
    });
});
