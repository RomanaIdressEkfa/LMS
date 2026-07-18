<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Course;
use App\Models\Enrollment;
use Illuminate\Http\Request;

/**
 * Student-facing course catalog inside the dashboard: browse published courses,
 * enroll (free = instant), and view purchases.
 */
class CatalogController extends Controller
{
    public function index(Request $request)
    {
        $courses = Course::published()
            ->with('teacher:id,name', 'category:id,name,slug,color')
            ->withCount('lessons')
            ->latest()
            ->get();

        return view('dashboard.catalog.index', [
            'courses' => $courses,
            'categories' => Category::orderBy('name')->get(['id', 'name', 'slug']),
            'enrolledIds' => $request->user()->enrollments()->pluck('course_id')->all(),
            'canCreate' => $request->user()->can('courses.create'),
        ]);
    }

    public function enroll(Request $request, Course $course)
    {
        $user = $request->user();

        if ($course->status !== 'published') {
            return back()->with('err', 'This course is not available.');
        }
        if ($course->isEnrolled($user)) {
            return redirect("/dashboard/learn/{$course->slug}");
        }
        if (! $course->is_free) {
            return redirect("/courses/{$course->slug}")->with('err', 'This is a paid course — checkout is coming soon.');
        }

        Enrollment::create([
            'user_id' => $user->id,
            'course_id' => $course->id,
            'amount_paid' => 0,
            'source' => 'free',
        ]);

        return redirect("/dashboard/learn/{$course->slug}")->with('ok', 'Enrolled! Start learning.');
    }

    public function purchases(Request $request)
    {
        $enrollments = $request->user()->enrollments()
            ->with('course:id,title,slug')
            ->latest()
            ->get();

        return view('dashboard.purchases', compact('enrollments'));
    }
}
