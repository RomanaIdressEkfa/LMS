<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\QuizQuestion;
use Illuminate\Http\Request;

class QuizController extends Controller
{
    /* ---------------------------------------------------------------------
     |  Student-facing
     * ------------------------------------------------------------------- */

    /** Published quizzes a student can take, with their best attempt. */
    public function available(Request $request)
    {
        $user = $request->user();

        $quizzes = Quiz::where('published', true)
            ->with(['course:id,title', 'teacher:id,name'])
            ->withCount('questions')
            ->orderByDesc('id')
            ->get()
            ->map(function ($q) use ($user) {
                $best = QuizAttempt::where('user_id', $user->id)
                    ->where('quiz_id', $q->id)->max('score');
                return array_merge($q->toArray(), ['best_score' => $best]);
            });

        return response()->json(['quizzes' => $quizzes]);
    }

    /** Quiz for taking — questions WITHOUT the correct answers. */
    public function take(Quiz $quiz)
    {
        abort_unless($quiz->published, 404);

        $quiz->load('questions:id,quiz_id,question,options,points,sort_order');

        $questions = $quiz->questions->map(fn (QuizQuestion $q) => [
            'id' => $q->id,
            'question' => $q->question,
            'options' => $q->options,
            'points' => $q->points,
        ]);

        return response()->json([
            'quiz' => [
                'id' => $quiz->id,
                'title' => $quiz->title,
                'description' => $quiz->description,
                'pass_mark' => $quiz->pass_mark,
                'time_limit_minutes' => $quiz->time_limit_minutes,
                'questions' => $questions,
            ],
        ]);
    }

    /** Grade a submission server-side. answers = { questionId: chosenIndex }. */
    public function submit(Request $request, Quiz $quiz)
    {
        abort_unless($quiz->published, 404);

        $data = $request->validate([
            'answers' => ['required', 'array'],
        ]);
        $answers = $data['answers'];

        $quiz->load('questions');
        $totalPoints = 0;
        $earned = 0;

        foreach ($quiz->questions as $q) {
            $totalPoints += $q->points;
            $chosen = $answers[$q->id] ?? null;
            if ($chosen !== null && (int) $chosen === (int) $q->correct_index) {
                $earned += $q->points;
            }
        }

        $score = $totalPoints > 0 ? (int) round(($earned / $totalPoints) * 100) : 0;
        $passed = $score >= $quiz->pass_mark;

        $attempt = QuizAttempt::create([
            'user_id' => $request->user()->id,
            'quiz_id' => $quiz->id,
            'score' => $score,
            'passed' => $passed,
            'answers' => $answers,
            'completed_at' => now(),
        ]);

        return response()->json([
            'result' => [
                'score' => $score,
                'passed' => $passed,
                'pass_mark' => $quiz->pass_mark,
                'earned' => $earned,
                'total' => $totalPoints,
                'attempt_id' => $attempt->id,
            ],
        ], 201);
    }

    /* ---------------------------------------------------------------------
     |  Teacher-facing (builder)
     * ------------------------------------------------------------------- */

    public function mine(Request $request)
    {
        $quizzes = Quiz::where('teacher_id', $request->user()->id)
            ->withCount(['questions', 'attempts'])
            ->orderByDesc('id')
            ->get();

        return response()->json(['quizzes' => $quizzes]);
    }

    /** Full quiz incl. correct answers — owner only (the editor). */
    public function manage(Request $request, Quiz $quiz)
    {
        $this->ensureOwner($request, $quiz);
        $quiz->load('questions');

        return response()->json(['quiz' => $quiz]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:200'],
            'description' => ['nullable', 'string'],
            'course_id' => ['nullable', 'exists:courses,id'],
            'pass_mark' => ['nullable', 'integer', 'min:0', 'max:100'],
            'time_limit_minutes' => ['nullable', 'integer', 'min:0'],
        ]);
        $data['teacher_id'] = $request->user()->id;

        $quiz = Quiz::create($data);

        return response()->json(['quiz' => $quiz], 201);
    }

    public function update(Request $request, Quiz $quiz)
    {
        $this->ensureOwner($request, $quiz);
        $data = $request->validate([
            'title' => ['required', 'string', 'max:200'],
            'description' => ['nullable', 'string'],
            'pass_mark' => ['nullable', 'integer', 'min:0', 'max:100'],
            'time_limit_minutes' => ['nullable', 'integer', 'min:0'],
            'published' => ['boolean'],
        ]);
        $quiz->update($data);

        return response()->json(['quiz' => $quiz->fresh()]);
    }

    public function destroy(Request $request, Quiz $quiz)
    {
        $this->ensureOwner($request, $quiz);
        $quiz->delete();

        return response()->json(['message' => 'Quiz deleted.']);
    }

    public function addQuestion(Request $request, Quiz $quiz)
    {
        $this->ensureOwner($request, $quiz);
        $data = $this->validateQuestion($request);
        $data['sort_order'] = (int) $quiz->questions()->max('sort_order') + 1;

        $question = $quiz->questions()->create($data);

        return response()->json(['question' => $question], 201);
    }

    public function updateQuestion(Request $request, Quiz $quiz, QuizQuestion $question)
    {
        $this->ensureOwner($request, $quiz);
        abort_unless($question->quiz_id === $quiz->id, 404);
        $question->update($this->validateQuestion($request));

        return response()->json(['question' => $question->fresh()]);
    }

    public function deleteQuestion(Request $request, Quiz $quiz, QuizQuestion $question)
    {
        $this->ensureOwner($request, $quiz);
        abort_unless($question->quiz_id === $quiz->id, 404);
        $question->delete();

        return response()->json(['message' => 'Question deleted.']);
    }

    private function validateQuestion(Request $request): array
    {
        return $request->validate([
            'question' => ['required', 'string'],
            'options' => ['required', 'array', 'min:2'],
            'options.*' => ['required', 'string'],
            'correct_index' => ['required', 'integer', 'min:0'],
            'points' => ['nullable', 'integer', 'min:1'],
        ]);
    }

    private function ensureOwner(Request $request, Quiz $quiz): void
    {
        abort_unless(
            $quiz->teacher_id === $request->user()->id || $request->user()->hasRole(['admin', 'super-admin']),
            403,
            'You can only manage your own quizzes.'
        );
    }
}
