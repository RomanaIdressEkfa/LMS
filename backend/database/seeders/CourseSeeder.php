<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\User;

class CourseSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Web Development', 'slug' => 'web-development', 'icon' => 'code', 'color' => '#2563ff'],
            ['name' => 'Design', 'slug' => 'design', 'icon' => 'palette', 'color' => '#a855f7'],
            ['name' => 'Business', 'slug' => 'business', 'icon' => 'briefcase', 'color' => '#f59e0b'],
            ['name' => 'Data Science', 'slug' => 'data-science', 'icon' => 'bar-chart', 'color' => '#22c55e'],
            ['name' => 'Marketing', 'slug' => 'marketing', 'icon' => 'megaphone', 'color' => '#ef4444'],
        ];
        foreach ($categories as $i => $c) {
            Category::updateOrCreate(['slug' => $c['slug']], array_merge($c, ['sort_order' => $i]));
        }

        $teacher = User::where('email', 'teacher@novalms.test')->first();
        if (! $teacher) {
            return;
        }

        $webDev = Category::where('slug', 'web-development')->first();
        $design = Category::where('slug', 'design')->first();
        $data = Category::where('slug', 'data-science')->first();

        $courses = [
            [
                'title' => 'Modern React from Scratch',
                'subtitle' => 'Build production apps with React 19 & Next.js',
                'category_id' => $webDev->id,
                'level' => 'intermediate',
                'is_free' => false,
                'price' => 49.99,
                'lessons' => ['Welcome & Setup', 'Components & Props', 'Hooks Deep Dive', 'Data Fetching', 'Deploying to Production'],
            ],
            [
                'title' => 'Laravel API Masterclass',
                'subtitle' => 'REST APIs, auth, and testing the right way',
                'category_id' => $webDev->id,
                'level' => 'advanced',
                'is_free' => false,
                'price' => 59.00,
                'lessons' => ['Routing & Controllers', 'Eloquent Relationships', 'Sanctum Auth', 'Writing Tests'],
            ],
            [
                'title' => 'UI/UX Design Fundamentals',
                'subtitle' => 'Design interfaces people love to use',
                'category_id' => $design->id,
                'level' => 'beginner',
                'is_free' => true,
                'price' => 0,
                'lessons' => ['Design Thinking', 'Color & Typography', 'Layout & Spacing', 'Prototyping in Figma'],
            ],
            [
                'title' => 'Python for Data Analysis',
                'subtitle' => 'Pandas, NumPy and real datasets',
                'category_id' => $data->id,
                'level' => 'beginner',
                'is_free' => true,
                'price' => 0,
                'lessons' => ['Intro to Pandas', 'Cleaning Data', 'Visualization', 'Your First Analysis'],
            ],
        ];

        foreach ($courses as $data) {
            $lessonTitles = $data['lessons'];
            unset($data['lessons']);

            $course = Course::updateOrCreate(
                ['title' => $data['title']],
                array_merge($data, [
                    'teacher_id' => $teacher->id,
                    'status' => 'published',
                    'published_at' => now(),
                    'description' => 'This course gives you hands-on, practical skills through short focused lessons and real projects. Enroll and start learning today.',
                    'duration_minutes' => count($lessonTitles) * 12,
                ]),
            );

            foreach ($lessonTitles as $i => $title) {
                Lesson::updateOrCreate(
                    ['course_id' => $course->id, 'title' => $title],
                    [
                        'type' => 'video',
                        'content' => 'Watch the video, then answer the question to unlock the next lesson.',
                        // A normal YouTube link — auto-converted to an embed by the model.
                        'video_url' => 'https://www.youtube.com/watch?v=aqz-KE-bpKQ',
                        'duration_minutes' => 12,
                        'is_preview' => $i === 0, // first lesson is a free preview
                        'sort_order' => $i,
                        // One quiz question per lesson (unlocks the next on correct answer).
                        'question' => "What did you learn in \"{$title}\"?",
                        'question_options' => ['A key concept', 'Nothing at all', 'Only the intro', 'It was skipped'],
                        'question_correct_index' => 0,
                    ],
                );
            }
        }
    }
}
