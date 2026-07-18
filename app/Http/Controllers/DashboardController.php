<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function home(Request $request)
    {
        $user = $request->user();

        $enrolled = $user->enrollments();

        return view('dashboard.home', [
            'user' => $user,
            'enrolledCount' => (clone $enrolled)->count(),
            'completedCount' => (clone $enrolled)->where('progress', '>=', 100)->count(),
            'teachingCount' => $user->teacherCourses()->count(),
            'permissions' => $user->getAllPermissions()->pluck('name')->sort()->values(),
        ]);
    }
}
