<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BootstrapController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\ModuleController;
use App\Http\Controllers\Api\GatewayController;
use App\Http\Controllers\Api\CourseController;
use App\Http\Controllers\Api\LessonController;
use App\Http\Controllers\Api\EnrollmentController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ProgressController;
use App\Http\Controllers\Api\CheckoutController;
use App\Http\Controllers\Api\LiveSessionController;
use App\Http\Controllers\Api\QuizController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\SettingsController;
use App\Http\Controllers\Api\PlanController;
use App\Http\Controllers\Api\TenantController;

/*
|--------------------------------------------------------------------------
| Public routes
|--------------------------------------------------------------------------
*/
Route::get('/bootstrap', BootstrapController::class);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Public catalog (course detail respects lesson-lock rules per viewer).
Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/courses', [CourseController::class, 'index']);
Route::get('/courses/{slug}', [CourseController::class, 'show']);

// Public marketing data (instructors directory, pricing).
Route::get('/instructors', [\App\Http\Controllers\Api\PublicController::class, 'instructors']);
Route::get('/pricing', [\App\Http\Controllers\Api\PublicController::class, 'pricing']);

// Public editable marketing content (home/about/pricing/instructors/contact).
Route::get('/content', [\App\Http\Controllers\Api\SiteContentController::class, 'show']);

