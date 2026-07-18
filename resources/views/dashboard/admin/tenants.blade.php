@extends('layouts.dashboard')
@section('title', 'Tenants — LMS')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-3xl">Tenants</h1>
        <p class="mt-1 text-[var(--muted)]">Organizations using the platform.</p>
    </div>

    @if ($tenants->isEmpty())
        <div class="card grid place-items-center p-12 text-center">
            <span class="text-4xl">🏢</span>
            <p class="mt-3 font-bold">No tenants yet.</p>
        </div>
    @else
        <div class="card overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="border-b border-[var(--border)] text-xs font-bold uppercase tracking-wide text-[var(--muted)]">
                    <tr><th class="p-4">Tenant</th><th class="p-4">Plan</th><th class="p-4">Created</th></tr>
                </thead>
                <tbody class="divide-y divide-[var(--border)]">
                    @foreach ($tenants as $t)
                        <tr>
                            <td class="p-4 font-bold text-[var(--foreground)]">{{ $t->name }}</td>
                            <td class="p-4 text-[var(--muted)]">{{ optional($t->plan)->name ?? '—' }}</td>
                            <td class="p-4 text-[var(--muted)]">{{ $t->created_at->format('M j, Y') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection
