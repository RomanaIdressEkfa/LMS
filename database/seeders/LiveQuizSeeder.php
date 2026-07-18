<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Course;
use App\Models\LiveSession;
use App\Models\Quiz;
use App\Models\User;

class LiveQuizSeeder extends Seeder
{
    public function run(): void
    {
        $teacher = User::where('email', 'teacher@novalms.test')->first();
        if (! $teacher) {
            return;
        }

        $course = Course::where('slug', 'uiux-design-fundamentals')->first();

        // A demo live class (public + one course-linked).
        LiveSession::updateOrCreate(
            ['title' => 'Live Q&A: Getting Started in Design'],
            [
                'teacher_id' => $teacher->id,
                'course_id' => null,
                'description' => 'Open session — bring your questions about breaking into design.',
                'provider' => 'custom',
                'meeting_url' => 'https://meet.example.com/nova-design-qa',
                'scheduled_at' => now()->addDays(2)->setTime(18, 0),
                'duration_minutes' => 60,
                'status' => 'scheduled',
            ]
        );

        LiveSession::updateOrCreate(
            ['title' => 'Design Workshop (Live Now)'],
            [
                'teacher_id' => $teacher->id,
                'course_id' => $course?->id,
                'description' => 'Hands-on workshop for enrolled students.',
                'provider' => 'custom',
                'meeting_url' => 'https://meet.example.com/nova-workshop',
                'scheduled_at' => now()->subMinutes(10),
                'duration_minutes' => 90,
                'status' => 'live',
            ]
        );

        // A demo quiz with 3 questions.
        $quiz = Quiz::updateOrCreate(
            ['title' => 'Design Fundamentals Quiz'],
            [
                'teacher_id' => $teacher->id,
                'course_id' => $course?->id,
                'description' => 'Test your grasp of the basics.',
                'pass_mark' => 60,
                'time_limit_minutes' => 0,
                'published' => true,
            ]
        );

        if ($quiz->questions()->count() === 0) {
            $questions = [
                ['question' => 'Which principle is about visual hierarchy?', 'options' => ['Contrast', 'Latency', 'Caching', 'Sharding'], 'correct_index' => 0, 'points' => 1],
                ['question' => 'What does "whitespace" improve?', 'options' => ['File size', 'Readability & focus', 'Server load', 'SEO only'], 'correct_index' => 1, 'points' => 1],
                ['question' => 'A good primary CTA button should be…', 'options' => ['Hidden', 'The same as everything else', 'Visually prominent', 'Tiny'], 'correct_index' => 2, 'points' => 1],
            ];
            foreach ($questions as $i => $q) {
                $quiz->questions()->create(array_merge($q, ['sort_order' => $i]));
            }
        }
    }
}