/*
|--------------------------------------------------------------------------
| Authenticated routes (Sanctum bearer token)
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // ---- Roles & Permissions builder ----
    Route::get('/permissions/catalog', [RoleController::class, 'catalog'])
        ->middleware('permission:roles.view');
    Route::apiResource('roles', RoleController::class)
        ->middleware('permission:roles.view');

    // ---- Users management ----
    Route::get('/users/assignable-roles', [UserController::class, 'assignableRoles'])
        ->middleware('permission:users.view');
    Route::get('/users', [UserController::class, 'index'])
        ->middleware('permission:users.view');
    Route::post('/users', [UserController::class, 'store'])
        ->middleware('permission:users.create');
    Route::put('/users/{user}', [UserController::class, 'update'])
        ->middleware('permission:users.update');
    Route::post('/users/{user}/roles', [UserController::class, 'setRoles'])
        ->middleware('permission:users.update');
    Route::post('/users/{user}/toggle-status', [UserController::class, 'toggleStatus'])
        ->middleware('permission:users.update');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])
        ->middleware('permission:users.delete');
    Route::post('/users/{user}/impersonate', [UserController::class, 'impersonate'])
        ->middleware('permission:users.impersonate');

    // ---- Site settings ----
    Route::get('/settings', [SettingsController::class, 'index'])
        ->middleware('permission:settings.view');
    Route::put('/settings', [SettingsController::class, 'update'])
        ->middleware('permission:settings.manage');
    Route::post('/settings/logo', [SettingsController::class, 'uploadLogo'])
        ->middleware('permission:settings.manage');
    Route::delete('/settings/logo', [SettingsController::class, 'removeLogo'])
        ->middleware('permission:settings.manage');

    // ---- Editable marketing content (public pages) ----
    Route::put('/content', [\App\Http\Controllers\Api\SiteContentController::class, 'update'])
        ->middleware('permission:settings.manage');
    Route::put('/content/text', [\App\Http\Controllers\Api\SiteContentController::class, 'saveText'])
        ->middleware('permission:settings.manage');

    // ---- Platform: reselling plans (super admin) ----
    Route::get('/plans', [PlanController::class, 'index'])
        ->middleware('permission:tenants.view');
    Route::post('/plans', [PlanController::class, 'store'])
        ->middleware('permission:plans.manage');
    Route::put('/plans/{plan}', [PlanController::class, 'update'])
        ->middleware('permission:plans.manage');
    Route::delete('/plans/{plan}', [PlanController::class, 'destroy'])
        ->middleware('permission:plans.manage');

    // ---- Platform: tenant (customer) management ----
    Route::get('/tenants', [TenantController::class, 'index'])
        ->middleware('permission:tenants.view');
    Route::get('/tenants/{tenant}', [TenantController::class, 'show'])
        ->middleware('permission:tenants.view');
    Route::post('/tenants', [TenantController::class, 'store'])
        ->middleware('permission:tenants.manage');
    Route::put('/tenants/{tenant}', [TenantController::class, 'update'])
        ->middleware('permission:tenants.manage');
    Route::post('/tenants/{tenant}/modules/toggle', [TenantController::class, 'toggleModule'])
        ->middleware('permission:tenants.manage');
    Route::post('/tenants/{tenant}/status', [TenantController::class, 'setStatus'])
        ->middleware('permission:tenants.manage');
    Route::delete('/tenants/{tenant}', [TenantController::class, 'destroy'])
        ->middleware('permission:tenants.manage');

    // ---- Addon / module system ----
    Route::get('/modules', [ModuleController::class, 'index'])
        ->middleware('permission:modules.view');
    Route::post('/modules/{id}/toggle', [ModuleController::class, 'toggle'])
        ->middleware('permission:modules.manage');

    // ---- Payment gateways ----
    Route::get('/gateways', [GatewayController::class, 'index'])
        ->middleware('permission:gateways.view');
    Route::post('/gateways/{id}/toggle', [GatewayController::class, 'toggle'])
        ->middleware('permission:gateways.manage');
    Route::put('/gateways/{id}', [GatewayController::class, 'update'])
        ->middleware('permission:gateways.manage');

    // ---- Checkout & orders (paid courses) ----
    Route::get('/checkout/gateways', [CheckoutController::class, 'gateways']);
    Route::post('/courses/{course}/checkout', [CheckoutController::class, 'store']);
    Route::post('/checkout/{reference}/confirm', [CheckoutController::class, 'confirm']);
    Route::get('/my/orders', [CheckoutController::class, 'myOrders']);
    Route::post('/orders/{order}/approve', [CheckoutController::class, 'approve'])
        ->middleware('permission:payments.refund');

    // ---- Learning (students) ----
    Route::get('/my/enrollments', [EnrollmentController::class, 'index']);
    Route::post('/courses/{course}/enroll', [EnrollmentController::class, 'store']);
    Route::get('/courses/{course}/progress', [ProgressController::class, 'index']);
    Route::post('/courses/{course}/lessons/{lesson}/progress', [ProgressController::class, 'toggle']);
    // Answer a lesson's quiz question to complete it and unlock the next.
    Route::post('/courses/{course}/lessons/{lesson}/answer', [ProgressController::class, 'answer']);

    // ---- Teaching (instructors) ----
    Route::get('/my/courses', [CourseController::class, 'mine'])
        ->middleware('permission:courses.view');
    Route::get('/courses/{course}/manage', [CourseController::class, 'manage'])
        ->middleware('permission:courses.view');
    Route::post('/courses', [CourseController::class, 'store'])
        ->middleware('permission:courses.create');
    Route::put('/courses/{course}', [CourseController::class, 'update'])
        ->middleware('permission:courses.update');
    Route::post('/courses/{course}/publish', [CourseController::class, 'publish'])
        ->middleware('permission:courses.update');
    Route::delete('/courses/{course}', [CourseController::class, 'destroy'])
        ->middleware('permission:courses.delete');

    // Lessons (curriculum builder) — ownership enforced in controller via policy.
    Route::post('/courses/{course}/lessons', [LessonController::class, 'store'])
        ->middleware('permission:lessons.create');
    Route::put('/courses/{course}/lessons/{lesson}', [LessonController::class, 'update'])
        ->middleware('permission:lessons.update');
    Route::delete('/courses/{course}/lessons/{lesson}', [LessonController::class, 'destroy'])
        ->middleware('permission:lessons.delete');
    Route::post('/courses/{course}/lessons/reorder', [LessonController::class, 'reorder'])
        ->middleware('permission:lessons.update');
    Route::post('/courses/{course}/lessons/{lesson}/video', [LessonController::class, 'uploadVideo'])
        ->middleware('permission:lessons.update');

    // ---- Live Classes (module: live_classes) ----
    Route::middleware('module:live_classes')->group(function () {
        Route::get('/live', [LiveSessionController::class, 'index'])
            ->middleware('permission:live.view');
        Route::get('/my/live', [LiveSessionController::class, 'mine'])
            ->middleware('permission:live.host');
        Route::post('/live', [LiveSessionController::class, 'store'])
            ->middleware('permission:live.host');
        Route::put('/live/{live}', [LiveSessionController::class, 'update'])
            ->middleware('permission:live.host');
        Route::post('/live/{live}/status', [LiveSessionController::class, 'setStatus'])
            ->middleware('permission:live.host');
        Route::delete('/live/{live}', [LiveSessionController::class, 'destroy'])
            ->middleware('permission:live.host');
    });

    // ---- Quizzes (module: quizzes) ----
    Route::middleware('module:quizzes')->group(function () {
        // Students
        Route::get('/quizzes', [QuizController::class, 'available'])
            ->middleware('permission:quizzes.view');
        Route::get('/quizzes/{quiz}/take', [QuizController::class, 'take'])
            ->middleware('permission:quizzes.view');
        Route::post('/quizzes/{quiz}/submit', [QuizController::class, 'submit'])
            ->middleware('permission:quizzes.view');
        // Teachers (builder)
        Route::get('/my/quizzes', [QuizController::class, 'mine'])
            ->middleware('permission:quizzes.create');
        Route::get('/quizzes/{quiz}/manage', [QuizController::class, 'manage'])
            ->middleware('permission:quizzes.create');
        Route::post('/quizzes', [QuizController::class, 'store'])
            ->middleware('permission:quizzes.create');
        Route::put('/quizzes/{quiz}', [QuizController::class, 'update'])
            ->middleware('permission:quizzes.create');
        Route::delete('/quizzes/{quiz}', [QuizController::class, 'destroy'])
            ->middleware('permission:quizzes.create');
        Route::post('/quizzes/{quiz}/questions', [QuizController::class, 'addQuestion'])
            ->middleware('permission:quizzes.create');
        Route::put('/quizzes/{quiz}/questions/{question}', [QuizController::class, 'updateQuestion'])
            ->middleware('permission:quizzes.create');
        Route::delete('/quizzes/{quiz}/questions/{question}', [QuizController::class, 'deleteQuestion'])
            ->middleware('permission:quizzes.create');
    });
});
