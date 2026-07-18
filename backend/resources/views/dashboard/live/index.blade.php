@extends('layouts.dashboard')
@section('title', 'Live Classes — LMS')

@php
    $providers = ['custom' => 'Custom link', 'zoom' => 'Zoom', 'meet' => 'Google Meet', 'agora' => 'Agora'];
    $scheduledValue = old('scheduled_at', $editing && $editing->scheduled_at ? $editing->scheduled_at->format('Y-m-d\TH:i') : '');
@endphp

@section('content')
<div class="space-y-8" x-data="{ showForm: {{ $editing || $errors->any() ? 'true' : 'false' }} }">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-3xl">Live Classes</h1>
            <p class="mt-1 text-[var(--muted)]">Join live sessions or see what's coming up.</p>
        </div>
        @can('live.host')
            <button @click="showForm = !showForm" class="btn-primary" x-text="showForm ? 'Close' : '+ Schedule session'"></button>
        @endcan
    </div>

    @if (session('ok'))
        <p class="rounded-[var(--radius)] bg-[var(--success)]/10 px-4 py-3 text-sm font-bold text-[var(--success)]">{{ session('ok') }}</p>
    @endif

    @can('live.host')
        {{-- Schedule / edit form --}}
        <form x-show="showForm" x-cloak method="POST"
            action="/dashboard/live{{ $editing ? '/' . $editing->id : '' }}"
            class="card space-y-4 p-6">
            @csrf
            @if ($editing) @method('PUT') @endif
            <div class="flex items-center justify-between">
                <h2 class="text-xl">{{ $editing ? 'Edit session' : 'Schedule a session' }}</h2>
                @if ($editing)
                    <a href="/dashboard/live" class="text-sm font-bold text-[var(--primary)] hover:underline">Cancel edit</a>
                @endif
            </div>
            <div>
                <label class="label">Title</label>
                <input name="title" class="input" value="{{ old('title', $editing->title ?? '') }}" required>
                @error('title') <p class="mt-1 text-xs font-bold text-[var(--danger)]">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="label">Description</label>
                <textarea name="description" class="input min-h-20" placeholder="What's this session about?">{{ old('description', $editing->description ?? '') }}</textarea>
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="label">Course (optional)</label>
                    <select name="course_id" class="input">
                        <option value="">Public (no course)</option>
                        @foreach ($courses as $c)
                            <option value="{{ $c->id }}" @selected(old('course_id', $editing->course_id ?? null) == $c->id)>{{ $c->title }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="label">Provider</label>
                    <select name="provider" class="input">
                        @foreach ($providers as $val => $label)
                            <option value="{{ $val }}" @selected(old('provider', $editing->provider ?? 'custom') === $val)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div>
                <label class="label">Meeting link</label>
                <input name="meeting_url" class="input" placeholder="https://…" value="{{ old('meeting_url', $editing->meeting_url ?? '') }}">
                <p class="mt-1 text-xs text-[var(--muted)]">Shown to attendees only once the session goes live.</p>
                @error('meeting_url') <p class="mt-1 text-xs font-bold text-[var(--danger)]">{{ $message }}</p> @enderror
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="label">Starts at</label>
                    <input type="datetime-local" name="scheduled_at" class="input" value="{{ $scheduledValue }}" required>
                    @error('scheduled_at') <p class="mt-1 text-xs font-bold text-[var(--danger)]">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="label">Duration (minutes)</label>
                    <input type="number" name="duration_minutes" min="5" class="input" value="{{ old('duration_minutes', $editing->duration_minutes ?? 60) }}">
                </div>
            </div>
            <button type="submit" class="btn-primary">{{ $editing ? 'Save session' : 'Schedule session' }}</button>
        </form>

        {{-- Host's own sessions --}}
        @if ($mine->isNotEmpty())
            <div>
                <h2 class="text-xl">My sessions</h2>
                <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($mine as $s)
                        @php $status = $s->status; @endphp
                        <div class="card flex flex-col p-5">
                            <div class="flex items-center justify-between gap-2">
                                <h3 class="text-lg">{{ $s->title }}</h3>
                                @if ($status === 'live')
                                    <span class="pill bg-[var(--danger)] text-white">● Live</span>
                                @elseif ($status === 'ended')
                                    <span class="pill bg-[var(--border)] text-[var(--muted)]">ended</span>
                                @else
                                    <span class="pill bg-[var(--warning)]/15 text-[var(--warning)]">scheduled</span>
                                @endif
                            </div>
                            @if ($s->course)<p class="mt-1 text-xs font-bold uppercase text-[var(--primary)]">{{ $s->course->title }}</p>@endif
                            <div class="mt-2 flex-1 text-xs font-semibold text-[var(--muted)]">
                                🕒 {{ optional($s->scheduled_at)->format('M j, Y · g:i A') }} · {{ $s->duration_minutes }}m · {{ ucfirst($s->provider) }}
                            </div>
                            <div class="mt-4 flex flex-wrap items-center gap-2">
                                @if ($status === 'scheduled')
                                    <form method="POST" action="/dashboard/live/{{ $s->id }}/status">
                                        @csrf <input type="hidden" name="status" value="live">
                                        <button class="btn-primary !py-1.5 !px-3 text-xs">Start</button>
                                    </form>
                                @elseif ($status === 'live')
                                    <form method="POST" action="/dashboard/live/{{ $s->id }}/status">
                                        @csrf <input type="hidden" name="status" value="ended">
                                        <button class="btn-primary !py-1.5 !px-3 text-xs">End</button>
                                    </form>
                                @else
                                    <form method="POST" action="/dashboard/live/{{ $s->id }}/status">
                                        @csrf <input type="hidden" name="status" value="scheduled">
                                        <button class="btn-ghost !py-1.5 !px-3 text-xs">Reopen</button>
                                    </form>
                                @endif
                                <a href="/dashboard/live?edit={{ $s->id }}" class="text-sm font-bold text-[var(--primary)] hover:underline">Edit</a>
                                <form method="POST" action="/dashboard/live/{{ $s->id }}" onsubmit="return confirm('Delete this session?')">
                                    @csrf @method('DELETE')
                                    <button class="text-sm font-bold text-[var(--danger)] hover:underline">Delete</button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    @endcan

    {{-- Upcoming sessions (everyone) --}}
    <div>
        @can('live.host')<h2 class="text-xl">Upcoming</h2>@endcan
        @if ($sessions->isEmpty())
            <div class="card mt-4 grid place-items-center p-12 text-center">
                <span class="text-4xl">🎥</span>
                <p class="mt-3 font-bold">No upcoming live sessions.</p>
            </div>
        @else
            <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($sessions as $s)
                    @php $isLive = $s->status === 'live'; $isHost = $s->teacher_id === auth()->id(); @endphp
                    <div class="card flex flex-col p-5">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg">{{ $s->title }}</h3>
                            @if ($isLive)
                                <span class="pill bg-[var(--danger)] text-white">● Live</span>
                            @else
                                <span class="pill bg-[var(--border)] text-[var(--muted)]">{{ $s->status }}</span>
                            @endif
                        </div>
                        @if ($s->course)<p class="mt-1 text-xs font-bold uppercase text-[var(--primary)]">{{ $s->course->title }}</p>@endif
                        <p class="mt-1 flex-1 text-sm text-[var(--muted)]">{{ $s->description }}</p>
                        <div class="mt-3 text-xs font-semibold text-[var(--muted)]">
                            🕒 {{ optional($s->scheduled_at)->format('M j, Y · g:i A') }} · {{ $s->duration_minutes }}m · {{ optional($s->teacher)->name }}
                        </div>
                        @if (($isLive || $isHost) && $s->meeting_url)
                            <a href="{{ $s->meeting_url }}" target="_blank" rel="noopener" class="btn-primary mt-4">Join now →</a>
                        @else
                            <p class="mt-4 text-center text-xs font-semibold text-[var(--muted)]">Link opens when the session goes live</p>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection
