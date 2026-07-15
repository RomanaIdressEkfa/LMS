<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Lesson;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
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

    /**
     * Upload a video file for a lesson (mp4/webm/mov). Stored on the public
     * disk and served via /storage. Returns the new file URL.
     */
    public function uploadVideo(Request $request, Course $course, Lesson $lesson)
    {
        $this->authorize('manageLessons', $course);
        abort_unless($lesson->course_id === $course->id, 404);

        $request->validate([
            'video' => ['required', 'file', 'mimetypes:video/mp4,video/webm,video/quicktime,video/x-msvideo', 'max:204800'], // 200MB
        ]);

        // Replace any previous upload.
        if ($lesson->video_file) {
            Storage::disk('public')->delete($lesson->video_file);
        }

        $path = $request->file('video')->store('lesson-videos', 'public');
        $lesson->update(['video_file' => $path]);

        return response()->json([
            'lesson' => $lesson->fresh(),
            'video_file_url' => $lesson->video_file_url,
        ]);
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
            // Optional per-lesson quiz question that unlocks the next lesson.
            'question' => ['nullable', 'string'],
            'question_options' => ['nullable', 'array', 'min:2'],
            'question_options.*' => ['string'],
            'question_correct_index' => ['nullable', 'integer', 'min:0'],
        ]);
    }
}
