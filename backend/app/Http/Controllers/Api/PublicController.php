<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\User;

/**
 * Public marketing endpoints — no auth. Feed the public website
 * (instructors directory, pricing page).
 */
class PublicController extends Controller
{
    /** Teachers with their published-course counts, for the instructors page. */
    public function instructors()
    {
        $instructors = User::role('teacher')
            ->withCount(['teacherCourses as courses_count' => fn ($q) => $q->where('status', 'published')])
            ->orderByDesc('courses_count')
            ->limit(24)
            ->get(['id', 'name', 'avatar', 'bio'])
            ->map(fn (User $u) => [
                'id' => $u->id,
                'name' => $u->name,
                'avatar' => $u->avatar,
                'bio' => $u->bio,
                'courses_count' => $u->courses_count,
            ]);

        return response()->json(['instructors' => $instructors]);
    }

    /** Active reselling plans, for the public pricing page. */
    public function pricing()
    {
        $plans = Plan::where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('price')
            ->get(['id', 'name', 'description', 'price', 'interval', 'module_keys']);

        return response()->json(['plans' => $plans]);
    }
}
