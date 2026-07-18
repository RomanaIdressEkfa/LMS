<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\QuizQuestion;
use Illuminate\Http\Request;

/**
 * Quiz flow (Blade). Students browse published quizzes and take them (grading is
 * server-side in submit(), posted via an Alpine fetch). Creators (quizzes.create)
 * build quizzes in edit(): quiz details + a question builder, all via web forms.
 */
class QuizWebController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $quizzes = Quiz::where('published', true)
            ->with('course:id,title', 'teacher:id,name')
            ->withCount('questions')
            ->orderByDesc('id')
            ->get()
            ->map(function ($q) use ($user) {
                $q->best_score = QuizAttempt::where('user_id', $user->id)->where('quiz_id', $q->id)->max('score');

                return $q;
            });

        $canCreate = $user->can('quizzes.create');
        $mine = $canCreate
            ? Quiz::where('teacher_id', $user->id)->withCount('questions', 'attempts')->orderByDesc('id')->get()
            : collect();
        $courses = $canCreate
            ? $user->teacherCourses()->orderBy('title')->get(['id', 'title'])
            : collect();

        return view('dashboard.quizzes.index', compact('quizzes', 'mine', 'courses'));
    }

    public function take(Quiz $quiz)
    {
        abort_unless($quiz->published, 404);
        $quiz->load('questions:id,quiz_id,question,options,points,sort_order');

        return view('dashboard.quizzes.take', compact('quiz'));
    }

    public function submit(Request $request, Quiz $quiz)
    {
        abort_unless($quiz->published, 404);
        $answers = $request->validate(['answers' => ['required', 'array']])['answers'];

        $quiz->load('questions');
        $total = 0;
        $earned = 0;
        foreach ($quiz->questions as $q) {
            $total += $q->points;
            if (isset($answers[$q->id]) && (int) $answers[$q->id] === (int) $q->correct_index) {
                $earned += $q->points;
            }
        }
        $score = $total > 0 ? (int) round(($earned / $total) * 100) : 0;
        $passed = $score >= $quiz->pass_mark;

        QuizAttempt::create([
            'user_id' => $request->user()->id,
            'quiz_id' => $quiz->id,
            'score' => $score,
            'passed' => $passed,
            'answers' => $answers,
            'completed_at' => now(),
        ]);

        return response()->json(compact('score', 'passed', 'earned', 'total') + ['pass_mark' => $quiz->pass_mark]);
    }

    /* ---------------------------------------------------------------------
     |  Creator-facing builder (permission: quizzes.create)
     * ------------------------------------------------------------------- */

    /** Create a blank quiz, then drop the creator into its builder. */
    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:200'],
            'course_id' => ['nullable', 'exists:courses,id'],
            'pass_mark' => ['nullable', 'integer', 'min:0', 'max:100'],
            'time_limit_minutes' => ['nullable', 'integer', 'min:0'],
        ]);
        $data['teacher_id'] = $request->user()->id;
        $quiz = Quiz::create($data);

        return redirect("/dashboard/quizzes/{$quiz->id}/edit")->with('ok', 'Quiz created — add your questions below.');
    }

    /** The builder: quiz details + question list/editor. Owner only. */
    public function edit(Request $request, Quiz $quiz)
    {
        $this->ensureOwner($request, $quiz);
        $quiz->load('questions');
        $courses = $request->user()->teacherCourses()->orderBy('title')->get(['id', 'title']);

        return view('dashboard.quizzes.edit', compact('quiz', 'courses'));
    }

    public function update(Request $request, Quiz $quiz)
    {
        $this->ensureOwner($request, $quiz);
        $data = $request->validate([
            'title' => ['required', 'string', 'max:200'],
            'description' => ['nullable', 'string'],
            'course_id' => ['nullable', 'exists:courses,id'],
            'pass_mark' => ['nullable', 'integer', 'min:0', 'max:100'],
            'time_limit_minutes' => ['nullable', 'integer', 'min:0'],
        ]);
        $quiz->update($data);

        return back()->with('ok', 'Quiz saved.');
    }

    public function togglePublish(Request $request, Quiz $quiz)
    {
        $this->ensureOwner($request, $quiz);
        if (! $quiz->published && $quiz->questions()->count() === 0) {
            return back()->with('error', 'Add at least one question before publishing.');
        }
        $quiz->update(['published' => ! $quiz->published]);

        return back()->with('ok', $quiz->published ? 'Quiz published.' : 'Quiz unpublished.');
    }

    public function destroy(Request $request, Quiz $quiz)
    {
        $this->ensureOwner($request, $quiz);
        $quiz->delete();

        return redirect('/dashboard/quizzes')->with('ok', 'Quiz deleted.');
    }

    public function storeQuestion(Request $request, Quiz $quiz)
    {
        $this->ensureOwner($request, $quiz);
        $data = $this->validateQuestion($request);
        $data['sort_order'] = (int) $quiz->questions()->max('sort_order') + 1;
        $quiz->questions()->create($data);

        return redirect("/dashboard/quizzes/{$quiz->id}/edit")->with('ok', 'Question added.');
    }

    public function updateQuestion(Request $request, Quiz $quiz, QuizQuestion $question)
    {
        $this->ensureOwner($request, $quiz);
        abort_unless($question->quiz_id === $quiz->id, 404);
        $question->update($this->validateQuestion($request));

        return redirect("/dashboard/quizzes/{$quiz->id}/edit")->with('ok', 'Question updated.');
    }

    public function destroyQuestion(Request $request, Quiz $quiz, QuizQuestion $question)
    {
        $this->ensureOwner($request, $quiz);
        abort_unless($question->quiz_id === $quiz->id, 404);
        $question->delete();

        return back()->with('ok', 'Question deleted.');
    }

    /** Normalise the option list: drop blanks, clamp correct_index into range. */
    private function validateQuestion(Request $request): array
    {
        $data = $request->validate([
            'question' => ['required', 'string'],
            'options' => ['required', 'array', 'min:2'],
            'options.*' => ['nullable', 'string'],
            'correct_index' => ['required', 'integer', 'min:0'],
            'points' => ['nullable', 'integer', 'min:1'],
        ]);

        $opts = array_values(array_filter($data['options'], fn ($o) => trim((string) $o) !== ''));
        abort_if(count($opts) < 2, 422, 'A question needs at least two non-empty options.');

        return [
            'question' => $data['question'],
            'options' => $opts,
            'correct_index' => min((int) $data['correct_index'], count($opts) - 1),
            'points' => (int) ($data['points'] ?? 1),
        ];
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
