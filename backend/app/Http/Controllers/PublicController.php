<?php

namespace App\Http\Controllers;

use App\Http\Controllers\SiteContentController;
use App\Models\Course;
use App\Models\Plan;
use App\Models\User;

/**
 * Server-rendered public marketing site (Blade + Tailwind + Alpine), replacing
 * the old Next.js frontend. Editable content comes from the same site_content
 * setting and is injected straight into the view — no API round-trip, no flash.
 */
class PublicController extends Controller
{
    /** Wrap view() so every public page gets the editable $site content + $text. */
    protected function page(string $view, array $data = [])
    {
        return view($view, array_merge([
            'site' => SiteContentController::effectiveContent(),
            'text' => SiteContentController::textOverrides(),
        ], $data));
    }

    public function home()
    {
        $featured = Course::published()
            ->with('teacher:id,name')
            ->latest()
            ->take(6)
            ->get();

        return $this->page('public.home', compact('featured'));
    }

    public function about()
    {
        return $this->page('public.about');
    }

    public function pricing()
    {
        $plans = Plan::where('is_active', true)->orderBy('sort_order')->get();

        return $this->page('public.pricing', compact('plans'));
    }

    public function instructors()
    {
        $instructors = User::role('teacher')
            ->withCount(['teacherCourses as courses_count' => fn ($q) => $q->published()])
            ->get();

        return $this->page('public.instructors', compact('instructors'));
    }

    public function contact()
    {
        return $this->page('public.contact');
    }

    public function courses()
    {
        $courses = Course::published()
            ->with('teacher:id,name', 'category:id,name,slug,color')
            ->withCount('lessons')
            ->latest()
            ->get();

        $categories = \App\Models\Category::orderBy('name')->get(['id', 'name', 'slug']);

        return $this->page('public.courses', compact('courses', 'categories'));
    }

    public function courseShow(string $slug)
    {
        $course = Course::where('slug', $slug)
            ->with('teacher:id,name,bio,avatar', 'category:id,name,color', 'lessons')
            ->withCount(['lessons', 'enrollments'])
            ->firstOrFail();

        return $this->page('public.course-show', compact('course'));
    }
}
