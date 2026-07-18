@extends('layouts.dashboard')
@section('title', 'Certificate — LMS')

@section('content')
<style>@media print {
    body * { visibility: hidden !important; }
    #certificate, #certificate * { visibility: visible !important; }
    #certificate { position: absolute; inset: 0; margin: 0 !important; box-shadow: none !important; }
    @page { size: landscape; margin: 12mm; }
}</style>

<div class="space-y-5">
    <div class="no-print flex items-center justify-between">
        <a href="/dashboard/learn" class="text-sm font-bold text-[var(--primary)] hover:underline">← My Learning</a>
        <button onclick="window.print()" class="btn-primary">🖨️ Print / Download PDF</button>
    </div>

    <div id="certificate" class="relative mx-auto max-w-3xl overflow-hidden rounded-[var(--radius)] bg-white p-1 shadow-[var(--shadow-card)]">
        <div class="grad-primary p-1">
            <div class="relative bg-white px-8 py-12 text-center sm:px-14">
                <div class="pointer-events-none absolute left-0 top-0 h-20 w-20 border-l-4 border-t-4 border-[var(--primary)]/40"></div>
                <div class="pointer-events-none absolute bottom-0 right-0 h-20 w-20 border-b-4 border-r-4 border-[var(--primary)]/40"></div>

                <div class="flex items-center justify-center gap-2 text-[var(--primary)]">
                    <span class="grid h-9 w-9 place-items-center rounded-xl bg-[var(--primary)] text-lg text-white">✦</span>
                    <span class="text-xl font-extrabold tracking-tight text-[var(--foreground)]">LMS</span>
                </div>

                <p class="mt-8 text-xs font-bold uppercase tracking-[0.3em] text-[var(--muted)]">Certificate of Completion</p>
                <p class="mt-6 text-sm text-[var(--muted)]">This certifies that</p>
                <h1 class="mt-2 text-4xl font-extrabold text-[var(--foreground)]">{{ $studentName }}</h1>

                <p class="mt-6 text-sm text-[var(--muted)]">has successfully completed</p>
                <h2 class="mt-2 text-2xl font-extrabold text-[var(--primary)]">{{ $courseTitle }}</h2>
                <p class="mt-2 text-sm text-[var(--muted)]">{{ $lessonsCount }} lesson{{ $lessonsCount === 1 ? '' : 's' }} · Completed {{ $completedDate }}</p>

                <div class="mt-10 flex items-end justify-between gap-6 text-left">
                    <div>
                        <p class="border-t border-[var(--border)] pt-2 text-sm font-bold text-[var(--foreground)]">{{ $instructorName ?? '—' }}</p>
                        <p class="text-xs text-[var(--muted)]">Instructor</p>
                    </div>
                    <div class="text-right">
                        <p class="border-t border-[var(--border)] pt-2 font-mono text-sm font-bold text-[var(--foreground)]">{{ $serial }}</p>
                        <p class="text-xs text-[var(--muted)]">Certificate ID</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
