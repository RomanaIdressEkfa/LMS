<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Course;
use App\Models\Lesson;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

/**
 * Instructor course management (Blade): course list, create, and the curriculum
 * editor. Server-rendered; video upload uses an Alpine XHR to uploadVideo() for
 * a real progress bar, and the MCQ "Generate" button is client-side (no AI).
 */
class TeachingController extends Controller
{
    public function index(Request $request)
    {
        $courses = $request->user()->teacherCourses()
            ->withCount('lessons', 'enrollments')
            ->latest()
            ->get();
        $categories = Category::orderBy('name')->get();

        return view('dashboard.teaching.index', compact('courses', 'categories'));
    }

    public function store(Request $request)
    {
        $data = $this->validateCourse($request);
        $data['teacher_id'] = $request->user()->id;
        $course = Course::create($data);

        return redirect("/dashboard/teaching/{$course->id}")->with('ok', 'Course created.');
    }

    public function edit(Course $course)
    {
        $this->authorize('update', $course);
        $course->load('lessons');
        $course->lessons->each->append('video_file_url');

        return view('dashboard.teaching.edit', [
            'course' => $course,
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    public function updateCourse(Request $request, Course $course)
    {
        $this->authorize('update', $course);
        $course->update($this->validateCourse($request, $course));

        return back()->with('ok', 'Course saved.');
    }

    public function publish(Request $request, Course $course)
    {
        $this->authorize('update', $course);
        $publish = ! ($course->status === 'published');
        $course->update([
            'status' => $publish ? 'published' : 'draft',
            'published_at' => $publish ? ($course->published_at ?? now()) : null,
        ]);

        return back()->with('ok', $publish ? 'Course published.' : 'Course unpublished.');
    }

    public function storeLesson(Request $request, Course $course)
    {
        $this->authorize('manageLessons', $course);
        $data = $this->validateLesson($request);
        $data['sort_order'] = (int) $course->lessons()->max('sort_order') + 1;
        $lesson = $course->lessons()->create($data);

        return redirect("/dashboard/teaching/{$course->id}?lesson={$lesson->id}")->with('ok', 'Lesson added — upload its video below.');
    }

    public function updateLesson(Request $request, Course $course, Lesson $lesson)
    {
        $this->authorize('manageLessons', $course);
        abort_unless($lesson->course_id === $course->id, 404);
        $lesson->update($this->validateLesson($request));

        return redirect("/dashboard/teaching/{$course->id}")->with('ok', 'Lesson updated.');
    }

    public function destroyLesson(Course $course, Lesson $lesson)
    {
        $this->authorize('manageLessons', $course);
        abort_unless($lesson->course_id === $course->id, 404);
        $lesson->delete();

        return back()->with('ok', 'Lesson deleted.');
    }

    /** Video upload — returns JSON for the Alpine XHR progress bar. */
    public function uploadVideo(Request $request, Course $course, Lesson $lesson)
    {
        $this->authorize('manageLessons', $course);
        abort_unless($lesson->course_id === $course->id, 404);

        $request->validate([
            'video' => ['required', 'file', 'mimetypes:video/mp4,video/webm,video/quicktime,video/x-msvideo', 'max:204800'],
        ]);

        if ($lesson->video_file) {
            Storage::disk('public')->delete($lesson->video_file);
        }
        $path = $request->file('video')->store('lesson-videos', 'public');
        $lesson->update(['video_file' => $path]);

        return response()->json(['video_file_url' => $lesson->fresh()->video_file_url]);
    }

    private function validateCourse(Request $request, ?Course $course = null): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:200'],
            'subtitle' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'level' => ['required', Rule::in(['beginner', 'intermediate', 'advanced'])],
            'is_free' => ['boolean'],
            'price' => ['nullable', 'numeric', 'min:0'],
        ]);
        $data['is_free'] = $request->boolean('is_free');
        $data['price'] = $data['is_free'] ? 0 : (float) ($data['price'] ?? 0);

        return $data;
    }

    private function validateLesson(Request $request): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:200'],
            'type' => ['required', Rule::in(['video', 'text', 'pdf'])],
            'content' => ['nullable', 'string'],
            'video_url' => ['nullable', 'string', 'max:500'],
            'duration_minutes' => ['nullable', 'integer', 'min:0'],
            'is_preview' => ['boolean'],
            'question' => ['nullable', 'string'],
            'question_options' => ['nullable', 'array', 'min:2'],
            'question_options.*' => ['string'],
            'question_correct_index' => ['nullable', 'integer', 'min:0'],
        ]);
        $data['is_preview'] = $request->boolean('is_preview');

        // Only keep a question if it has text + at least two non-empty options.
        $opts = array_values(array_filter($data['question_options'] ?? [], fn ($o) => trim($o) !== ''));
        if (! empty($data['question']) && count($opts) >= 2) {
            $data['question_options'] = $opts;
            $data['question_correct_index'] = min((int) ($data['question_correct_index'] ?? 0), count($opts) - 1);
        } else {
            $data['question'] = null;
            $data['question_options'] = null;
            $data['question_correct_index'] = null;
        }

        return $data;
    }
}
