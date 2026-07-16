<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

/**
 * Placeholder dashboard landing (Phase 2). The full Blade dashboard — learn,
 * teaching, quizzes, admin, etc. — is built in Phase 3.
 */
class DashboardController extends Controller
{
    public function home(Request $request)
    {
        return view('dashboard.home', ['user' => $request->user()]);
    }
}
