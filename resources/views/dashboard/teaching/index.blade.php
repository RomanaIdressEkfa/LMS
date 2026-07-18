@extends('layouts.dashboard')
@section('title', 'Teaching — LMS')

@section('content')
<div class="space-y-6" x-data="{ showForm: {{ $errors->any() ? 'true' : 'false' }} }">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-3xl">Teaching</h1>
            <p class="mt-1 text-[var(--muted)]">Create and manage your courses.</p>
        </div>
        <button @click="showForm = !showForm" class="btn-primary" x-text="showForm ? 'Close' : '+ New Course'"></button>
    </div>

    @if (session('ok'))
        <p class="rounded-[var(--radius)] bg-[var(--success)]/10 px-4 py-3 text-sm font-bold text-[var(--success)]">{{ session('ok') }}</p>
    @endif

    <form x-show="showForm" x-cloak method="POST" action="/dashboard/teaching" class="card space-y-4 p-6">
        @csrf
        <h2 class="text-xl">New course</h2>
        <div>
            <label class="label">Title</label>
            <input name="title" class="input" value="{{ old('title') }}" required>
            @error('title') <p class="mt-1 text-xs font-bold text-[var(--danger)]">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="label">Subtitle</label>
            <input name="subtitle" class="input" value="{{ old('subtitle') }}">
        </div>
        <div class="grid gap-4 sm:grid-cols-3">
            <div>
                <label class="label">Category</label>
                <select name="category_id" class="input">
                    <option value="">None</option>
                    @foreach ($categories as $c)
                        <option value="{{ $c->id }}" @selected(old('category_id') == $c->id)>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="label">Level</label>
                <select name="level" class="input">
                    @foreach (['beginner', 'intermediate', 'advanced'] as $lvl)
                        <option value="{{ $lvl }}" @selected(old('level') === $lvl)>{{ ucfirst($lvl) }}</option>
                    @endforeach
                </select>
            </div>
            <div x-data="{ free: true }">
                <label class="label">Pricing</label>
                <div class="flex items-center gap-2">
                    <label class="flex items-center gap-1.5 text-sm font-bold">
                        <input type="checkbox" name="is_free" value="1" checked x-model="free" class="h-4 w-4 accent-[var(--primary)]"> Free
                    </label>
                    <input type="number" name="price" min="0" step="0.01" class="input" placeholder="$" x-show="!free" x-cloak>
                </div>
            </div>
        </div>
        <button type="submit" class="btn-primary">Create course</button>
    </form>

    @if ($courses->isEmpty())
        <div class="card grid place-items-center p-12 text-center">
            <span class="text-4xl">✏️</span>
            <p class="mt-3 font-bold">No courses yet — create your first above.</p>
        </div>
    @else
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($courses as $c)
                <a href="/dashboard/teaching/{{ $c->id }}" class="card block p-5 transition-transform hover:-translate-y-1">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg">{{ $c->title }}</h3>
                        <span class="rounded-full px-2.5 py-1 text-[11px] font-bold uppercase {{ $c->status === 'published' ? 'bg-[var(--success)]/15 text-[var(--success)]' : 'bg-[var(--warning)]/15 text-[var(--warning)]' }}">{{ $c->status }}</span>
                    </div>
                    <p class="mt-1 text-sm text-[var(--muted)]">{{ $c->subtitle }}</p>
                    <div class="mt-4 flex gap-4 border-t border-[var(--border)] pt-3 text-xs font-semibold text-[var(--muted)]">
                        <span>📚 {{ $c->lessons_count }} lessons</span>
                        <span>👥 {{ $c->enrollments_count }} enrolled</span>
                    </div>
                </a>
            @endforeach
        </div>
    @endif
</div>
@endsection
