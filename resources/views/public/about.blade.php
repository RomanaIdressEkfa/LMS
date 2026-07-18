@extends('layouts.public')
@section('title', 'About — ' . ($site['footer']['brand'] ?? 'LMS'))

@section('content')
@php $about = $site['about']; @endphp
<div>
    <section class="grad-hero border-b border-[var(--border)]">
        <div class="mx-auto max-w-4xl px-5 py-16 text-center md:px-8 md:py-20">
            <h1 class="text-4xl md:text-5xl"><x-loc :value="$about['hero']['titleA']" /> <span class="gradient-text"><x-loc :value="$about['hero']['titleHl']" /></span></h1>
            <p class="mx-auto mt-4 max-w-2xl text-lg font-semibold text-[var(--muted)]"><x-loc :value="$about['hero']['subtitle']" /></p>
            <div class="mt-8 flex flex-wrap justify-center gap-3">
                <a href="/register" class="btn-primary">Get started free</a>
                <a href="/courses" class="btn-ghost">Browse courses</a>
            </div>
        </div>
    </section>

    <section class="mx-auto max-w-[1600px] px-5 py-16 md:px-8">
        <h2 class="text-center text-3xl md:text-4xl">What we stand for</h2>
        <div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ($about['values'] as $v)
                <div class="card p-7">
                    <div class="grid h-14 w-14 place-items-center rounded-2xl text-2xl text-white {{ $v['grad'] }}">{{ $v['icon'] }}</div>
                    <h3 class="mt-5 text-xl"><x-loc :value="$v['title']" /></h3>
                    <p class="mt-2 font-semibold text-[var(--muted)]"><x-loc :value="$v['text']" /></p>
                </div>
            @endforeach
        </div>
    </section>

    <section class="bg-[var(--surface)]">
        <div class="mx-auto max-w-[1600px] px-5 py-16 md:px-8">
            <h2 class="text-center text-3xl md:text-4xl">How it works</h2>
            <div class="mt-10 grid gap-6 md:grid-cols-3">
                @foreach ($about['steps'] as $s)
                    <div class="card p-8">
                        <div class="grad-primary grid h-12 w-12 place-items-center rounded-2xl text-xl font-extrabold text-white">{{ $s['n'] }}</div>
                        <h3 class="mt-5 text-xl"><x-loc :value="$s['title']" /></h3>
                        <p class="mt-2 font-semibold text-[var(--muted)]"><x-loc :value="$s['text']" /></p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="mx-auto max-w-[1600px] px-5 py-16 md:px-8">
        <div class="grad-primary grid grid-cols-2 gap-6 rounded-[2rem] p-10 text-center text-white md:grid-cols-4">
            @foreach ($about['stats'] as $s)
                <div>
                    <p class="text-3xl md:text-4xl">{{ $s['v'] }}</p>
                    <p class="mt-1 text-sm font-bold text-white/80"><x-loc :value="$s['l']" /></p>
                </div>
            @endforeach
        </div>
    </section>
</div>
@endsection
