@extends('layouts.dashboard')
@section('title', 'Dashboard — LMS')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-3xl"><x-t k="dash.hello" />, {{ $user->name }} 👋</h1>
        <p class="mt-1 text-[var(--muted)]"><x-t k="dash.welcome" /></p>
    </div>

    {{-- Stat cards --}}
    @php
        $stats = [
            ['📚', $enrolledCount, 'dash.stat.courses', 'grad-primary'],
            ['🎓', $completedCount, 'dash.stat.certs', 'grad-purple'],
        ];
        if ($user->can('courses.create')) {
            $stats[] = ['✏️', $teachingCount, 'dash.stat.teaching', 'grad-sunset'];
        }
    @endphp
    <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
        @foreach ($stats as [$icon, $value, $label, $grad])
            <div class="card flex items-center gap-4 p-6">
                <div class="{{ $grad }} grid h-14 w-14 shrink-0 place-items-center rounded-2xl text-2xl text-white">{{ $icon }}</div>
                <div>
                    <p class="text-3xl font-extrabold">{{ $value }}</p>
                    <p class="text-sm font-bold text-[var(--muted)]"><x-t :k="$label" /></p>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Access / permissions --}}
    <div class="card p-6 md:p-8">
        <h2 class="text-xl"><x-t k="dash.access" /></h2>
        <p class="mt-1 text-sm font-semibold text-[var(--muted)]"><x-t k="dash.accessSub" /></p>
        <div class="mt-5 flex flex-wrap gap-2">
            @forelse ($permissions as $p)
                <span class="rounded-full border border-[var(--border)] bg-[var(--background)] px-3 py-1 text-xs font-bold text-[var(--foreground)]">{{ $p }}</span>
            @empty
                <span class="text-sm text-[var(--muted)]">No special permissions.</span>
            @endforelse
        </div>
    </div>
</div>
@endsection
