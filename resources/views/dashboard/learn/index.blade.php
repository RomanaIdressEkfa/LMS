@extends('layouts.dashboard')
@section('title', 'My Learning — LMS')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-3xl"><x-t k="learn.title" /></h1>
        <p class="mt-1 text-[var(--muted)]"><x-t k="learn.sub" /></p>
    </div>

    @if ($enrollments->isEmpty())
        <div class="card grid place-items-center p-12 text-center">
            <span class="text-4xl">📚</span>
            <p class="mt-3 font-bold"><x-t k="learn.empty" /></p>
            <a href="/dashboard/courses" class="btn-primary mt-4"><x-t k="learn.browse" /></a>
        </div>
    @else
        <div class="grid gap-4 sm:grid-cols-2">
            @foreach ($enrollments as $e)
                <div class="space-y-2">
                    <a href="/dashboard/learn/{{ $e->course->slug }}" class="card block p-5 transition-transform hover:-translate-y-1">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg">{{ $e->course->title }}</h3>
                            <span class="text-sm font-bold text-[var(--primary)]">{{ (int) $e->progress }}%</span>
                        </div>
                        <p class="mt-1 text-sm text-[var(--muted)]">{{ optional($e->course->teacher)->name }}</p>
                        <div class="mt-4 h-2 overflow-hidden rounded-full bg-[var(--border)]">
                            <div class="h-full rounded-full bg-[var(--primary)] transition-all" style="width: {{ (int) $e->progress }}%"></div>
                        </div>
                        <p class="mt-3 text-sm font-bold text-[var(--primary)]">
                            @if ($e->progress >= 100) ✅ <x-t k="learn.completed" /> @else <x-t k="learn.continue" /> → @endif
                        </p>
                    </a>
                    @if ($e->progress >= 100)
                        <a href="/dashboard/certificate/{{ $e->course->slug }}" class="btn-ghost flex w-full items-center justify-center gap-1.5 text-sm">🎓 Get certificate</a>
                    @endif
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
