<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\PublicPostController;
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
Route::get('courses',[HomeController::class, 'courses'])->name('courses');
Route::get('post',[PublicPostController::class, 'index'])->name('post');
Route::get('post/{post:slug}', [PublicPostController::class, 'show'])->name('post.show');
Route::post('post/{post:slug}/comments', [PublicPostController::class, 'storeComment'])->name('post.comments.store');
Route::put('post/{post:slug}/comments/{comment}', [PublicPostController::class, 'updateComment'])->name('post.comments.update');
Route::delete('post/{post:slug}/comments/{comment}', [PublicPostController::class, 'destroyComment'])->name('post.comments.destroy');
Route::post('post/{post:slug}/like', [PublicPostController::class, 'toggleLike'])->name('post.like');
Route::get('teacher',[HomeController::class, 'teacher'])->name('teacher');
Route::get('contact',[HomeController::class, 'contact'])->name('contact');

// login
Route::get('login',[AuthController::class, 'login'])->name('login');
Route::post('authenticate',[AuthController::class,'authenticate'])->name('authenticate');
Route::post('authanticate',[AuthController::class,'authenticate'])->name('authanticate');
// register
Route::get('register',[AuthController::class, 'register'])->name('register');
Route::post('register',[AuthController::class, 'registerStore'])->name('register.store');
Route::post('regiter',[AuthController::class, 'registerStore'])->name('regiter_store');

Route::post('logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware('auth')->group(function(){
    Route::get('dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('user', [AdminController::class, 'user'])->name('user');
    Route::put('user/{user}', [AdminController::class, 'updateUser'])->name('user.update');
    Route::delete('user/{user}', [AdminController::class, 'destroyUser'])->name('user.destroy');
    Route::get('notification', [AdminController::class, 'notification'])->name('notification');
});


Route::prefix('admin')->middleware('auth')->group(function(){
    Route::resource('posts', PostController::class);
    Route::resource('categories', CategoryController::class)->except('show');
});
