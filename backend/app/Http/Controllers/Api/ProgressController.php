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

    /**
     * Complete a lesson by answering its quiz question. If the lesson has a
     * question, the answer must be correct; otherwise it just completes.
     * On success the next lesson unlocks. Enforces sequential order: you can
     * only complete a lesson that is currently unlocked.
     */
    public function answer(Request $request, Course $course, Lesson $lesson)
    {
        $user = $request->user();
        abort_unless($lesson->course_id === $course->id, 404);

        $enrollment = $course->enrollments()->where('user_id', $user->id)->first();
        abort_unless($enrollment, 403, 'You are not enrolled in this course.');

        // Guard: the lesson must be unlocked (previous lesson completed).
        $ordered = $course->lessons()->pluck('id')->all();
        $completedIds = LessonProgress::where('user_id', $user->id)
            ->where('course_id', $course->id)->pluck('lesson_id')->all();
        $index = array_search($lesson->id, $ordered, true);
        $prevId = $index > 0 ? $ordered[$index - 1] : null;
        if ($prevId !== null && ! in_array($prevId, $completedIds, true)) {
            return response()->json(['message' => 'Finish the previous lesson first.'], 423);
        }

        // Check the answer if the lesson has a question.
        if (! empty($lesson->question)) {
            $data = $request->validate(['answer_index' => ['required', 'integer']]);
            if ((int) $data['answer_index'] !== (int) $lesson->question_correct_index) {
                return response()->json(['correct' => false, 'message' => 'Wrong answer — try again.'], 200);
            }
        }

        LessonProgress::updateOrCreate(
            ['user_id' => $user->id, 'lesson_id' => $lesson->id],
            ['course_id' => $course->id, 'completed' => true],
        );

        $total = count($ordered);
        $done = count(array_unique(array_merge($completedIds, [$lesson->id])));
        $pct = $total > 0 ? (int) round(($done / $total) * 100) : 0;
        $enrollment->update(['progress' => $pct, 'completed_at' => $pct >= 100 ? now() : null]);

        $nextId = $index !== false && $index + 1 < $total ? $ordered[$index + 1] : null;

        return response()->json([
            'correct' => true,
            'completed' => true,
            'progress' => $pct,
            'next_lesson_id' => $nextId,
        ]);
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
