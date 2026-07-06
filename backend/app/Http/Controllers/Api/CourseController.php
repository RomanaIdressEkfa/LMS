<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CourseController extends Controller
{
    /**
     * Public catalog — published courses, filterable by category / search / price.
     */
    public function index(Request $request)
    {
        $courses = Course::query()
            ->published()
            ->with(['teacher:id,name,avatar', 'category:id,name,slug,color'])
            ->withCount('lessons')
            ->when($request->category, fn ($q, $slug) =>
                $q->whereHas('category', fn ($c) => $c->where('slug', $slug)))
            ->when($request->search, fn ($q, $s) =>
                $q->where('title', 'like', "%{$s}%"))
            ->when($request->price === 'free', fn ($q) => $q->where('is_free', true))
            ->when($request->price === 'paid', fn ($q) => $q->where('is_free', false))
            ->latest('published_at')
            ->paginate(12);

        return response()->json($courses);
    }

    /**
     * Courses owned by the authenticated teacher (any status).
     */
    public function mine(Request $request)
    {
        $courses = Course::where('teacher_id', $request->user()->id)
            ->with('category:id,name,color')
            ->withCount(['lessons', 'enrollments'])
            ->latest()
            ->get();

        return response()->json(['courses' => $courses]);
    }

    /**
     * Single course by slug, with curriculum. Lesson bodies are hidden unless
     * the viewer is enrolled, owns the course, or the lesson is a free preview.
     */
    public function show(Request $request, string $slug)
    {
        $course = Course::where('slug', $slug)
            ->with(['teacher:id,name,avatar,bio', 'category:id,name,slug,color', 'lessons'])
            ->withCount(['lessons', 'enrollments'])
            ->firstOrFail();

        $user = $request->user('sanctum');
        $enrolled = $course->isEnrolled($user);
        $owner = $user && $course->teacher_id === $user->id;
        $canViewAll = $enrolled || $owner;

        $lessons = $course->lessons->map(fn ($l) => [
            'id' => $l->id,
            'title' => $l->title,
            'type' => $l->type,
            'duration_minutes' => $l->duration_minutes,
            'is_preview' => $l->is_preview,
            'locked' => ! ($canViewAll || $l->is_preview),
            'video_url' => ($canViewAll || $l->is_preview) ? $l->video_url : null,
            'content' => ($canViewAll || $l->is_preview) ? $l->content : null,
        ]);

        return response()->json([
            'course' => array_merge($course->toArray(), [
                'lessons' => $lessons,
                'is_enrolled' => $enrolled,
                'is_owner' => $owner,
            ]),
        ]);
    }

    /**
     * Full course + all lessons for the owner's editor (works for drafts).
     */
    public function manage(Request $request, Course $course)
    {
        $this->authorize('update', $course);

        $course->load(['lessons', 'category:id,name,color']);

        return response()->json(['course' => $course]);
    }

    public function store(Request $request)
    {
        $data = $this->validateCourse($request);
        $data['teacher_id'] = $request->user()->id;

        $course = Course::create($data);

        return response()->json(['course' => $course], 201);
    }

    public function update(Request $request, Course $course)
    {
        $this->authorize('update', $course);
        $course->update($this->validateCourse($request, $course));

        return response()->json(['course' => $course->fresh()]);
    }

    public function publish(Request $request, Course $course)
    {
        $this->authorize('update', $course);

        $publish = $request->boolean('publish', true);
        $course->update([
            'status' => $publish ? 'published' : 'draft',
            'published_at' => $publish ? ($course->published_at ?? now()) : null,
        ]);

        return response()->json(['course' => $course->fresh()]);
    }

    public function destroy(Course $course)
    {
        $this->authorize('delete', $course);
        $course->delete();

        return response()->json(['message' => 'Course deleted.']);
    }

    private function validateCourse(Request $request, ?Course $course = null): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:200'],
            'subtitle' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'category_id' => ['nullable', Rule::exists('categories', 'id')],
            'level' => ['required', Rule::in(['beginner', 'intermediate', 'advanced'])],
            'is_free' => ['boolean'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'thumbnail' => ['nullable', 'string'],
        ]);

        // A free course always has price 0.
        if (! empty($data['is_free'])) {
            $data['price'] = 0;
        }

        return $data;
    }
}
