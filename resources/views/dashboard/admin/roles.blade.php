@extends('layouts.dashboard')
@section('title', 'Roles & Permissions — LMS')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-3xl">Roles & Permissions</h1>
        <p class="mt-1 text-[var(--muted)]">What each role can do. Every page and menu adapts to these.</p>
    </div>

    <div class="grid gap-4 lg:grid-cols-2">
        @foreach ($roles as $r)
            <div class="card p-6">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg capitalize">{{ str_replace('-', ' ', $r['name']) }}</h3>
                    <div class="flex items-center gap-2">
                        @if ($r['protected'])<span class="pill bg-[var(--border)] text-[var(--muted)]">protected</span>@endif
                        <span class="pill bg-[var(--primary-soft)] text-[var(--primary)]">{{ $r['users_count'] }} users</span>
                    </div>
                </div>
                <div class="mt-4 flex flex-wrap gap-1.5">
                    @forelse ($r['permissions'] as $p)
                        <span class="rounded-full border border-[var(--border)] bg-[var(--background)] px-2.5 py-0.5 text-[11px] font-bold text-[var(--foreground)]">{{ $p }}</span>
                    @empty
                        <span class="text-sm text-[var(--muted)]">No permissions.</span>
                    @endforelse
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection
