<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LearnController;
use App\Http\Controllers\LiveController;
use App\Http\Controllers\PublicController;
use App\Http\Controllers\QuizWebController;
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

    // Catalog + enrollment + purchases
    Route::get('/dashboard/courses', [CatalogController::class, 'index'])->name('catalog');
    Route::post('/dashboard/courses/{course}/enroll', [CatalogController::class, 'enroll'])->name('enroll');
    Route::get('/dashboard/purchases', [CatalogController::class, 'purchases'])->name('purchases');

    // Student learning
    Route::get('/dashboard/learn', [LearnController::class, 'index'])->name('learn');
    Route::get('/dashboard/learn/{slug}', [LearnController::class, 'show'])->name('learn.show');
    Route::post('/dashboard/courses/{course}/lessons/{lesson}/answer', [LearnController::class, 'answer'])->name('learn.answer');
    Route::get('/dashboard/certificate/{slug}', [LearnController::class, 'certificate'])->name('certificate');

    // Quizzes (students take; creators see their own)
    Route::middleware('permission:quizzes.view')->group(function () {
        Route::get('/dashboard/quizzes', [QuizWebController::class, 'index'])->name('quizzes');
        Route::get('/dashboard/quizzes/{quiz}/take', [QuizWebController::class, 'take'])->name('quizzes.take');
        Route::post('/dashboard/quizzes/{quiz}/submit', [QuizWebController::class, 'submit'])->name('quizzes.submit');
    });

    // Quiz authoring (creators build their own quizzes)
    Route::middleware('permission:quizzes.create')->group(function () {
        Route::post('/dashboard/quizzes', [QuizWebController::class, 'store'])->name('quizzes.store');
        Route::get('/dashboard/quizzes/{quiz}/edit', [QuizWebController::class, 'edit'])->name('quizzes.edit');
        Route::put('/dashboard/quizzes/{quiz}', [QuizWebController::class, 'update'])->name('quizzes.update');
        Route::post('/dashboard/quizzes/{quiz}/publish', [QuizWebController::class, 'togglePublish'])->name('quizzes.publish');
        Route::delete('/dashboard/quizzes/{quiz}', [QuizWebController::class, 'destroy'])->name('quizzes.destroy');
        Route::post('/dashboard/quizzes/{quiz}/questions', [QuizWebController::class, 'storeQuestion'])->name('quizzes.questions.store');
        Route::put('/dashboard/quizzes/{quiz}/questions/{question}', [QuizWebController::class, 'updateQuestion'])->name('quizzes.questions.update');
        Route::delete('/dashboard/quizzes/{quiz}/questions/{question}', [QuizWebController::class, 'destroyQuestion'])->name('quizzes.questions.destroy');
    });

    // Live classes
    Route::get('/dashboard/live', [LiveController::class, 'index'])->middleware('permission:live.view')->name('live');

    // Admin
    Route::middleware('permission:modules.view')->group(function () {
        Route::get('/dashboard/modules', [AdminController::class, 'modules'])->name('modules');
        Route::post('/dashboard/modules/{module}/toggle', [AdminController::class, 'toggleModule']);
    });
    Route::middleware('permission:gateways.view')->group(function () {
        Route::get('/dashboard/gateways', [AdminController::class, 'gateways'])->name('gateways');
        Route::post('/dashboard/gateways/{gateway}/toggle', [AdminController::class, 'toggleGateway']);
    });
    Route::middleware('permission:users.view')->group(function () {
        Route::get('/dashboard/users', [AdminController::class, 'users'])->name('users');
        Route::post('/dashboard/users/{user}/status', [AdminController::class, 'toggleUserStatus']);
        Route::post('/dashboard/users/{user}/role', [AdminController::class, 'setUserRole']);
    });
    // Site content editor (view needs settings.view; saving needs settings.manage)
    Route::get('/dashboard/content', [AdminController::class, 'content'])->middleware('permission:settings.view')->name('content');
    Route::middleware('permission:settings.manage')->group(function () {
        Route::put('/dashboard/content', [\App\Http\Controllers\Api\SiteContentController::class, 'update']);
        Route::put('/dashboard/content/text', [\App\Http\Controllers\Api\SiteContentController::class, 'saveText']);
    });
    // Settings + Roles
    Route::middleware('permission:settings.view')->group(function () {
        Route::get('/dashboard/settings', [AdminController::class, 'settings'])->name('settings');
        Route::put('/dashboard/settings', [AdminController::class, 'updateSettings']);
    });
    Route::get('/dashboard/roles', [AdminController::class, 'roles'])->middleware('permission:roles.view')->name('roles');
    // Platform: plans + tenants
    Route::middleware('permission:tenants.view')->group(function () {
        Route::get('/dashboard/platform/plans', [AdminController::class, 'plans'])->name('plans');
        Route::delete('/dashboard/platform/plans/{plan}', [AdminController::class, 'deletePlan']);
        Route::get('/dashboard/platform/tenants', [AdminController::class, 'tenants'])->name('tenants');
    });

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
