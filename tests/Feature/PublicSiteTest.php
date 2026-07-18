<?php

namespace Tests\Feature;

use App\Models\Course;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class PublicSiteTest extends TestCase
{
    public static function publicPages(): array
    {
        return [
            'home' => ['/'],
            'about' => ['/about'],
            'pricing' => ['/pricing'],
            'instructors' => ['/instructors'],
            'contact' => ['/contact'],
            'courses' => ['/courses'],
        ];
    }

    #[DataProvider('publicPages')]
    public function test_public_page_renders(string $path): void
    {
        $this->get($path)->assertOk();
    }

    public function test_course_detail_page_renders_for_published_course(): void
    {
        $teacher = $this->userWithRole('teacher');
        $course = Course::create([
            'teacher_id' => $teacher->id,
            'title' => 'Published Course',
            'level' => 'beginner',
            'status' => 'published',
            'published_at' => now(),
        ]);

        $this->get('/courses/'.$course->slug)->assertOk()->assertSee('Published Course');
    }

    public function test_unknown_course_slug_returns_404(): void
    {
        $this->get('/courses/does-not-exist')->assertNotFound();
    }
}
