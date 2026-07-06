<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\LessonProgress;
use Illuminate\Http\Request;

class ProgressController extends Controller
{
    /**
     * Mark a lesson complete/incomplete for the current user and recompute the
     * course-level progress percentage on their enrollment.
     */
    public function toggle(Request $request, Course $course, Lesson $lesson)
    {
        $user = $request->user();
        abort_unless($lesson->course_id === $course->id, 404);

        $enrollment = $course->enrollments()->where('user_id', $user->id)->first();
        abort_unless($enrollment, 403, 'You are not enrolled in this course.');

        $completed = $request->boolean('completed', true);

        if ($completed) {
            LessonProgress::updateOrCreate(
                ['user_id' => $user->id, 'lesson_id' => $lesson->id],
                ['course_id' => $course->id, 'completed' => true],
            );
        } else {
            LessonProgress::where('user_id', $user->id)
                ->where('lesson_id', $lesson->id)->delete();
        }

        // Recompute percentage.
        $total = $course->lessons()->count();
        $done = LessonProgress::where('user_id', $user->id)
            ->where('course_id', $course->id)->count();
        $pct = $total > 0 ? (int) round(($done / $total) * 100) : 0;

        $enrollment->update([
            'progress' => $pct,
            'completed_at' => $pct >= 100 ? now() : null,
        ]);

        return response()->json(['progress' => $pct, 'completed_lessons' => $done]);
    }

    /** Ids of lessons the user has completed in a course. */
    public function index(Request $request, Course $course)
    {
        $ids = LessonProgress::where('user_id', $request->user()->id)
            ->where('course_id', $course->id)
            ->pluck('lesson_id');

        return response()->json(['completed_lesson_ids' => $ids]);
    }
}
