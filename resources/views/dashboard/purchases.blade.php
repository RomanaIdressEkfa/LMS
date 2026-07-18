@extends('layouts.dashboard')
@section('title', 'My Purchases — LMS')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-3xl">My Purchases</h1>
        <p class="mt-1 text-[var(--muted)]">Your enrollment history and receipts.</p>
    </div>

    @if ($enrollments->isEmpty())
        <div class="card grid place-items-center p-12 text-center">
            <span class="text-4xl">🧾</span>
            <p class="mt-3 font-bold">No purchases yet.</p>
            <a href="/dashboard/courses" class="btn-primary mt-4">Browse courses</a>
        </div>
    @else
        <div class="card overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="border-b border-[var(--border)] text-xs font-bold uppercase tracking-wide text-[var(--muted)]">
                    <tr>
                        <th class="p-4">Course</th>
                        <th class="p-4">Source</th>
                        <th class="p-4">Amount</th>
                        <th class="p-4">Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[var(--border)]">
                    @foreach ($enrollments as $e)
                        <tr>
                            <td class="p-4 font-bold text-[var(--foreground)]">
                                <a href="/dashboard/learn/{{ $e->course->slug }}" class="hover:text-[var(--primary)]">{{ $e->course->title }}</a>
                            </td>
                            <td class="p-4 capitalize text-[var(--muted)]">{{ $e->source ?? 'free' }}</td>
                            <td class="p-4 font-bold">{{ (float) $e->amount_paid > 0 ? '$' . number_format((float) $e->amount_paid, 2) : 'Free' }}</td>
                            <td class="p-4 text-[var(--muted)]">{{ $e->created_at->format('M j, Y') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection
