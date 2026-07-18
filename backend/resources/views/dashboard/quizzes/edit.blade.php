@extends('layouts.dashboard')
@section('title', 'Edit Quiz — LMS')

@php
    use Illuminate\Support\Js;
    $editing = request('question') ? $quiz->questions->firstWhere('id', (int) request('question')) : null;
    $eOptions = $editing && is_array($editing->options) && count($editing->options) >= 2 ? array_values($editing->options) : ['', ''];
    $eCorrect = (int) ($editing->correct_index ?? 0);
@endphp

@section('content')
<div class="space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <a href="/dashboard/quizzes" class="text-sm font-bold text-[var(--primary)] hover:underline">← Quizzes</a>
            <h1 class="mt-1 text-3xl">Edit Quiz</h1>
        </div>
        <div class="flex items-center gap-2">
            <form method="POST" action="/dashboard/quizzes/{{ $quiz->id }}/publish">
                @csrf
                <button class="{{ $quiz->published ? 'btn-ghost' : 'btn-primary' }}">{{ $quiz->published ? 'Unpublish' : 'Publish' }}</button>
            </form>
            <form method="POST" action="/dashboard/quizzes/{{ $quiz->id }}" onsubmit="return confirm('Delete this quiz and all its questions?')">
                @csrf @method('DELETE')
                <button class="btn-ghost text-[var(--danger)]">Delete</button>
            </form>
        </div>
    </div>

    @if (session('ok'))
        <p class="rounded-[var(--radius)] bg-[var(--success)]/10 px-4 py-3 text-sm font-bold text-[var(--success)]">{{ session('ok') }}</p>
    @endif
    @if (session('error'))
        <p class="rounded-[var(--radius)] bg-[var(--danger)]/10 px-4 py-3 text-sm font-bold text-[var(--danger)]">{{ session('error') }}</p>
    @endif

    <div class="grid gap-6 lg:grid-cols-2">
        {{-- Quiz details --}}
        <form method="POST" action="/dashboard/quizzes/{{ $quiz->id }}" class="card space-y-4 p-6">
            @csrf @method('PUT')
            <div class="flex items-center justify-between">
                <h2 class="text-xl">Details</h2>
                <span class="rounded-full px-2.5 py-1 text-[11px] font-bold uppercase {{ $quiz->published ? 'bg-[var(--success)]/15 text-[var(--success)]' : 'bg-[var(--warning)]/15 text-[var(--warning)]' }}">{{ $quiz->published ? 'published' : 'draft' }}</span>
            </div>
            <div>
                <label class="label">Title</label>
                <input name="title" class="input" value="{{ old('title', $quiz->title) }}" required>
                @error('title') <p class="mt-1 text-xs font-bold text-[var(--danger)]">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="label">Description</label>
                <textarea name="description" class="input min-h-24" placeholder="What this quiz covers…">{{ old('description', $quiz->description) }}</textarea>
            </div>
            <div>
                <label class="label">Course (optional)</label>
                <select name="course_id" class="input">
                    <option value="">None</option>
                    @foreach ($courses as $c)
                        <option value="{{ $c->id }}" @selected(old('course_id', $quiz->course_id) == $c->id)>{{ $c->title }}</option>
                    @endforeach
                </select>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="label">Pass mark (%)</label>
                    <input type="number" name="pass_mark" min="0" max="100" class="input" value="{{ old('pass_mark', $quiz->pass_mark) }}">
                </div>
                <div>
                    <label class="label">Time limit (min)</label>
                    <input type="number" name="time_limit_minutes" min="0" class="input" value="{{ old('time_limit_minutes', $quiz->time_limit_minutes) }}">
                    <p class="mt-1 text-xs text-[var(--muted)]">0 = untimed</p>
                </div>
            </div>
            <button type="submit" class="btn-primary">Save details</button>
        </form>

        {{-- Questions --}}
        <div class="space-y-6">
            <div class="card p-6">
                <h2 class="text-xl">Questions ({{ $quiz->questions->count() }})</h2>
                <div class="mt-4 space-y-2">
                    @forelse ($quiz->questions as $i => $q)
                        <div class="flex items-start gap-3 rounded-[var(--radius)] border border-[var(--border)] p-3 {{ $editing && $editing->id === $q->id ? 'ring-2 ring-[var(--primary)]' : '' }}">
                            <span class="mt-0.5 grid h-7 w-7 shrink-0 place-items-center rounded-full bg-[var(--primary)]/10 text-xs font-bold text-[var(--primary)]">{{ $i + 1 }}</span>
                            <div class="min-w-0 flex-1">
                                <p class="truncate font-bold text-[var(--foreground)]">{{ $q->question }}</p>
                                <p class="text-xs text-[var(--muted)]">{{ count($q->options) }} options · {{ $q->points }} pt{{ $q->points === 1 ? '' : 's' }} · ✓ {{ $q->options[$q->correct_index] ?? '—' }}</p>
                            </div>
                            <a href="/dashboard/quizzes/{{ $quiz->id }}/edit?question={{ $q->id }}" class="text-sm font-bold text-[var(--primary)] hover:underline">Edit</a>
                            <form method="POST" action="/dashboard/quizzes/{{ $quiz->id }}/questions/{{ $q->id }}" onsubmit="return confirm('Delete this question?')">
                                @csrf @method('DELETE')
                                <button class="text-sm font-bold text-[var(--danger)] hover:underline">Delete</button>
                            </form>
                        </div>
                    @empty
                        <p class="text-sm text-[var(--muted)]">No questions yet — add your first below.</p>
                    @endforelse
                </div>
            </div>

            {{-- Add / edit question --}}
            <form method="POST"
                action="/dashboard/quizzes/{{ $quiz->id }}/questions{{ $editing ? '/' . $editing->id : '' }}"
                class="card space-y-3 p-6"
                x-data="{
                    options: {{ Js::from($eOptions) }},
                    correct: {{ $eCorrect }},
                    addOption() { if (this.options.length < 6) this.options.push(''); },
                    removeOption(i) { if (this.options.length > 2) { this.options.splice(i, 1); if (this.correct >= this.options.length) this.correct = 0; } },
                }">
                @csrf
                @if ($editing) @method('PUT') @endif
                <h3 class="text-lg">{{ $editing ? 'Edit question' : 'Add question' }}</h3>
                <textarea name="question" class="input min-h-16" placeholder="Question text" required>{{ old('question', $editing->question ?? '') }}</textarea>
                @error('question') <p class="text-xs font-bold text-[var(--danger)]">{{ $message }}</p> @enderror

                <p class="label">Options (pick the correct one)</p>
                <template x-for="(opt, i) in options" :key="i">
                    <div class="mb-2 flex items-center gap-2">
                        <input type="radio" name="correct_index" :value="i" :checked="correct === i" @change="correct = i" class="h-4 w-4 accent-[var(--success)]">
                        <input class="input" name="options[]" :placeholder="`Option ${i + 1}`" x-model="options[i]">
                        <button type="button" x-show="options.length > 2" @click="removeOption(i)" class="text-lg text-[var(--danger)]">×</button>
                    </div>
                </template>
                <button type="button" x-show="options.length < 6" @click="addOption()" class="text-sm font-bold text-[var(--primary)] hover:underline">+ Add option</button>

                <div class="w-32">
                    <label class="label">Points</label>
                    <input type="number" name="points" min="1" class="input" value="{{ old('points', $editing->points ?? 1) }}">
                </div>

                <div class="flex gap-2 pt-1">
                    <button type="submit" class="btn-primary">{{ $editing ? 'Update question' : 'Add question' }}</button>
                    @if ($editing)
                        <a href="/dashboard/quizzes/{{ $quiz->id }}/edit" class="btn-ghost">Cancel</a>
                    @endif
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
