<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Lesson;
use App\Models\User;
use Tests\TestCase;

class LearningFlowTest extends TestCase
{
    /** A published free course with two MCQ-gated lessons. */
    private function freeCourseWithTwoLessons(): Course
    {
        $teacher = $this->userWithRole('teacher');
        $course = Course::create([
            'teacher_id' => $teacher->id,
            'title' => 'Two Step Course',
            'level' => 'beginner',
            'is_free' => true,
            'status' => 'published',
            'published_at' => now(),
        ]);

        foreach ([1, 2] as $n) {
            $course->lessons()->create([
                'title' => "Lesson $n",
                'type' => 'video',
                'question' => "Q$n?",
                'question_options' => ['right', 'wrong'],
                'question_correct_index' => 0,
                'sort_order' => $n,
            ]);
        }

        return $course;
    }

    private function answer(User $student, Course $course, Lesson $lesson, int $index)
    {
        return $this->actingAs($student)->postJson(
            "/dashboard/courses/{$course->id}/lessons/{$lesson->id}/answer",
            ['answer_index' => $index],
        );
    }

    public function test_student_can_enroll_in_a_free_course(): void
    {
        $student = $this->userWithRole('student');
        $course = $this->freeCourseWithTwoLessons();

        $this->actingAs($student)->post("/dashboard/courses/{$course->id}/enroll")
            ->assertRedirect("/dashboard/learn/{$course->slug}");

        $this->assertDatabaseHas('enrollments', [
            'user_id' => $student->id, 'course_id' => $course->id, 'source' => 'free',
        ]);
    }

    public function test_paid_course_enrollment_is_deferred_to_checkout(): void
    {
        $student = $this->userWithRole('student');
        $teacher = $this->userWithRole('teacher');
        $paid = Course::create([
            'teacher_id' => $teacher->id, 'title' => 'Paid', 'level' => 'beginner',
            'is_free' => false, 'price' => 49, 'status' => 'published', 'published_at' => now(),
        ]);

        $this->actingAs($student)->post("/dashboard/courses/{$paid->id}/enroll")
            ->assertRedirect("/courses/{$paid->slug}");
        $this->assertDatabaseCount('enrollments', 0);
    }

    public function test_correct_answer_unlocks_the_next_lesson_and_advances_progress(): void
    {
        $student = $this->userWithRole('student');
        $course = $this->freeCourseWithTwoLessons();
        $course->enrollments()->create(['user_id' => $student->id]);
        [$l1, $l2] = $course->lessons->all();

        $this->answer($student, $course, $l1, 0)
            ->assertOk()
            ->assertJson(['correct' => true, 'progress' => 50, 'next_lesson_id' => $l2->id]);

        $this->assertDatabaseHas('lesson_progress', [
            'user_id' => $student->id, 'lesson_id' => $l1->id, 'completed' => true,
        ]);
    }

    public function test_wrong_answer_does_not_complete_the_lesson(): void
    {
        $student = $this->userWithRole('student');
        $course = $this->freeCourseWithTwoLessons();
        $course->enrollments()->create(['user_id' => $student->id]);
        $l1 = $course->lessons->first();

        $this->answer($student, $course, $l1, 1)->assertOk()->assertJson(['correct' => false]);
        $this->assertDatabaseMissing('lesson_progress', ['lesson_id' => $l1->id]);
    }

    public function test_later_lesson_is_locked_until_the_previous_is_done(): void
    {
        $student = $this->userWithRole('student');
        $course = $this->freeCourseWithTwoLessons();
        $course->enrollments()->create(['user_id' => $student->id]);
        $l2 = $course->lessons->last();

        $this->answer($student, $course, $l2, 0)->assertStatus(423);
    }

    public function test_non_enrolled_user_cannot_answer(): void
    {
        $student = $this->userWithRole('student');
        $course = $this->freeCourseWithTwoLessons();
        $l1 = $course->lessons->first();

        $this->answer($student, $course, $l1, 0)->assertForbidden();
    }

    public function test_finishing_all_lessons_reaches_100_and_unlocks_certificate(): void
    {
        $student = $this->userWithRole('student');
        $course = $this->freeCourseWithTwoLessons();
        $course->enrollments()->create(['user_id' => $student->id]);
        [$l1, $l2] = $course->lessons->all();

        // Certificate is gated before completion.
        $this->actingAs($student)->get("/dashboard/certificate/{$course->slug}")->assertForbidden();

        $this->answer($student, $course, $l1, 0)->assertJson(['progress' => 50]);
        $this->answer($student, $course, $l2, 0)->assertJson(['progress' => 100]);

        $this->assertDatabaseHas('enrollments', [
            'user_id' => $student->id, 'course_id' => $course->id, 'progress' => 100,
        ]);
        $this->actingAs($student)->get("/dashboard/certificate/{$course->slug}")->assertOk();
    }
}
