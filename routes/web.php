<?php

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
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\CourseEnrollmentController;
use App\Http\Controllers\ExamController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PublicCourseController;
use App\Http\Controllers\PublicPostController;
use App\Http\Controllers\PublicTeacherController;
use App\Http\Controllers\ResultController;
use App\Http\Controllers\TeacherCommentController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\TeacherCourseController;
use App\Http\Controllers\TeacherEnrollmentController;
use App\Http\Controllers\TeacherExamController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', [HomeController::class, 'home'])->name('home');
Route::get('lang/{locale}', [LocaleController::class, 'switch'])->name('locale.switch');

Route::get('about', [HomeController::class, 'about'])->name('about');
Route::get('courses', [PublicCourseController::class, 'index'])->name('courses');
Route::get('courses/{course}', [PublicCourseController::class, 'show'])->name('courses.show');
Route::post('courses/{course}/enroll', [CourseEnrollmentController::class, 'store'])
    ->middleware(['auth', 'active'])
    ->name('courses.enroll');
Route::delete('courses/{course}/enroll', [CourseEnrollmentController::class, 'destroy'])
    ->middleware(['auth', 'active'])
    ->name('courses.enroll.cancel');
Route::get('taqvim', [CalendarController::class, 'index'])->name('calendar');
Route::get('post', [PublicPostController::class, 'index'])->name('post');
Route::get('post/{post:slug}', [PublicPostController::class, 'show'])->name('post.show');
Route::post('post/{post:slug}/comments', [PublicPostController::class, 'storeComment'])
    ->middleware(['throttle:comments', 'active'])
    ->name('post.comments.store');
Route::put('post/{post:slug}/comments/{comment}', [PublicPostController::class, 'updateComment'])
    ->middleware(['throttle:comments', 'active'])
    ->name('post.comments.update');
Route::delete('post/{post:slug}/comments/{comment}', [PublicPostController::class, 'destroyComment'])
    ->middleware(['active'])
    ->name('post.comments.destroy');
Route::post('post/{post:slug}/comments/{comment}/like', [PublicPostController::class, 'toggleCommentLike'])
    ->middleware(['active'])
    ->name('post.comments.like');
Route::post('post/{post:slug}/like', [PublicPostController::class, 'toggleLike'])
    ->middleware(['active'])
    ->name('post.like');
Route::post('teacher/{teacher:slug}/comments', [TeacherCommentController::class, 'store'])
    ->middleware(['throttle:comments', 'active'])
    ->name('teacher.comments.store');
Route::put('teacher/comments/{comment}', [TeacherCommentController::class, 'update'])
    ->middleware(['throttle:comments', 'active'])
    ->name('teacher.comments.update');
Route::delete('teacher/comments/{comment}', [TeacherCommentController::class, 'destroy'])
    ->middleware(['active'])
    ->name('teacher.comments.destroy');
Route::post('teacher/comments/{comment}/like', [TeacherCommentController::class, 'toggleCommentLike'])
    ->middleware(['active'])
    ->name('teacher.comments.like');
Route::get('teacher', [PublicTeacherController::class, 'index'])->name('teacher');
Route::get('teacher/{teacher:slug}', [PublicTeacherController::class, 'show'])->name('teacher.show');
Route::post('teacher/{teacher:slug}/like', [PublicTeacherController::class, 'toggleLike'])
    ->middleware(['active'])
    ->name('teacher.like');
Route::get('contact', [HomeController::class, 'contact'])->name('contact');
Route::post('contact', [HomeController::class, 'storeContact'])
    ->middleware('throttle:10,1')
    ->name('contact.store');

// login
Route::get('login', [AuthController::class, 'login'])->name('login');
Route::post('authenticate', [AuthController::class, 'authenticate'])->name('authenticate');
Route::get('auth/google/redirect', [AuthController::class, 'redirectToGoogle'])->name('auth.google.redirect');
Route::get('auth/google/callback', [AuthController::class, 'handleGoogleCallback'])->name('auth.google.callback');
Route::get('login/verify-code', [AuthController::class, 'showLoginVerify'])->name('login.verify.form');
Route::post('login/verify-code', [AuthController::class, 'verifyLoginCode'])->name('login.verify');
Route::post('login/verify-code/resend', [AuthController::class, 'resendLoginCode'])->name('login.verify.resend');
// register
Route::get('register', [AuthController::class, 'register'])->name('register');
Route::post('register', [AuthController::class, 'registerStore'])->name('register.store');
Route::get('register/verify-code', [AuthController::class, 'showRegisterVerify'])->name('register.verify.form');
Route::post('register/verify-code', [AuthController::class, 'verifyRegisterCode'])->name('register.verify');
Route::post('register/verify-code/resend', [AuthController::class, 'resendRegisterCode'])->name('register.verify.resend');

// password reset
Route::get('forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.forgot.form');
Route::post('forgot-password', [AuthController::class, 'sendPasswordResetCode'])->name('password.forgot.send');
Route::get('reset-password', [AuthController::class, 'showPasswordResetForm'])->name('password.reset.form');
Route::post('reset-password', [AuthController::class, 'resetPassword'])->name('password.reset');
Route::post('reset-password/resend', [AuthController::class, 'resendPasswordResetCode'])->name('password.reset.resend');

Route::post('logout', [AuthController::class, 'logout'])->name('logout');

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
    Route::post('profile/email/request', [ProfileController::class, 'requestEmailChange'])->name('profile.email.request');
    Route::post('profile/email/verify', [ProfileController::class, 'verifyEmailChange'])->name('profile.email.verify');
    Route::post('profile/email/resend', [ProfileController::class, 'resendEmailChange'])->name('profile.email.resend');
    Route::post('profile/email/cancel', [ProfileController::class, 'cancelEmailChange'])->name('profile.email.cancel');
    Route::post('profile/password/confirm', [ProfileController::class, 'confirmPasswordChange'])->name('profile.password.confirm');
    Route::post('profile/password/update', [ProfileController::class, 'updatePassword'])->name('profile.password.update');

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
        Route::get('profile/exams/results/export', [TeacherExamController::class, 'exportResults'])->name('profile.exams.results.export');
        Route::get('profile/exams/results/{result}', [TeacherExamController::class, 'showResult'])->name('profile.exams.results.show');
        Route::post('profile/exams/results/{result}/grade/{answer}', [TeacherExamController::class, 'gradeTextAnswer'])->name('profile.exams.grade');
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

Route::middleware(['auth', 'role:super_admin,admin,editor,moderator'])->group(function () {
    Route::get('dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
});

Route::middleware(['auth', 'role:super_admin,admin'])->group(function () {
    Route::get('user', [AdminController::class, 'user'])->name('user');
    Route::put('user/{user}', [AdminController::class, 'updateUser'])->name('user.update');
    Route::delete('user/{user}', [AdminController::class, 'destroyUser'])->name('user.destroy');
    Route::post('user/{user}/password-reset', [AuthController::class, 'adminSendPasswordReset'])->name('user.password-reset.send');
    Route::post('user/{user}/course-open/approve', [AdminController::class, 'approveCourseOpenRequest'])->name('user.course-open.approve');
    Route::post('user/{user}/course-open/reject', [AdminController::class, 'rejectCourseOpenRequest'])->name('user.course-open.reject');
});

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

    Route::middleware('role:super_admin,admin')->group(function () {
        Route::resource('teachers', TeacherController::class);
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
        Route::get('exam-results/{result}', [AdminExamController::class, 'showResult'])->name('admin.exams.results.show');
        Route::post('exam-results/{result}/grade/{answer}', [AdminExamController::class, 'gradeTextAnswer'])->name('admin.exams.results.grade');
        Route::delete('exam-results/{result}', [AdminExamController::class, 'destroyResult'])->name('admin.exams.results.destroy');
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
        Route::get('settings', [AdminSettingsController::class, 'index'])->name('admin.settings.index');
        Route::put('settings', [AdminSettingsController::class, 'update'])->name('admin.settings.update');
    });
});

Route::post('ai-chat', [App\Http\Controllers\SiteAiController::class, 'generate'])->middleware(['auth', 'throttle:30,1', 'active'])->name('ai.chat');

// Qolgan barcha yo‘llar uchun custom 404 sahifa
Route::fallback(function () {
    abort(404);
});
