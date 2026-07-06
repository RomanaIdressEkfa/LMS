<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Lesson;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LessonController extends Controller
{
    public function store(Request $request, Course $course)
    {
        $this->authorize('manageLessons', $course);

        $data = $this->validateLesson($request);
        $data['sort_order'] = (int) $course->lessons()->max('sort_order') + 1;

        $lesson = $course->lessons()->create($data);

        return response()->json(['lesson' => $lesson], 201);
    }

    public function update(Request $request, Course $course, Lesson $lesson)
    {
        $this->authorize('manageLessons', $course);
        abort_unless($lesson->course_id === $course->id, 404);

        $lesson->update($this->validateLesson($request));

        return response()->json(['lesson' => $lesson->fresh()]);
    }

    public function destroy(Course $course, Lesson $lesson)
    {
        $this->authorize('manageLessons', $course);
        abort_unless($lesson->course_id === $course->id, 404);

        $lesson->delete();

        return response()->json(['message' => 'Lesson deleted.']);
    }

    /** Persist a new lesson order (array of lesson ids in the desired order). */
    public function reorder(Request $request, Course $course)
    {
        $this->authorize('manageLessons', $course);

        $ids = $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['integer'],
        ])['ids'];

        foreach ($ids as $order => $id) {
            $course->lessons()->where('id', $id)->update(['sort_order' => $order]);
        }

        return response()->json(['message' => 'Reordered.']);
    }

    private function validateLesson(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:200'],
            'type' => ['required', Rule::in(['video', 'text', 'pdf'])],
            'content' => ['nullable', 'string'],
            'video_url' => ['nullable', 'string', 'max:500'],
            'attachment' => ['nullable', 'string', 'max:500'],
            'duration_minutes' => ['nullable', 'integer', 'min:0'],
            'is_preview' => ['boolean'],
        ]);
    }
}
