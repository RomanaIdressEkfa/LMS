<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Http\Request;

/**
 * Completion certificate for a student who has finished a course. The data is
 * derived from the enrollment — a course is "finished" when its progress
 * reaches 100%. The certificate serial is deterministic (same enrollment
 * always yields the same serial), so it can be re-fetched and verified.
 */
class CertificateController extends Controller
{
    public function show(Request $request, string $slug)
    {
        $course = Course::where('slug', $slug)
            ->with('teacher:id,name')
            ->firstOrFail();

        $user = $request->user();
        $enrollment = $course->enrollments()->where('user_id', $user->id)->first();

        abort_unless($enrollment, 403, 'You are not enrolled in this course.');
        abort_unless(
            (int) $enrollment->progress >= 100,
            403,
            'Finish all lessons to earn your certificate.'
        );

        $completedAt = $enrollment->completed_at ?? $enrollment->updated_at;
        $serial = sprintf('NOVA-%s-%05d', $completedAt->format('Y'), $enrollment->id);

        return response()->json([
            'certificate' => [
                'serial' => $serial,
                'student_name' => $user->name,
                'course_title' => $course->title,
                'instructor_name' => $course->teacher?->name,
                'completed_at' => $completedAt->toIso8601String(),
                'completed_date' => $completedAt->format('F j, Y'),
                'lessons_count' => $course->lessons()->count(),
            ],
        ]);
    }
}
