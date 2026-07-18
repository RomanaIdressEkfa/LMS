@extends('layouts.dashboard')
@section('title', 'Quizzes — LMS')

@section('content')
<div class="space-y-8" x-data="{ showForm: {{ $errors->any() ? 'true' : 'false' }} }">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-3xl">Quizzes</h1>
            <p class="mt-1 text-[var(--muted)]">Test your knowledge and earn passing scores.</p>
        </div>
        @can('quizzes.create')
            <button @click="showForm = !showForm" class="btn-primary" x-text="showForm ? 'Close' : '+ New quiz'"></button>
        @endcan
    </div>

    @if (session('ok'))
        <p class="rounded-[var(--radius)] bg-[var(--success)]/10 px-4 py-3 text-sm font-bold text-[var(--success)]">{{ session('ok') }}</p>
    @endif

    @can('quizzes.create')
        <form x-show="showForm" x-cloak method="POST" action="/dashboard/quizzes" class="card space-y-4 p-6">
            @csrf
            <h2 class="text-xl">New quiz</h2>
            <div>
                <label class="label">Title</label>
                <input name="title" class="input" value="{{ old('title') }}" required>
                @error('title') <p class="mt-1 text-xs font-bold text-[var(--danger)]">{{ $message }}</p> @enderror
            </div>
            <div class="grid gap-4 sm:grid-cols-3">
                <div>
                    <label class="label">Course (optional)</label>
                    <select name="course_id" class="input">
                        <option value="">None</option>
                        @foreach ($courses as $c)
                            <option value="{{ $c->id }}" @selected(old('course_id') == $c->id)>{{ $c->title }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="label">Pass mark (%)</label>
                    <input type="number" name="pass_mark" min="0" max="100" class="input" value="{{ old('pass_mark', 70) }}">
                </div>
                <div>
                    <label class="label">Time limit (min, 0 = none)</label>
                    <input type="number" name="time_limit_minutes" min="0" class="input" value="{{ old('time_limit_minutes', 0) }}">
                </div>
            </div>
            <button type="submit" class="btn-primary">Create quiz</button>
        </form>
    @endcan

    @if ($mine->isNotEmpty())
        <div>
            <h2 class="text-xl">My quizzes</h2>
            <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($mine as $q)
                    <a href="/dashboard/quizzes/{{ $q->id }}/edit" class="card block p-5 transition-transform hover:-translate-y-1">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg">{{ $q->title }}</h3>
                            <span class="rounded-full px-2.5 py-1 text-[11px] font-bold uppercase {{ $q->published ? 'bg-[var(--success)]/15 text-[var(--success)]' : 'bg-[var(--warning)]/15 text-[var(--warning)]' }}">{{ $q->published ? 'published' : 'draft' }}</span>
                        </div>
                        <p class="mt-2 text-sm text-[var(--muted)]">{{ $q->questions_count }} questions · {{ $q->attempts_count }} attempts</p>
                        <p class="mt-3 text-sm font-bold text-[var(--primary)]">Edit quiz →</p>
                    </a>
                @endforeach
            </div>
        </div>
    @endif

    <div>
        <h2 class="text-xl">Available quizzes</h2>
        @if ($quizzes->isEmpty())
            <div class="card mt-4 grid place-items-center p-12 text-center">
                <span class="text-4xl">📝</span>
                <p class="mt-3 font-bold">No published quizzes yet.</p>
            </div>
        @else
            <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($quizzes as $q)
                    <div class="card flex flex-col p-5">
                        <h3 class="text-lg">{{ $q->title }}</h3>
                        <p class="mt-1 flex-1 text-sm text-[var(--muted)]">{{ $q->description }}</p>
                        <div class="mt-3 flex flex-wrap gap-3 text-xs font-semibold text-[var(--muted)]">
                            <span>❓ {{ $q->questions_count }} questions</span>
                            <span>🎯 {{ $q->pass_mark }}% pass</span>
                            @if (! is_null($q->best_score))
                                <span class="font-bold text-[var(--primary)]">Best: {{ $q->best_score }}%</span>
                            @endif
                        </div>
                        <a href="/dashboard/quizzes/{{ $q->id }}/take" class="btn-primary mt-4">{{ is_null($q->best_score) ? 'Start quiz' : 'Retake' }}</a>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection
