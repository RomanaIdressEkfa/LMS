@extends('layouts.public')
@section('title', ($site['footer']['brand'] ?? 'LMS') . ' — Learn & teach without limits')

@section('content')
<div class="overflow-hidden">
    {{-- ===== Hero ===== --}}
    <section class="relative">
        <div class="blob grad-ph -left-32 -top-24 h-96 w-96"></div>
        <div class="blob grad-magenta right-0 top-10 h-80 w-80"></div>
        <div class="relative mx-auto grid max-w-[1600px] items-center gap-10 px-5 py-16 md:grid-cols-2 md:px-8 md:py-24">
            <div>
                <span class="pill grad-ph text-white shadow-md">⚡ <x-t k="ph.badge" /></span>
                <h1 class="mt-5 text-4xl leading-[1.12] md:text-5xl">
                    <x-t k="ph.h1a" /> <span class="hl hl-purple"><x-t k="ph.h1b" /></span> <x-t k="ph.h1c" />
                </h1>
                <p class="mt-5 max-w-md text-lg font-semibold text-[var(--muted)]"><x-t k="ph.heroSub" /></p>
                <div class="mt-8 flex flex-wrap gap-3">
                    <a href="/register" class="btn grad-magenta text-base text-white shadow-lg"><x-t k="ph.cta1" /> →</a>
                    <a href="/courses" class="btn-ghost text-base"><x-t k="ph.cta2" /></a>
                </div>
                <div class="mt-8 flex flex-wrap gap-2">
                    <span class="pill grad-ph text-white">🎬 <x-t k="ph.badge1" /></span>
                    <span class="pill bg-[var(--foreground)] text-white">🏆 <x-t k="ph.badge2" /></span>
                    <span class="pill grad-magenta text-white">📦 <x-t k="ph.badge3" /></span>
                </div>
            </div>

            <div class="relative hidden md:block">
                <div class="grad-ph absolute left-4 top-2 h-44 w-64 rotate-[-6deg] rounded-3xl opacity-90 shadow-[0_30px_60px_-20px_rgba(160,32,240,0.5)]"></div>
                <div class="grad-magenta absolute right-0 top-28 h-40 w-60 rotate-[6deg] rounded-3xl opacity-90 shadow-[0_30px_60px_-20px_rgba(214,31,154,0.5)]"></div>
                <div class="card relative z-10 ml-8 mt-16 w-72 p-6">
                    <div class="grad-magenta grid h-12 w-12 place-items-center rounded-2xl text-2xl">🎓</div>
                    <p class="mt-4 text-lg font-extrabold">Full-Stack Track</p>
                    <p class="text-sm font-semibold text-[var(--muted)]">42 lessons · 6 projects</p>
                    <div class="mt-4 h-2 rounded-full bg-[var(--border)]"><div class="grad-ph h-full w-3/4 rounded-full"></div></div>
                    <p class="mt-2 text-xs font-bold" style="color:#a020f0">75% complete</p>
                </div>
            </div>
        </div>
    </section>

    {{-- ===== Tech strip ===== --}}
    <section class="border-y border-[var(--border)] bg-[var(--surface)]">
        <div class="mx-auto max-w-[1600px] px-5 py-6 md:px-8">
            <p class="text-center text-xs font-bold uppercase tracking-wider text-[var(--muted)]"><x-t k="ph.techLabel" /></p>
            <div class="mt-4 flex flex-wrap items-center justify-center gap-x-8 gap-y-3">
                @foreach ($site['home']['techStrip'] as $x)
                    <span class="text-lg font-extrabold text-[var(--muted)]">{{ $x }}</span>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ===== Why learn with us ===== --}}
    @php
        $why = [
            ['🏭', 'ph.why1', 'ph.why1d', 'pastel-purple'],
            ['🎥', 'ph.why2', 'ph.why2d', 'pastel-blue'],
            ['🚀', 'ph.why3', 'ph.why3d', 'pastel-green'],
            ['💼', 'ph.why4', 'ph.why4d', 'pastel-pink'],
        ];
    @endphp
    <section class="mx-auto max-w-[1600px] px-5 py-16 md:px-8 md:py-20">
        <div class="text-center">
            <h2 class="text-3xl md:text-4xl"><x-t k="ph.whyTitle" /></h2>
            <p class="mx-auto mt-3 max-w-xl font-semibold text-[var(--muted)]"><x-t k="ph.whySub" /></p>
        </div>
        <div class="mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ($why as [$icon, $title, $text, $bg])
                <div class="rounded-[var(--radius)] p-7 {{ $bg }} transition-transform hover:-translate-y-1">
                    <div class="grid h-14 w-14 place-items-center rounded-2xl bg-white text-2xl shadow-sm">{{ $icon }}</div>
                    <h3 class="mt-5 text-lg"><x-t :k="$title" /></h3>
                    <p class="mt-2 text-sm font-semibold text-[var(--muted)]"><x-t :k="$text" /></p>
                </div>
            @endforeach
        </div>
    </section>

    {{-- ===== Stats band ===== --}}
    <section class="mx-auto max-w-[1600px] px-5 pb-4 md:px-8">
        <div class="grad-ph relative overflow-hidden rounded-[2rem] px-6 py-12 md:py-14">
            <div class="blob grad-magenta -right-16 -top-16 h-64 w-64"></div>
            <h2 class="relative text-center text-2xl text-white md:text-3xl"><x-t k="ph.statsTitle" /></h2>
            <div class="relative mt-8 grid grid-cols-2 gap-6 text-center text-white md:grid-cols-4">
                @foreach ($site['home']['stats'] as $s)
                    <div>
                        <p class="text-4xl md:text-5xl">{{ $s['v'] }}</p>
                        <p class="mt-1 text-sm font-bold text-white/85"><x-loc :value="$s['l']" /></p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ===== Tech superpower grid ===== --}}
    <section class="mx-auto max-w-[1600px] px-5 py-16 md:px-8 md:py-20">
        <div class="text-center">
            <h2 class="text-3xl md:text-4xl"><x-t k="ph.techTitle" /></h2>
            <p class="mx-auto mt-3 max-w-xl font-semibold text-[var(--muted)]"><x-t k="ph.techSub" /></p>
        </div>
        <div class="mt-12 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ($site['home']['stack'] as $s)
                <div class="rounded-[var(--radius)] border border-[var(--border)] p-6 {{ $s['bg'] }}">
                    <div class="grid h-12 w-12 place-items-center rounded-2xl bg-white text-xl shadow-sm">{{ $s['icon'] }}</div>
                    <h3 class="mt-4 text-lg">{{ $s['name'] }}</h3>
                    <p class="mt-1.5 text-sm font-semibold text-[var(--muted)]"><x-loc :value="$s['d']" /></p>
                </div>
            @endforeach
        </div>
    </section>

    {{-- ===== Featured courses ===== --}}
    @if ($featured->count())
        <section class="bg-[var(--surface)]">
            <div class="mx-auto max-w-[1600px] px-5 py-16 md:px-8 md:py-20">
                <div class="flex flex-wrap items-end justify-between gap-3">
                    <h2 class="text-3xl md:text-4xl"><x-t k="home.featured" /></h2>
                    <a href="/courses" class="btn-ghost"><x-t k="home.seeAll" /></a>
                </div>
                <div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($featured as $c)
                        <a href="/courses/{{ $c->slug }}" class="card group flex flex-col overflow-hidden transition-transform hover:-translate-y-1">
                            <div class="flex h-40 items-center justify-center text-white" style="background: linear-gradient(135deg, {{ optional($c->category)->color ?? '#2563ff' }}, #a020f0)">
                                <span class="text-5xl">🎓</span>
                            </div>
                            <div class="flex flex-1 flex-col p-6">
                                @if ($c->category)
                                    <span class="text-xs font-bold uppercase" style="color: {{ $c->category->color }}">{{ $c->category->name }}</span>
                                @endif
                                <h3 class="mt-1 text-lg">{{ $c->title }}</h3>
                                <p class="mt-1 flex-1 text-sm font-semibold text-[var(--muted)]">{{ $c->subtitle }}</p>
                                <div class="mt-4 flex items-center justify-between border-t border-[var(--border)] pt-3">
                                    <span class="text-sm font-bold">{{ optional($c->teacher)->name }}</span>
                                    <span class="text-sm font-extrabold {{ $c->is_free ? 'text-[var(--success)]' : '' }}" @if (!$c->is_free) style="color:#a020f0" @endif>
                                        {{ $c->is_free ? 'FREE' : '$' . number_format((float) $c->price, 2) }}
                                    </span>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- ===== Success stories ===== --}}
    <section class="mx-auto max-w-[1600px] px-5 py-16 md:px-8 md:py-20">
        <h2 class="text-center text-3xl md:text-4xl"><x-t k="ph.storiesTitle" /></h2>
        <div class="mt-12 grid gap-6 md:grid-cols-3">
            @foreach ($site['home']['stories'] as $s)
                <div class="card p-7">
                    <div class="flex text-lg" style="color:#f59e0b">★★★★★</div>
                    <p class="mt-4 font-semibold text-[var(--foreground)]">“<x-loc :value="$s['text']" />”</p>
                    <div class="mt-5 flex items-center gap-3">
                        <div class="grid h-11 w-11 place-items-center rounded-full text-sm font-extrabold text-white {{ $s['grad'] }}">
                            {{ collect(explode(' ', $s['name']))->map(fn ($n) => $n[0] ?? '')->join('') }}
                        </div>
                        <div>
                            <p class="text-sm font-extrabold">{{ $s['name'] }}</p>
                            <p class="text-xs font-semibold text-[var(--muted)]"><x-loc :value="$s['role']" /></p>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    {{-- ===== Support ===== --}}
    @php
        $support = [
            ['💬', 'ph.sup1', 'ph.sup1d'], ['🔁', 'ph.sup2', 'ph.sup2d'], ['🧠', 'ph.sup3', 'ph.sup3d'],
            ['👤', 'ph.sup4', 'ph.sup4d'], ['📚', 'ph.sup5', 'ph.sup5d'], ['🎯', 'ph.sup6', 'ph.sup6d'],
        ];
    @endphp
    <section class="bg-[var(--surface)]">
        <div class="mx-auto max-w-[1600px] px-5 py-16 md:px-8 md:py-20">
            <h2 class="text-center text-3xl md:text-4xl"><x-t k="ph.supportTitle" /></h2>
            <div class="mt-12 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($support as [$icon, $title, $text])
                    <div class="card flex items-start gap-4 p-6">
                        <div class="grad-ph grid h-12 w-12 shrink-0 place-items-center rounded-2xl text-xl text-white">{{ $icon }}</div>
                        <div>
                            <h3 class="text-lg"><x-t :k="$title" /></h3>
                            <p class="mt-1 text-sm font-semibold text-[var(--muted)]"><x-t :k="$text" /></p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ===== FAQ ===== --}}
    @php $faq = [['ph.faqQ1', 'ph.faqA1'], ['ph.faqQ2', 'ph.faqA2'], ['ph.faqQ3', 'ph.faqA3'], ['ph.faqQ4', 'ph.faqA4']]; @endphp
    <section class="mx-auto max-w-3xl px-5 py-16 md:px-8 md:py-20">
        <h2 class="text-center text-3xl md:text-4xl"><x-t k="ph.faqTitle" /></h2>
        <div class="mt-10 space-y-3">
            @foreach ($faq as $i => [$q, $a])
                <details class="faq" @if ($i === 0) open @endif>
                    <summary><x-t :k="$q" /></summary>
                    <div><x-t :k="$a" /></div>
                </details>
            @endforeach
        </div>
    </section>

    {{-- ===== Final CTA ===== --}}
    <section class="mx-auto max-w-[1600px] px-5 pb-20 md:px-8">
        <div class="grad-magenta relative overflow-hidden rounded-[2rem] px-8 py-16 text-center text-white md:py-20">
            <div class="blob grad-ph -left-16 -bottom-16 h-64 w-64"></div>
            <div class="relative">
                <h2 class="text-3xl md:text-4xl"><x-t k="ph.finalTitle" /></h2>
                <p class="mx-auto mt-3 max-w-lg font-semibold text-white/85"><x-t k="ph.finalSub" /></p>
                <div class="mt-8 flex flex-wrap justify-center gap-3">
                    <a href="/register" class="rounded-xl bg-white px-6 py-3 font-bold text-[#a020f0] transition-transform hover:-translate-y-0.5"><x-t k="cta.createFree" /></a>
                    <a href="/login" class="rounded-xl border border-white/40 px-6 py-3 font-bold text-white hover:bg-white/10"><x-t k="cta.login" /></a>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
