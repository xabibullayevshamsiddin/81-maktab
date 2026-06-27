<?php

use App\Http\Controllers\BookmarkController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\CourseEnrollmentController;
use App\Http\Controllers\FeatureRequestController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\PublicCourseController;
use App\Http\Controllers\PublicPostController;
use App\Http\Controllers\PublicTeacherController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\TeacherCommentController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'home'])->name('home');
Route::redirect('home', '/')->name('home.redirect');
Route::get('lang/{locale}', [LocaleController::class, 'switch'])->name('locale.switch');

Route::get('about', [HomeController::class, 'about'])->name('about');
Route::get('search', [HomeController::class, 'globalSearch'])->name('search');
Route::get('privacy-policy', [HomeController::class, 'privacyPolicy'])->name('privacy-policy');
Route::get('terms', [HomeController::class, 'terms'])->name('terms');

Route::get('courses', [PublicCourseController::class, 'index'])->name('courses');
Route::get('courses/{course}', [PublicCourseController::class, 'show'])
    ->name('courses.show')
    ->missing(function () {
        return redirect()
            ->route('courses')
            ->with('error', "Kurs topilmadi yoki o'chirilgan.")
            ->with('toast_type', 'warning');
    });
Route::post('courses/{course}/enroll', [CourseEnrollmentController::class, 'store'])
    ->middleware(['auth', 'active'])
    ->name('courses.enroll');
Route::delete('courses/{course}/enroll', [CourseEnrollmentController::class, 'destroy'])
    ->middleware(['auth', 'active'])
    ->name('courses.enroll.cancel');
Route::post('courses/{course}/bookmark', [BookmarkController::class, 'toggleCourse'])
    ->middleware(['auth', 'active', 'throttle:60,1'])
    ->name('course.bookmark.toggle');

Route::get('taqvim', [CalendarController::class, 'index'])->name('calendar');

Route::get('post', [PublicPostController::class, 'index'])->name('post');
Route::get('post/{post:slug}', [PublicPostController::class, 'show'])
    ->name('post.show')
    ->missing(function () {
        return redirect()
            ->route('post')
            ->with('error', "Yangilik topilmadi yoki o'chirilgan.")
            ->with('toast_type', 'warning');
    });
Route::get('post/{post:slug}/stats', [PublicPostController::class, 'stats'])
    ->name('post.stats');
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
Route::post('post/{post:slug}/bookmark', [BookmarkController::class, 'togglePost'])
    ->middleware(['auth', 'active', 'throttle:60,1'])
    ->name('post.bookmark.toggle');

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
Route::get('teacher/{teacher:slug}/stats', [PublicTeacherController::class, 'stats'])->name('teacher.stats');
Route::post('teacher/{teacher:slug}/like', [PublicTeacherController::class, 'toggleLike'])
    ->middleware(['active'])
    ->name('teacher.like');
Route::post('teacher/{teacher:slug}/bookmark', [BookmarkController::class, 'toggleTeacher'])
    ->middleware(['auth', 'active', 'throttle:60,1'])
    ->name('teacher.bookmark.toggle');

Route::get('contact', [HomeController::class, 'contact'])->name('contact');
Route::get('feature-requests', [FeatureRequestController::class, 'index'])->name('feature-requests.index');
Route::post('contact', [HomeController::class, 'storeContact'])
    ->middleware('throttle:10,1')
    ->name('contact.store');

Route::get('sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');
Route::get('robots.txt', [SitemapController::class, 'robots'])->name('robots');
