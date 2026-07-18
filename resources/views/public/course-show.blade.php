@extends('layouts.public')
@section('title', $course->title . ' — ' . ($site['footer']['brand'] ?? 'LMS'))

@section('content')
@php
    $firstPreview = $course->lessons->firstWhere('is_preview', true);
    $catColor = optional($course->category)->color ?? '#2563ff';
@endphp
<div class="grad-hero" x-data="{ preview: null }">
    <div class="mx-auto max-w-[1600px] px-5 py-8 md:px-8 md:py-10">
        <a href="/courses" class="text-sm font-bold text-[var(--primary)] hover:underline">← Courses</a>

        <div class="mt-5 grid gap-8 lg:grid-cols-[minmax(0,1fr)_380px]">
            {{-- Left --}}
            <div class="min-w-0 space-y-6">
                <div>
                    @if ($course->category)
                        <span class="pill grad-ph text-white">{{ $course->category->name }}</span>
                    @endif
                    <h1 class="mt-3 text-3xl md:text-5xl">{{ $course->title }}</h1>
                    @if ($course->subtitle)
                        <p class="mt-3 text-lg font-semibold text-[var(--muted)]">{{ $course->subtitle }}</p>
                    @endif
                    <div class="mt-4 flex flex-wrap gap-4 text-sm font-semibold text-[var(--muted)]">
                        <span>👨‍🏫 <b class="text-[var(--foreground)]">{{ optional($course->teacher)->name }}</b></span>
                        <span>📚 {{ $course->lessons->count() }} lessons</span>
                        <span>🎯 {{ $course->level }}</span>
                        <span>👥 {{ $course->enrollments_count ?? 0 }} enrolled</span>
                    </div>
                </div>

                <div class="card p-6 md:p-8">
                    <h2 class="text-xl">About this course</h2>
                    <p class="mt-3 whitespace-pre-line font-semibold text-[var(--muted)]">{{ $course->description }}</p>
                </div>

                <div class="card p-6 md:p-8">
                    <h2 class="text-xl">Curriculum</h2>
                    <div class="mt-4 divide-y divide-[var(--border)]">
                        @foreach ($course->lessons as $i => $l)
                            @php $canPreview = $l->is_preview; @endphp
                            <button type="button"
                                @if ($canPreview)
                                    @click="preview = {{ \Illuminate\Support\Js::from(['title' => $l->title, 'file' => $l->video_file_url, 'url' => $l->video_url]) }}"
                                @endif
                                @disabled(! $canPreview)
                                class="flex w-full items-center gap-3 py-3.5 text-left transition-colors {{ $canPreview ? '-mx-2 rounded-lg px-2 hover:bg-[var(--primary)]/5' : 'cursor-default' }}">
                                <span class="grid h-9 w-9 shrink-0 place-items-center rounded-full bg-[var(--primary-soft)] text-sm font-bold text-[var(--primary)]">{{ $i + 1 }}</span>
                                <div class="flex-1">
                                    <p class="font-bold text-[var(--foreground)]">{{ $l->title }}</p>
                                    <p class="text-xs text-[var(--muted)]">{{ $l->type }} · {{ $l->duration_minutes }} min</p>
                                </div>
                                @if ($canPreview)
                                    <span class="pill bg-[var(--success)]/15 text-[var(--success)]">▶ Preview</span>
                                @else
                                    <span class="text-lg">🔒</span>
                                @endif
                            </button>
                        @endforeach
                    </div>
                    <p class="mt-4 text-sm font-semibold text-[var(--muted)]">🔒 Enroll to unlock all lessons and start learning.</p>
                </div>
            </div>

            {{-- Right: sticky enroll card --}}
            <div class="lg:sticky lg:top-24 lg:h-fit">
                <div class="card overflow-hidden">
                    <button type="button"
                        @if ($firstPreview) @click="preview = {{ \Illuminate\Support\Js::from(['title' => $firstPreview->title, 'file' => $firstPreview->video_file_url, 'url' => $firstPreview->video_url]) }}" @endif
                        class="relative flex h-44 w-full items-center justify-center text-white"
                        style="background: linear-gradient(135deg, {{ $catColor }}, #a020f0)">
                        <span class="text-5xl">🎓</span>
                        @if ($firstPreview)
                            <span class="pill absolute bottom-3 left-1/2 -translate-x-1/2 bg-black/40 text-white backdrop-blur">▶ Watch free preview</span>
                        @endif
                    </button>
                    <div class="p-6">
                        <p class="text-3xl">
                            @if ($course->is_free)
                                <span class="text-[var(--success)]">FREE</span>
                            @else
                                ${{ number_format((float) $course->price, 2) }}
                            @endif
                        </p>
                        <a href="/register" class="btn-primary mt-4 w-full">{{ $course->is_free ? 'Start learning free →' : 'Enroll Now' }}</a>
                        <p class="mt-2 text-center text-xs font-semibold text-[var(--muted)]">
                            <a href="/login" class="text-[var(--primary)] hover:underline">Login</a> to enroll
                        </p>
                        <ul class="mt-5 space-y-2 text-sm font-semibold text-[var(--muted)]">
                            <li>✅ {{ $course->lessons->count() }} lessons</li>
                            <li>✅ Lifetime access</li>
                            <li>✅ Learn at your own pace</li>
                            @if (! $course->is_free)
                                <li>✅ Certificate of completion</li>
                            @endif
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Preview video modal --}}
    <div x-show="preview" x-cloak @click="preview = null"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 p-4">
        <div class="w-full max-w-4xl overflow-hidden rounded-[var(--radius)] bg-black" @click.stop>
            <div class="flex items-center justify-between bg-[var(--surface)] px-5 py-3">
                <p class="font-bold text-[var(--foreground)]">▶ <span x-text="preview?.title"></span> <span class="font-semibold text-[var(--muted)]">· free preview</span></p>
                <button @click="preview = null" class="text-2xl leading-none text-[var(--muted)] hover:text-[var(--foreground)]">×</button>
            </div>
            <template x-if="preview?.file">
                <video controls autoplay class="aspect-video w-full bg-black" :src="preview.file"></video>
            </template>
            <template x-if="!preview?.file && preview?.url">
                <div class="aspect-video w-full">
                    <iframe :src="preview.url + (preview.url.includes('?') ? '&' : '?') + 'autoplay=1'" class="h-full w-full" allow="autoplay; encrypted-media; picture-in-picture" allowfullscreen></iframe>
                </div>
            </template>
            <template x-if="!preview?.file && !preview?.url">
                <div class="grid aspect-video place-items-center text-white/70">No video for this preview</div>
            </template>
        </div>
    </div>
</div>
@endsection
