<?php

namespace Tests\Feature;

use App\Models\Quiz;
use App\Models\QuizQuestion;
use Tests\TestCase;

class QuizAuthoringTest extends TestCase
{
    public function test_teacher_can_create_a_quiz_and_lands_on_the_builder(): void
    {
        $teacher = $this->userWithRole('teacher');

        $response = $this->actingAs($teacher)->post('/dashboard/quizzes', [
            'title' => 'Algebra Basics',
            'pass_mark' => 60,
            'time_limit_minutes' => 0,
        ]);

        $quiz = Quiz::where('title', 'Algebra Basics')->first();
        $this->assertNotNull($quiz);
        $this->assertSame($teacher->id, $quiz->teacher_id);
        $response->assertRedirect("/dashboard/quizzes/{$quiz->id}/edit");
    }

    public function test_adding_a_question_filters_blanks_and_clamps_correct_index(): void
    {
        $teacher = $this->userWithRole('teacher');
        $quiz = Quiz::create(['teacher_id' => $teacher->id, 'title' => 'Q', 'pass_mark' => 70]);

        $this->actingAs($teacher)->post("/dashboard/quizzes/{$quiz->id}/questions", [
            'question' => 'What is 2+2?',
            'options' => ['3', '4', '', ''],   // two blanks should be dropped
            'correct_index' => 9,              // out of range -> clamp to last valid
            'points' => 2,
        ])->assertRedirect("/dashboard/quizzes/{$quiz->id}/edit");

        $q = $quiz->questions()->first();
        $this->assertSame(['3', '4'], $q->options);
        $this->assertSame(1, $q->correct_index);
        $this->assertSame(2, $q->points);
    }

    public function test_publishing_is_blocked_until_a_question_exists(): void
    {
        $teacher = $this->userWithRole('teacher');
        $quiz = Quiz::create(['teacher_id' => $teacher->id, 'title' => 'Empty', 'pass_mark' => 70]);

        $this->actingAs($teacher)->post("/dashboard/quizzes/{$quiz->id}/publish")
            ->assertSessionHas('error');
        $this->assertFalse($quiz->fresh()->published);

        $quiz->questions()->create([
            'question' => 'Q1', 'options' => ['a', 'b'], 'correct_index' => 0, 'points' => 1, 'sort_order' => 1,
        ]);

        $this->actingAs($teacher)->post("/dashboard/quizzes/{$quiz->id}/publish")
            ->assertSessionHas('ok');
        $this->assertTrue($quiz->fresh()->published);
    }

    public function test_a_teacher_cannot_edit_another_teachers_quiz(): void
    {
        $owner = $this->userWithRole('teacher');
        $other = $this->userWithRole('teacher');
        $quiz = Quiz::create(['teacher_id' => $owner->id, 'title' => 'Owned', 'pass_mark' => 70]);

        $this->actingAs($other)->get("/dashboard/quizzes/{$quiz->id}/edit")->assertForbidden();
        $this->actingAs($other)->put("/dashboard/quizzes/{$quiz->id}", [
            'title' => 'Hijacked',
        ])->assertForbidden();

        $this->assertSame('Owned', $quiz->fresh()->title);
    }

    public function test_deleting_a_quiz_cascades_to_questions(): void
    {
        $teacher = $this->userWithRole('teacher');
        $quiz = Quiz::create(['teacher_id' => $teacher->id, 'title' => 'Doomed', 'pass_mark' => 70]);
        $quiz->questions()->create([
            'question' => 'Q1', 'options' => ['a', 'b'], 'correct_index' => 0, 'points' => 1, 'sort_order' => 1,
        ]);

        $this->actingAs($teacher)->delete("/dashboard/quizzes/{$quiz->id}")
            ->assertRedirect('/dashboard/quizzes');

        $this->assertDatabaseMissing('quizzes', ['id' => $quiz->id]);
        $this->assertSame(0, QuizQuestion::where('quiz_id', $quiz->id)->count());
    }
}
