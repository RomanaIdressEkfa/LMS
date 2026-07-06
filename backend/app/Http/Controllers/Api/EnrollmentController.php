<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Enrollment;
use Illuminate\Http\Request;

class EnrollmentController extends Controller
{
    /** The authenticated user's enrolled courses ("My Learning"). */
    public function index(Request $request)
    {
        $enrollments = Enrollment::where('user_id', $request->user()->id)
            ->with(['course' => fn ($q) => $q->with('teacher:id,name')->withCount('lessons')])
            ->latest()
            ->get();

        return response()->json(['enrollments' => $enrollments]);
    }

    /**
     * Enroll in a course. Free courses enroll instantly; paid courses will be
     * routed through checkout in Phase 3 (rejected here for now).
     */
    public function store(Request $request, Course $course)
    {
        $user = $request->user();

        if ($course->status !== 'published') {
            return response()->json(['message' => 'This course is not available.'], 422);
        }

        if ($course->isEnrolled($user)) {
            return response()->json(['message' => 'Already enrolled.'], 409);
        }

        if (! $course->is_free) {
            return response()->json([
                'message' => 'This is a paid course — please complete checkout.',
                'requires_payment' => true,
                'price' => $course->price,
            ], 402);
        }

        $enrollment = Enrollment::create([
            'user_id' => $user->id,
            'course_id' => $course->id,
            'amount_paid' => 0,
            'source' => 'free',
        ]);

        return response()->json(['enrollment' => $enrollment, 'message' => 'Enrolled!'], 201);
    }
}
