<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LearnController;
use App\Http\Controllers\PublicController;
use App\Http\Controllers\TeachingController;
use Illuminate\Support\Facades\Route;

/*
 * Public marketing site — server-rendered Blade (replaces the Next.js frontend).
 */
Route::get('/', [PublicController::class, 'home'])->name('home');
Route::get('/about', [PublicController::class, 'about'])->name('about');
Route::get('/pricing', [PublicController::class, 'pricing'])->name('pricing');
Route::get('/instructors', [PublicController::class, 'instructors'])->name('instructors');
Route::get('/contact', [PublicController::class, 'contact'])->name('contact');
Route::get('/courses', [PublicController::class, 'courses'])->name('courses');
Route::get('/courses/{slug}', [PublicController::class, 'courseShow'])->name('courses.show');

/*
 * Auth (session-based).
 */
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

/*
 * Dashboard (auth-only). Built out in Blade over phase 3.
 */
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'home'])->name('dashboard');

    // Student learning
    Route::get('/dashboard/learn', [LearnController::class, 'index'])->name('learn');
    Route::get('/dashboard/learn/{slug}', [LearnController::class, 'show'])->name('learn.show');
    Route::post('/dashboard/courses/{course}/lessons/{lesson}/answer', [LearnController::class, 'answer'])->name('learn.answer');
    Route::get('/dashboard/certificate/{slug}', [LearnController::class, 'certificate'])->name('certificate');

    // Teaching (instructors)
    Route::middleware('permission:courses.create')->group(function () {
        Route::get('/dashboard/teaching', [TeachingController::class, 'index'])->name('teaching');
        Route::post('/dashboard/teaching', [TeachingController::class, 'store']);
        Route::get('/dashboard/teaching/{course}', [TeachingController::class, 'edit'])->name('teaching.edit');
        Route::put('/dashboard/teaching/{course}', [TeachingController::class, 'updateCourse']);
        Route::post('/dashboard/teaching/{course}/publish', [TeachingController::class, 'publish']);
        Route::post('/dashboard/teaching/{course}/lessons', [TeachingController::class, 'storeLesson']);
        Route::put('/dashboard/teaching/{course}/lessons/{lesson}', [TeachingController::class, 'updateLesson']);
        Route::delete('/dashboard/teaching/{course}/lessons/{lesson}', [TeachingController::class, 'destroyLesson']);
        Route::post('/dashboard/teaching/{course}/lessons/{lesson}/video', [TeachingController::class, 'uploadVideo']);
    });
});
