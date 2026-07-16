@extends('layouts.dashboard')
@section('title', 'Modules — LMS')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-3xl">Modules / Addons</h1>
        <p class="mt-1 text-[var(--muted)]">Turn platform features on or off.</p>
    </div>

    @include('dashboard.admin._flash')

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @foreach ($modules as $m)
            <div class="card flex flex-col p-5">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg">{{ $m->name }}</h3>
                    @if ($m->is_core)
                        <span class="pill bg-[var(--border)] text-[var(--muted)]">core</span>
                    @else
                        <span class="pill {{ $m->enabled ? 'bg-[var(--success)]/15 text-[var(--success)]' : 'bg-[var(--border)] text-[var(--muted)]' }}">{{ $m->enabled ? 'on' : 'off' }}</span>
                    @endif
                </div>
                <p class="mt-1 flex-1 text-sm text-[var(--muted)]">{{ $m->description }}</p>
                <div class="mt-4">
                    @if ($m->is_core)
                        <button disabled class="btn-ghost w-full opacity-50">Always on</button>
                    @else
                        <form method="POST" action="/dashboard/modules/{{ $m->id }}/toggle">
                            @csrf
                            <button class="{{ $m->enabled ? 'btn-ghost' : 'btn-primary' }} w-full">{{ $m->enabled ? 'Disable' : 'Enable' }}</button>
                        </form>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection
