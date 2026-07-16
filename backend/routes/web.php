<?php

use App\Http\Controllers\PublicController;
use Illuminate\Support\Facades\Route;

/*
 * Public marketing site — server-rendered Blade (replaces the Next.js frontend).
 * Dashboard / auth pages are added in a later phase.
 */
Route::get('/', [PublicController::class, 'home'])->name('home');
Route::get('/about', [PublicController::class, 'about'])->name('about');
Route::get('/pricing', [PublicController::class, 'pricing'])->name('pricing');
Route::get('/instructors', [PublicController::class, 'instructors'])->name('instructors');
Route::get('/contact', [PublicController::class, 'contact'])->name('contact');
Route::get('/courses', [PublicController::class, 'courses'])->name('courses');
Route::get('/courses/{slug}', [PublicController::class, 'courseShow'])->name('courses.show');
