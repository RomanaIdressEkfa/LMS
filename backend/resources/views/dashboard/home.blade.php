@extends('layouts.bare')
@section('title', 'Dashboard — LMS')

@section('content')
<div class="mx-auto max-w-3xl px-5 py-16 md:px-8">
    <div class="flex items-center justify-between">
        <a href="/" class="flex items-center gap-2.5">
            <span class="grid h-9 w-9 place-items-center rounded-xl grad-primary text-lg text-white">✦</span>
            <span class="text-xl font-extrabold">LMS</span>
        </a>
        <form method="POST" action="/logout">
            @csrf
            <button class="btn-ghost">Logout</button>
        </form>
    </div>

    <div class="card mt-8 p-8">
        <span class="text-4xl">👋</span>
        <h1 class="mt-3 text-3xl">Hello, {{ $user->name }}</h1>
        <p class="mt-1 text-[var(--muted)]">You are signed in as
            <b class="text-[var(--foreground)]">{{ $user->getRoleNames()->join(', ') ?: 'user' }}</b>.
        </p>

        <div class="mt-6 rounded-[var(--radius)] bg-[var(--primary-soft)] p-5">
            <p class="font-bold text-[var(--primary)]">🚧 The full dashboard is being rebuilt in Blade (Phase 3).</p>
            <p class="mt-1 text-sm font-semibold text-[var(--muted)]">Learning, teaching, quizzes, certificates and admin tools are coming next.</p>
        </div>

        <div class="mt-6 flex flex-wrap gap-3">
            <a href="/courses" class="btn-primary">Browse courses</a>
            <a href="/" class="btn-ghost">Back to site</a>
        </div>
    </div>
</div>
@endsection
