<?php

namespace App\Http\Controllers;

use App\Models\Enrollment;
use App\Models\LiveSession;
use Illuminate\Http\Request;

/**
 * Live classes (Blade), read-only list for now: public sessions, plus any tied
 * to a course the user is enrolled in or teaches. The meeting link is exposed
 * only while a session is live (or to its host). Scheduling UI = later.
 */
class LiveController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $enrolledCourseIds = Enrollment::where('user_id', $user->id)->pluck('course_id');

        $sessions = LiveSession::with('teacher:id,name', 'course:id,title,slug')
            ->where('status', '!=', 'ended')
            ->where(function ($q) use ($enrolledCourseIds, $user) {
                $q->whereNull('course_id')
                    ->orWhereIn('course_id', $enrolledCourseIds)
                    ->orWhere('teacher_id', $user->id);
            })
            ->orderBy('scheduled_at')
            ->get();

        return view('dashboard.live.index', compact('sessions'));
    }
}
