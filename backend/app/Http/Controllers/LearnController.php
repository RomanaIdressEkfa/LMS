<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Lesson;
use App\Models\LessonProgress;
use Illuminate\Http\Request;

/**
 * Student learning experience (Blade): the enrolled-course list and the gated
 * lesson viewer (video → answer MCQ → next lesson unlocks). Server-rendered;
 * the MCQ submit is a small Alpine fetch to `answer()`.
 */
class LearnController extends Controller
{
    public function index(Request $request)
    {
        $enrollments = $request->user()->enrollments()
            ->with('course.teacher:id,name')
            ->latest()
            ->get();

        return view('dashboard.learn.index', compact('enrollments'));
    }

    public function show(Request $request, string $slug)
    {
        $user = $request->user();
        $course = Course::where('slug', $slug)
            ->with('teacher:id,name', 'lessons')
            ->firstOrFail();

        $enrollment = $course->enrollments()->where('user_id', $user->id)->first();

        if (! $enrollment) {
            return view('dashboard.learn.locked', compact('course'));
        }

        $completedIds = LessonProgress::where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->pluck('lesson_id')->all();

        // Sequential unlock: a lesson unlocks once the previous is completed.
        $prevCompleted = true;
        $lessons = $course->lessons->map(function (Lesson $l) use ($completedIds, &$prevCompleted) {
            $completed = in_array($l->id, $completedIds, true);
            $unlocked = $prevCompleted;
            $row = (object) [
                'id' => $l->id,
                'title' => $l->title,
                'duration_minutes' => $l->duration_minutes,
                'completed' => $completed,
                'unlocked' => $unlocked,
                'has_question' => ! empty($l->question),
                'video_file_url' => $unlocked ? $l->video_file_url : null,
                'video_url' => $unlocked ? $l->video_url : null,
                'content' => $unlocked ? $l->content : null,
                'question' => $unlocked ? $l->question : null,
                'options' => $unlocked ? ($l->question_options ?? []) : [],
            ];
            $prevCompleted = $completed;

            return $row;
        });

        // Active lesson: ?lesson=ID (if unlocked) else first incomplete unlocked else first.
        $wanted = (int) $request->query('lesson');
        $active = $lessons->firstWhere(fn ($l) => $l->id === $wanted && $l->unlocked)
            ?? $lessons->firstWhere(fn ($l) => $l->unlocked && ! $l->completed)
            ?? $lessons->first();

        return view('dashboard.learn.show', [
            'course' => $course,
            'lessons' => $lessons,
            'active' => $active,
            'progress' => (int) $enrollment->progress,
        ]);
    }

    /**
     * Complete the active lesson by answering its MCQ (if any). Returns JSON for
     * the Alpine front-end: { correct, next_lesson_id, progress }.
     */
    public function answer(Request $request, Course $course, Lesson $lesson)
    {
        $user = $request->user();
        abort_unless($lesson->course_id === $course->id, 404);

        $enrollment = $course->enrollments()->where('user_id', $user->id)->first();
        abort_unless($enrollment, 403, 'You are not enrolled in this course.');

        $ordered = $course->lessons()->pluck('id')->all();
        $completedIds = LessonProgress::where('user_id', $user->id)
            ->where('course_id', $course->id)->pluck('lesson_id')->all();
        $index = array_search($lesson->id, $ordered, true);
        $prevId = $index > 0 ? $ordered[$index - 1] : null;

        if ($prevId !== null && ! in_array($prevId, $completedIds, true)) {
            return response()->json(['correct' => false, 'message' => 'Finish the previous lesson first.'], 423);
        }

        if (! empty($lesson->question)) {
            $data = $request->validate(['answer_index' => ['required', 'integer']]);
            if ((int) $data['answer_index'] !== (int) $lesson->question_correct_index) {
                return response()->json(['correct' => false, 'message' => 'Wrong answer — try again.']);
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
            'progress' => $pct,
            'next_lesson_id' => $nextId,
        ]);
    }
}
