<?php

namespace App\Http\Controllers;

use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\QuizQuestion;
use Illuminate\Http\Request;

/**
 * Student quiz flow (Blade): browse published quizzes and take them. Grading is
 * server-side in submit(); the take page posts answers via an Alpine fetch.
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

        $mine = $user->can('quizzes.create')
            ? Quiz::where('teacher_id', $user->id)->withCount('questions', 'attempts')->orderByDesc('id')->get()
            : collect();

        return view('dashboard.quizzes.index', compact('quizzes', 'mine'));
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
}
