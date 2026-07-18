@extends('layouts.public')
@section('title', 'Pricing — ' . ($site['footer']['brand'] ?? 'LMS'))

@section('content')
@php
    $pricing = $site['pricing'];
    $moduleLabels = [
        'courses' => 'Courses', 'roles' => 'Roles & Permissions', 'quizzes' => 'Quizzes',
        'certificates' => 'Certificates', 'live_classes' => 'Live Classes', 'store' => 'Store',
        'wallet' => 'Wallet', 'forums' => 'Forums', 'events' => 'Events', 'jobs' => 'Jobs Board',
        'blog' => 'Blog', 'affiliates' => 'Affiliates',
    ];
    $cardGrad = ['grad-primary', 'grad-purple', 'grad-sunset'];
@endphp
<div>
    <section class="grad-hero border-b border-[var(--border)]">
        <div class="mx-auto max-w-[1600px] px-5 py-14 text-center md:px-8">
            <h1 class="text-4xl md:text-5xl"><x-loc :value="$pricing['hero']['titleA']" /> <span class="gradient-text"><x-loc :value="$pricing['hero']['titleHl']" /></span></h1>
            <p class="mx-auto mt-3 max-w-xl text-lg font-semibold text-[var(--muted)]"><x-loc :value="$pricing['hero']['subtitle']" /></p>
        </div>
    </section>

    <div class="mx-auto max-w-6xl px-5 py-14 md:px-8">
        @if ($plans->isEmpty())
            <p class="text-center text-[var(--muted)]">No plans available yet.</p>
        @else
            <div class="grid gap-6 md:grid-cols-3">
                @foreach ($plans as $i => $p)
                    @php $featured = $i === 1; @endphp
                    <div class="card relative flex flex-col p-8 {{ $featured ? 'ring-2 ring-[var(--primary)]' : '' }}">
                        @if ($featured)
                            <span class="pill grad-primary absolute -top-3 left-1/2 -translate-x-1/2 text-white">Most popular</span>
                        @endif
                        <div class="grid h-12 w-12 place-items-center rounded-2xl text-xl text-white {{ $cardGrad[$i % 3] }}">★</div>
                        <h3 class="mt-5 text-2xl">{{ $p->name }}</h3>
                        <p class="mt-1 text-sm font-semibold text-[var(--muted)]">{{ $p->description }}</p>
                        <p class="mt-5 text-4xl">
                            ${{ number_format((float) $p->price, 0) }}
                            <span class="text-base font-bold text-[var(--muted)]">/{{ $p->interval === 'monthly' ? 'mo' : ($p->interval === 'yearly' ? 'yr' : 'once') }}</span>
                        </p>
                        <a href="/register" class="mt-6 {{ $featured ? 'btn-primary' : 'btn-ghost' }} w-full">Get started</a>
                        <ul class="mt-6 space-y-2.5 border-t border-[var(--border)] pt-6">
                            @foreach (($p->module_keys ?? []) as $k)
                                <li class="flex items-center gap-2.5 text-sm font-semibold">
                                    <span class="grid h-5 w-5 place-items-center rounded-full bg-[var(--success)]/15 text-xs text-[var(--success)]">✓</span>
                                    {{ $moduleLabels[$k] ?? $k }}
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endforeach
            </div>
        @endif
        <p class="mt-10 text-center text-sm font-semibold text-[var(--muted)]">
            <x-loc :value="$pricing['footnote']" />
            <a href="/contact" class="font-bold text-[var(--primary)] hover:underline">Contact us</a>.
        </p>
    </div>
</div>
@endsection
