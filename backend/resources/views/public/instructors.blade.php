@extends('layouts.public')
@section('title', 'Instructors — ' . ($site['footer']['brand'] ?? 'LMS'))

@section('content')
@php
    $c = $site['instructors'];
    $grads = ['grad-primary', 'grad-purple', 'grad-sunset', 'grad-teal'];
@endphp
<div>
    <section class="grad-hero border-b border-[var(--border)]">
        <div class="mx-auto max-w-[1600px] px-5 py-14 md:px-8">
            <h1 class="text-4xl md:text-5xl"><x-loc :value="$c['hero']['titleA']" /> <span class="gradient-text"><x-loc :value="$c['hero']['titleHl']" /></span></h1>
            <p class="mt-3 max-w-xl text-lg font-semibold text-[var(--muted)]"><x-loc :value="$c['hero']['subtitle']" /></p>
        </div>
    </section>

    <div class="mx-auto max-w-[1600px] px-5 py-12 md:px-8">
        @if ($instructors->isEmpty())
            <div class="card grid place-items-center p-12 text-center">
                <span class="text-4xl">🧑‍🏫</span>
                <p class="mt-3 font-bold">No instructors yet.</p>
            </div>
        @else
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                @foreach ($instructors as $i => $t)
                    <div class="card p-6 text-center transition-transform hover:-translate-y-1">
                        <div class="mx-auto grid h-20 w-20 place-items-center rounded-full text-2xl font-extrabold text-white {{ $grads[$i % count($grads)] }}">
                            {{ collect(explode(' ', $t->name))->map(fn ($n) => $n[0] ?? '')->take(2)->join('') }}
                        </div>
                        <h3 class="mt-4 text-lg">{{ $t->name }}</h3>
                        <p class="mt-1 text-sm font-semibold text-[var(--muted)]">{{ $t->bio ?? 'Instructor' }}</p>
                        <span class="pill mt-4 bg-[var(--primary-soft)] text-[var(--primary)]">{{ $t->courses_count }} courses</span>
                    </div>
                @endforeach
            </div>
        @endif

        <div class="mt-12 card grad-purple flex flex-col items-center gap-4 p-10 text-center text-white sm:flex-row sm:justify-between sm:text-left">
            <div>
                <h2 class="text-2xl"><x-loc :value="$c['cta']['title']" /></h2>
                <p class="mt-1 font-semibold text-white/85"><x-loc :value="$c['cta']['text']" /></p>
            </div>
            <a href="/register" class="rounded-xl bg-white px-6 py-3 font-bold text-[var(--purple)]"><x-loc :value="$c['cta']['button']" /></a>
        </div>
    </div>
</div>
@endsection
