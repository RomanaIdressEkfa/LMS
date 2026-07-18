@extends('layouts.dashboard')
@section('title', $course->title . ' — LMS')

@section('content')
<div class="grid gap-6 lg:grid-cols-[1fr_340px]">
    {{-- Player + quiz --}}
    <div class="space-y-4">
        <a href="/dashboard/learn" class="text-sm font-bold text-[var(--primary)] hover:underline">← <x-t k="learn.title" /></a>

        @if ($progress >= 100)
            <div class="card flex flex-wrap items-center justify-between gap-3 border-[var(--success)]/40 bg-[var(--success)]/5 p-5">
                <div class="flex items-center gap-3">
                    <span class="text-3xl">🎓</span>
                    <div>
                        <p class="font-extrabold text-[var(--foreground)]">Course complete — congratulations!</p>
                        <p class="text-sm text-[var(--muted)]">You've earned your certificate of completion.</p>
                    </div>
                </div>
                <a href="/dashboard/certificate/{{ $course->slug }}" class="btn-primary shrink-0">Get your certificate →</a>
            </div>
        @endif

        @if ($active)
            <div class="card overflow-hidden"
                x-data="{ answer: null, result: null, submitting: false,
                    async submit() {
                        this.submitting = true; this.result = null;
                        try {
                            const res = await fetch('/dashboard/courses/{{ $course->id }}/lessons/{{ $active->id }}/answer', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
                                body: JSON.stringify({{ $active->has_question ? '{ answer_index: this.answer }' : '{}' }})
                            });
                            const data = await res.json();
                            if (data.correct) {
                                window.location = '/dashboard/learn/{{ $course->slug }}' + (data.next_lesson_id ? ('?lesson=' + data.next_lesson_id) : '');
                            } else { this.result = data.message || 'Wrong answer — try again.'; }
                        } finally { this.submitting = false; }
                    }
                }">
                {{-- Video --}}
                @if ($active->video_file_url)
                    <video controls class="aspect-video w-full bg-black" src="{{ $active->video_file_url }}"></video>
                @elseif ($active->video_url)
                    <div class="aspect-video w-full bg-black">
                        <iframe src="{{ $active->video_url }}" class="h-full w-full" allowfullscreen title="{{ $active->title }}"
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"></iframe>
                    </div>
                @else
                    <div class="grid aspect-video place-items-center bg-[var(--background)] text-[var(--muted)]">No video for this lesson</div>
                @endif

                <div class="p-6">
                    <h1 class="text-2xl">{{ $active->title }}</h1>
                    @if ($active->content)
                        <p class="mt-2 whitespace-pre-line text-[var(--muted)]">{{ $active->content }}</p>
                    @endif

                    {{-- MCQ --}}
                    @if (! $active->completed && $active->has_question)
                        <div class="mt-6 rounded-[var(--radius)] border border-[var(--border)] bg-[var(--background)] p-5">
                            <p class="font-extrabold">🧠 {{ $active->question }}</p>
                            <div class="mt-3 space-y-2">
                                @foreach ($active->options as $i => $opt)
                                    <label :class="answer === {{ $i }} ? 'border-[var(--primary)] bg-[var(--primary-soft)]' : 'border-[var(--border)] bg-[var(--surface)] hover:border-[var(--primary)]'"
                                        class="flex cursor-pointer items-center gap-3 rounded-xl border p-3 text-sm font-bold transition-colors">
                                        <input type="radio" name="ans" value="{{ $i }}" @click="answer = {{ $i }}; result = null" class="h-4 w-4 accent-[var(--primary)]">
                                        {{ $opt }}
                                    </label>
                                @endforeach
                            </div>
                            <p x-show="result" x-cloak class="mt-3 text-sm font-bold text-[var(--danger)]" x-text="'❌ ' + result"></p>
                        </div>
                    @endif

                    @if ($active->completed)
                        <p class="mt-5 inline-flex items-center gap-2 font-bold text-[var(--success)]">✓ Lesson completed</p>
                    @else
                        <button @click="submit()"
                            :disabled="submitting || ({{ $active->has_question ? 'true' : 'false' }} && answer === null)"
                            class="btn-primary mt-5 disabled:opacity-50">
                            <span x-show="!submitting">{{ $active->has_question ? 'Submit answer & continue' : 'Mark complete & continue' }}</span>
                            <span x-show="submitting" x-cloak>Checking…</span>
                        </button>
                    @endif
                </div>
            </div>
        @endif
    </div>

    {{-- Lesson list --}}
    <div class="lg:sticky lg:top-24 lg:h-fit">
        <div class="card p-5">
            <div class="flex items-center justify-between">
                <h2 class="text-lg">Course content</h2>
                <span class="text-sm font-bold text-[var(--primary)]">{{ $progress }}%</span>
            </div>
            <div class="mt-3 h-2 overflow-hidden rounded-full bg-[var(--border)]">
                <div class="grad-primary h-full rounded-full transition-all" style="width: {{ $progress }}%"></div>
            </div>

            <div class="mt-4 space-y-1">
                @foreach ($lessons as $i => $l)
                    @php $isActive = $active && $active->id === $l->id; $locked = ! $l->unlocked; @endphp
                    <a @if (! $locked) href="/dashboard/learn/{{ $course->slug }}?lesson={{ $l->id }}" @endif
                        class="flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-left transition-colors
                            {{ $isActive ? 'bg-[var(--primary-soft)]' : ($locked ? 'opacity-60 cursor-not-allowed' : 'hover:bg-[var(--primary)]/5') }}">
                        <span class="grid h-6 w-6 shrink-0 place-items-center rounded-full text-xs font-bold
                            {{ $l->completed ? 'bg-[var(--success)] text-white' : ($locked ? 'bg-[var(--border)] text-[var(--muted)]' : 'bg-[var(--primary)] text-white') }}">
                            {{ $l->completed ? '✓' : ($locked ? '🔒' : $i + 1) }}
                        </span>
                        <span class="flex-1 text-sm font-bold text-[var(--foreground)]">{{ $l->title }}</span>
                        <span class="text-xs text-[var(--muted)]">{{ $l->duration_minutes }}m</span>
                    </a>
                @endforeach
            </div>
            <p class="mt-4 text-center text-xs font-semibold text-[var(--muted)]">🔒 lessons unlock as you complete each one.</p>
        </div>
    </div>
</div>
@endsection
