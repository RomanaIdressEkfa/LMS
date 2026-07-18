<?php

namespace Tests\Unit;

use App\Models\Course;
use App\Models\User;
use Tests\TestCase;

class CourseModelTest extends TestCase
{
    public function test_slug_is_auto_generated_from_title(): void
    {
        $teacher = $this->userWithRole('teacher');

        $course = Course::create([
            'teacher_id' => $teacher->id,
            'title' => 'UI/UX Design Fundamentals',
            'level' => 'beginner',
        ]);

        $this->assertSame('uiux-design-fundamentals', $course->slug);
    }

    public function test_duplicate_titles_get_unique_slugs(): void
    {
        $teacher = $this->userWithRole('teacher');

        $a = Course::create(['teacher_id' => $teacher->id, 'title' => 'Laravel Basics', 'level' => 'beginner']);
        $b = Course::create(['teacher_id' => $teacher->id, 'title' => 'Laravel Basics', 'level' => 'beginner']);

        $this->assertSame('laravel-basics', $a->slug);
        $this->assertSame('laravel-basics-1', $b->slug);
    }

    public function test_is_enrolled_reflects_enrollment(): void
    {
        $teacher = $this->userWithRole('teacher');
        $student = $this->userWithRole('student');
        $course = Course::create(['teacher_id' => $teacher->id, 'title' => 'Go Deep', 'level' => 'beginner']);

        $this->assertFalse($course->isEnrolled($student));

        $course->enrollments()->create(['user_id' => $student->id]);

        $this->assertTrue($course->fresh()->isEnrolled($student));
    }
}
