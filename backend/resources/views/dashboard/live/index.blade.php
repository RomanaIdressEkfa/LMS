@extends('layouts.dashboard')
@section('title', 'Live Classes — LMS')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-3xl">Live Classes</h1>
        <p class="mt-1 text-[var(--muted)]">Join live sessions or see what's coming up.</p>
    </div>

    @if ($sessions->isEmpty())
        <div class="card grid place-items-center p-12 text-center">
            <span class="text-4xl">🎥</span>
            <p class="mt-3 font-bold">No upcoming live sessions.</p>
        </div>
    @else
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
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
@endsection
