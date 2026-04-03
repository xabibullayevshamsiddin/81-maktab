<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\PublicPostController;
use App\Http\Controllers\TeacherCommentController;
use App\Http\Controllers\PublicTeacherController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\TeacherCourseController;
use App\Http\Controllers\PublicCourseController;
use App\Http\Controllers\AdminCourseController;
use App\Http\Controllers\AdminCommentController;
use App\Http\Controllers\AdminCourseEnrollmentController;
use App\Http\Controllers\CourseEnrollmentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TeacherEnrollmentController;
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

Route::get('/',[HomeController::class, 'home'])->name('home');
Route::get('about',[HomeController::class, 'about'])->name('about');
Route::get('courses',[PublicCourseController::class, 'index'])->name('courses');
Route::post('courses/{course}/enroll', [CourseEnrollmentController::class, 'store'])
    ->middleware('auth')
    ->name('courses.enroll');
Route::delete('courses/{course}/enroll', [CourseEnrollmentController::class, 'destroy'])
    ->middleware('auth')
    ->name('courses.enroll.cancel');
Route::get('post',[PublicPostController::class, 'index'])->name('post');
Route::get('post/{post:slug}', [PublicPostController::class, 'show'])->name('post.show');
Route::post('post/{post:slug}/comments', [PublicPostController::class, 'storeComment'])->name('post.comments.store');
Route::put('post/{post:slug}/comments/{comment}', [PublicPostController::class, 'updateComment'])->name('post.comments.update');
Route::delete('post/{post:slug}/comments/{comment}', [PublicPostController::class, 'destroyComment'])->name('post.comments.destroy');
Route::post('post/{post:slug}/comments/{comment}/like', [PublicPostController::class, 'toggleCommentLike'])->name('post.comments.like');
Route::post('post/{post:slug}/like', [PublicPostController::class, 'toggleLike'])->name('post.like');
Route::post('teacher/comments', [TeacherCommentController::class, 'store'])->name('teacher.comments.store');
Route::put('teacher/comments/{comment}', [TeacherCommentController::class, 'update'])->name('teacher.comments.update');
Route::delete('teacher/comments/{comment}', [TeacherCommentController::class, 'destroy'])->name('teacher.comments.destroy');
Route::post('teacher/comments/{comment}/like', [TeacherCommentController::class, 'toggleCommentLike'])->name('teacher.comments.like');
Route::get('teacher',[PublicTeacherController::class, 'index'])->name('teacher');
Route::get('teacher/{teacher:slug}',[PublicTeacherController::class, 'show'])->name('teacher.show');
Route::post('teacher/{teacher:slug}/like', [PublicTeacherController::class, 'toggleLike'])->name('teacher.like');
Route::get('contact',[HomeController::class, 'contact'])->name('contact');

// login
Route::get('login',[AuthController::class, 'login'])->name('login');
Route::post('authenticate',[AuthController::class,'authenticate'])->name('authenticate');
Route::post('authanticate',[AuthController::class,'authenticate'])->name('authanticate');
Route::get('login/verify-code', [AuthController::class, 'showLoginVerify'])->name('login.verify.form');
Route::post('login/verify-code', [AuthController::class, 'verifyLoginCode'])->name('login.verify');
Route::post('login/verify-code/resend', [AuthController::class, 'resendLoginCode'])->name('login.verify.resend');
// register
Route::get('register',[AuthController::class, 'register'])->name('register');
Route::post('register',[AuthController::class, 'registerStore'])->name('register.store');
Route::post('regiter',[AuthController::class, 'registerStore'])->name('regiter_store');
Route::get('register/verify-code', [AuthController::class, 'showRegisterVerify'])->name('register.verify.form');
Route::post('register/verify-code', [AuthController::class, 'verifyRegisterCode'])->name('register.verify');
Route::post('register/verify-code/resend', [AuthController::class, 'resendRegisterCode'])->name('register.verify.resend');

Route::post('logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::put('profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('profile/email/request', [ProfileController::class, 'requestEmailChange'])->name('profile.email.request');
    Route::post('profile/email/verify', [ProfileController::class, 'verifyEmailChange'])->name('profile.email.verify');
    Route::post('profile/email/resend', [ProfileController::class, 'resendEmailChange'])->name('profile.email.resend');
    Route::post('profile/email/cancel', [ProfileController::class, 'cancelEmailChange'])->name('profile.email.cancel');

    Route::get('profile/kurs-arizalari', [TeacherEnrollmentController::class, 'index'])->name('teacher.enrollments.index');
    Route::post('profile/kurs-arizalari/{enrollment}/tasdiqlash', [TeacherEnrollmentController::class, 'approve'])->name('teacher.enrollments.approve');
    Route::post('profile/kurs-arizalari/{enrollment}/rad-etish', [TeacherEnrollmentController::class, 'reject'])->name('teacher.enrollments.reject');
    Route::delete('profile/kurs-arizalari/{enrollment}', [TeacherEnrollmentController::class, 'destroy'])->name('teacher.enrollments.destroy');
});

Route::middleware(['auth', 'role:super_admin,admin,editor,moderator,teacher'])->group(function(){
    Route::get('dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
});

Route::middleware(['auth', 'role:super_admin,admin'])->group(function(){
    Route::get('user', [AdminController::class, 'user'])->name('user');
    Route::put('user/{user}', [AdminController::class, 'updateUser'])->name('user.update');
    Route::delete('user/{user}', [AdminController::class, 'destroyUser'])->name('user.destroy');
    Route::get('notification', [AdminController::class, 'notification'])->name('notification');
});

Route::middleware(['auth', 'role:teacher,super_admin,admin'])->group(function(){
    Route::get('course-open', [TeacherCourseController::class, 'create'])->name('teacher.courses.create');
    Route::post('course-open', [TeacherCourseController::class, 'store'])->name('teacher.courses.store');
    Route::get('course-open/{course}/verify', [TeacherCourseController::class, 'verifyForm'])->name('teacher.courses.verify.form');
    Route::post('course-open/{course}/verify', [TeacherCourseController::class, 'verifyCode'])->name('teacher.courses.verify');
    Route::post('course-open/{course}/resend', [TeacherCourseController::class, 'resendCode'])->name('teacher.courses.verify.resend');
});


Route::prefix('admin')->middleware(['auth', 'role:super_admin,admin,editor,moderator,teacher'])->group(function(){
    Route::middleware('role:super_admin,admin,editor')->group(function () {
        Route::resource('posts', PostController::class);
        Route::resource('categories', CategoryController::class)->except('show');
    });

    Route::middleware('role:super_admin,admin,editor,moderator')->group(function () {
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
    });

    Route::middleware('role:super_admin,admin,teacher')->group(function () {
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
});

// Qolgan barcha yo‘llar uchun custom 404 sahifa
Route::fallback(function () {
    abort(404);
});
