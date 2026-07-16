@extends('layouts.dashboard')
@section('title', 'Course Catalog — LMS')

@section('content')
<div class="space-y-6" x-data="{ search: '', category: '', price: '' }">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-3xl"><x-t k="cat.title" /></h1>
            <p class="mt-1 text-[var(--muted)]"><x-t k="cat.sub" /></p>
        </div>
        @if ($canCreate)
            <a href="/dashboard/teaching" class="btn-primary">+ Create Course</a>
        @endif
    </div>

    @if (session('ok'))
        <p class="rounded-[var(--radius)] bg-[var(--success)]/10 px-4 py-3 text-sm font-bold text-[var(--success)]">{{ session('ok') }}</p>
    @endif
    @if (session('err'))
        <p class="rounded-[var(--radius)] bg-[var(--danger)]/10 px-4 py-3 text-sm font-bold text-[var(--danger)]">{{ session('err') }}</p>
    @endif

    {{-- Filters --}}
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
        <div class="card grid place-items-center p-12 text-center">
            <span class="text-4xl">📚</span>
            <p class="mt-3 font-bold">No courses published yet.</p>
        </div>
    @else
        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($courses as $c)
                @php $enrolled = in_array($c->id, $enrolledIds, true); @endphp
                <div x-show="(!search || {{ \Illuminate\Support\Js::from(mb_strtolower($c->title . ' ' . $c->subtitle)) }}.includes(search.toLowerCase()))
                        && (!category || category === '{{ optional($c->category)->slug }}')
                        && (!price || price === '{{ $c->is_free ? 'free' : 'paid' }}')"
                    class="card flex flex-col overflow-hidden">
                    <div class="relative h-40 text-white" style="background: linear-gradient(135deg, {{ optional($c->category)->color ?? '#2563ff' }}, #7c3aed)">
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
                        <div class="mt-3 flex items-center justify-between text-xs text-[var(--muted)]">
                            <span class="font-bold text-[var(--foreground)]">{{ optional($c->teacher)->name }}</span>
                            <span>{{ $c->lessons_count }} <x-t k="cat.lessons" /></span>
                        </div>
                        <div class="mt-4">
                            @if ($enrolled)
                                <a href="/dashboard/learn/{{ $c->slug }}" class="btn-ghost w-full">Continue →</a>
                            @else
                                <form method="POST" action="/dashboard/courses/{{ $c->id }}/enroll">
                                    @csrf
                                    <button class="btn-primary w-full">{{ $c->is_free ? 'Enroll free' : 'Enroll' }}</button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
