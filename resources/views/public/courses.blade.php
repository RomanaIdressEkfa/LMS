@extends('layouts.public')
@section('title', 'Courses — ' . ($site['footer']['brand'] ?? 'LMS'))

@section('content')
<div x-data="{ search: '', category: '{{ request('category', '') }}', price: '' }">
    <section class="grad-hero border-b border-[var(--border)]">
        <div class="mx-auto max-w-[1600px] px-5 py-14 md:px-8">
            <h1 class="text-4xl md:text-5xl">Explore <span class="gradient-text">courses</span></h1>
            <p class="mt-3 max-w-xl text-lg font-semibold text-[var(--muted)]">Learn from real instructors. Free courses start instantly.</p>
        </div>
    </section>

    <div class="mx-auto max-w-[1600px] px-5 py-10 md:px-8">
        {{-- Filters (instant, client-side via Alpine) --}}
        <div class="card flex flex-wrap items-center gap-3 p-4">
            <input class="input max-w-xs" placeholder="Search courses…" x-model="search">
            <select class="input max-w-[200px]" x-model="category">
                <option value="">All categories</option>
                @foreach ($categories as $cat)
                    <option value="{{ $cat->slug }}">{{ $cat->name }}</option>
                @endforeach
            </select>
            <div class="flex gap-2">
                @foreach ([['', 'All'], ['free', 'Free'], ['paid', 'Paid']] as [$v, $l])
                    <button type="button" @click="price = '{{ $v }}'"
                        :class="price === '{{ $v }}' ? 'grad-primary border-transparent text-white' : 'border-[var(--border)] hover:border-[var(--primary)]'"
                        class="rounded-xl border px-4 py-2 text-sm font-bold transition-colors">{{ $l }}</button>
                @endforeach
            </div>
        </div>

        @if ($courses->isEmpty())
            <div class="card mt-8 grid place-items-center p-12 text-center">
                <span class="text-4xl">📚</span>
                <p class="mt-3 font-bold">No courses published yet.</p>
            </div>
        @else
            <div class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($courses as $c)
                    <a href="/courses/{{ $c->slug }}"
                        x-show="(!search || {{ \Illuminate\Support\Js::from(mb_strtolower($c->title . ' ' . $c->subtitle)) }}.includes(search.toLowerCase()))
                            && (!category || category === '{{ optional($c->category)->slug }}')
                            && (!price || price === '{{ $c->is_free ? 'free' : 'paid' }}')"
                        class="card group flex flex-col overflow-hidden transition-transform hover:-translate-y-1">
                        <div class="relative h-44 text-white" style="background: linear-gradient(135deg, {{ optional($c->category)->color ?? '#2563ff' }}, #7c3aed)">
                            <div class="absolute inset-0 flex items-center justify-center text-5xl">🎓</div>
                            <span class="pill absolute left-3 top-3 bg-black/25 text-white backdrop-blur">{{ $c->level }}</span>
                            <span class="pill absolute right-3 top-3 {{ $c->is_free ? 'bg-[var(--success)] text-white' : 'bg-white text-[var(--primary)]' }}">
                                {{ $c->is_free ? 'FREE' : '$' . number_format((float) $c->price, 2) }}
                            </span>
                        </div>
                        <div class="flex flex-1 flex-col p-6">
                            @if ($c->category)
                                <span class="text-xs font-bold uppercase" style="color: {{ $c->category->color }}">{{ $c->category->name }}</span>
                            @endif
                            <h3 class="mt-1 text-lg">{{ $c->title }}</h3>
                            <p class="mt-1 flex-1 text-sm font-semibold text-[var(--muted)]">{{ $c->subtitle }}</p>
                            <div class="mt-4 flex items-center justify-between border-t border-[var(--border)] pt-3 text-xs text-[var(--muted)]">
                                <span class="font-bold text-[var(--foreground)]">{{ optional($c->teacher)->name }}</span>
                                <span>{{ $c->lessons_count ?? 0 }} lessons</span>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection
