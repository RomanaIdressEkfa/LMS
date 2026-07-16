@extends('layouts.dashboard')
@section('title', 'Plans — LMS')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-3xl">Plans</h1>
        <p class="mt-1 text-[var(--muted)]">Subscription plans offered to tenants.</p>
    </div>

    @include('dashboard.admin._flash')

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @foreach ($plans as $p)
            <div class="card flex flex-col p-6">
                <div class="flex items-center justify-between">
                    <h3 class="text-xl">{{ $p->name }}</h3>
                    <span class="pill bg-[var(--primary-soft)] text-[var(--primary)]">{{ $p->tenants_count }} tenants</span>
                </div>
                <p class="mt-2 text-3xl">${{ number_format((float) $p->price, 0) }}<span class="text-base font-bold text-[var(--muted)]">/{{ $p->interval }}</span></p>
                <p class="mt-1 flex-1 text-sm text-[var(--muted)]">{{ $p->description }}</p>
                <div class="mt-3 flex flex-wrap gap-1">
                    @foreach (($p->module_keys ?? []) as $k)
                        <span class="rounded-full border border-[var(--border)] px-2 py-0.5 text-[10px] font-bold text-[var(--muted)]">{{ $k }}</span>
                    @endforeach
                </div>
                @if ($p->tenants_count === 0)
                    <form method="POST" action="/dashboard/platform/plans/{{ $p->id }}" class="mt-4" onsubmit="return confirm('Delete this plan?')">
                        @csrf @method('DELETE')
                        <button class="text-sm font-bold text-[var(--danger)] hover:underline">Delete plan</button>
                    </form>
                @endif
            </div>
        @endforeach
    </div>
</div>
@endsection
