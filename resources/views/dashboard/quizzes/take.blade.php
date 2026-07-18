@extends('layouts.dashboard')
@section('title', $quiz->title . ' — Quiz')

@section('content')
<div class="mx-auto max-w-3xl space-y-6"
    x-data="{
        answers: {},
        submitting: false,
        result: null,
        get answered() { return Object.keys(this.answers).length; },
        async submit() {
            this.submitting = true;
            try {
                const res = await fetch('/dashboard/quizzes/{{ $quiz->id }}/submit', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
                    body: JSON.stringify({ answers: this.answers })
                });
                this.result = await res.json();
                window.scrollTo({ top: 0, behavior: 'smooth' });
            } finally { this.submitting = false; }
        }
    }">
    <a href="/dashboard/quizzes" class="text-sm font-bold text-[var(--primary)] hover:underline">← All quizzes</a>

    {{-- Result banner --}}
    <template x-if="result">
        <div class="card p-6 text-center" :class="result.passed ? 'border-[var(--success)]/40 bg-[var(--success)]/5' : 'border-[var(--danger)]/40 bg-[var(--danger)]/5'">
            <span class="text-4xl" x-text="result.passed ? '🎉' : '😔'"></span>
            <h2 class="mt-2 text-2xl" x-text="result.passed ? 'Passed!' : 'Not passed'"></h2>
            <p class="mt-1 font-bold" :class="result.passed ? 'text-[var(--success)]' : 'text-[var(--danger)]'">
                <span x-text="result.score"></span>% — <span x-text="result.earned"></span>/<span x-text="result.total"></span> points (need <span x-text="result.pass_mark"></span>%)
            </p>
            <div class="mt-4 flex justify-center gap-3">
                <a href="/dashboard/quizzes" class="btn-ghost">Back to quizzes</a>
                <button @click="result = null; answers = {}" class="btn-primary">Retake</button>
            </div>
        </div>
    </template>

    <div x-show="!result">
        <div>
            <h1 class="text-3xl">{{ $quiz->title }}</h1>
            @if ($quiz->description)<p class="mt-1 text-[var(--muted)]">{{ $quiz->description }}</p>@endif
            <p class="mt-2 text-sm font-bold text-[var(--muted)]">Pass mark: {{ $quiz->pass_mark }}% · {{ $quiz->questions->count() }} questions</p>
        </div>

        <div class="mt-6 space-y-4">
            @foreach ($quiz->questions as $qi => $q)
                <div class="card p-6">
                    <p class="font-extrabold">{{ $qi + 1 }}. {{ $q->question }}</p>
                    <div class="mt-3 space-y-2">
                        @foreach (($q->options ?? []) as $oi => $opt)
                            <label :class="answers[{{ $q->id }}] === {{ $oi }} ? 'border-[var(--primary)] bg-[var(--primary-soft)]' : 'border-[var(--border)] bg-[var(--surface)] hover:border-[var(--primary)]'"
                                class="flex cursor-pointer items-center gap-3 rounded-xl border p-3 text-sm font-bold transition-colors">
                                <input type="radio" name="q{{ $q->id }}" @click="answers[{{ $q->id }}] = {{ $oi }}" class="h-4 w-4 accent-[var(--primary)]">
                                {{ $opt }}
                            </label>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>

        <div class="sticky bottom-4 mt-6 flex items-center gap-3 rounded-[var(--radius)] border border-[var(--border)] bg-[var(--surface)]/95 p-4 shadow-[var(--shadow-card)] backdrop-blur">
            <button @click="submit()" :disabled="submitting || answered === 0" class="btn-primary disabled:opacity-50">
                <span x-show="!submitting">Submit quiz</span><span x-show="submitting" x-cloak>Grading…</span>
            </button>
            <span class="text-sm font-bold text-[var(--muted)]"><span x-text="answered"></span>/{{ $quiz->questions->count() }} answered</span>
        </div>
    </div>
</div>
@endsection
