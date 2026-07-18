@extends('layouts.dashboard')
@section('title', 'Edit Course — LMS')

@php
    use Illuminate\Support\Js;
    $editing = request('lesson') ? $course->lessons->firstWhere('id', (int) request('lesson')) : null;
    $eOptions = $editing && $editing->question_options && count($editing->question_options) >= 2 ? array_values($editing->question_options) : ['', ''];
    $eCorrect = (int) ($editing->question_correct_index ?? 0);
    $siblingTitles = $course->lessons->where('id', '!=', optional($editing)->id)->pluck('title')->values();
    $editingVideoUrl = $editing?->video_file_url;
@endphp

@section('content')
<div class="space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <a href="/dashboard/teaching" class="text-sm font-bold text-[var(--primary)] hover:underline">← Teaching</a>
            <h1 class="mt-1 text-3xl">Edit Course</h1>
        </div>
        <div class="flex items-center gap-2">
            <a href="/courses/{{ $course->slug }}" class="btn-ghost">Preview</a>
            <form method="POST" action="/dashboard/teaching/{{ $course->id }}/publish">
                @csrf
                <button class="{{ $course->status === 'published' ? 'btn-ghost' : 'btn-primary' }}">{{ $course->status === 'published' ? 'Unpublish' : 'Publish' }}</button>
            </form>
        </div>
    </div>

    @if (session('ok'))
        <p class="rounded-[var(--radius)] bg-[var(--success)]/10 px-4 py-3 text-sm font-bold text-[var(--success)]">{{ session('ok') }}</p>
    @endif

    <div class="grid gap-6 lg:grid-cols-2">
        {{-- Course details --}}
        <form method="POST" action="/dashboard/teaching/{{ $course->id }}" class="card space-y-4 p-6" x-data="{ free: {{ $course->is_free ? 'true' : 'false' }} }">
            @csrf @method('PUT')
            <div class="flex items-center justify-between">
                <h2 class="text-xl">Details</h2>
                <span class="rounded-full px-2.5 py-1 text-[11px] font-bold uppercase {{ $course->status === 'published' ? 'bg-[var(--success)]/15 text-[var(--success)]' : 'bg-[var(--warning)]/15 text-[var(--warning)]' }}">{{ $course->status }}</span>
            </div>
            <div>
                <label class="label">Title</label>
                <input name="title" class="input" value="{{ old('title', $course->title) }}" required>
            </div>
            <div>
                <label class="label">Subtitle</label>
                <input name="subtitle" class="input" value="{{ old('subtitle', $course->subtitle) }}">
            </div>
            <div>
                <label class="label">Description</label>
                <textarea name="description" class="input min-h-28">{{ old('description', $course->description) }}</textarea>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="label">Level</label>
                    <select name="level" class="input">
                        @foreach (['beginner', 'intermediate', 'advanced'] as $lvl)
                            <option value="{{ $lvl }}" @selected($course->level === $lvl)>{{ ucfirst($lvl) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="label">Pricing</label>
                    <div class="flex items-center gap-2">
                        <label class="flex items-center gap-1.5 text-sm font-bold">
                            <input type="checkbox" name="is_free" value="1" x-model="free" class="h-4 w-4 accent-[var(--primary)]"> Free
                        </label>
                        <input type="number" name="price" min="0" step="0.01" class="input" placeholder="$" value="{{ $course->price }}" x-show="!free" x-cloak>
                    </div>
                </div>
            </div>
            <button type="submit" class="btn-primary">Save details</button>
        </form>

        {{-- Curriculum --}}
        <div class="space-y-6">
            <div class="card p-6">
                <h2 class="text-xl">Curriculum ({{ $course->lessons->count() }})</h2>
                <div class="mt-4 space-y-2">
                    @forelse ($course->lessons as $i => $l)
                        <div class="flex items-center gap-3 rounded-[var(--radius)] border border-[var(--border)] p-3">
                            <span class="grid h-7 w-7 place-items-center rounded-full bg-[var(--primary)]/10 text-xs font-bold text-[var(--primary)]">{{ $i + 1 }}</span>
                            <div class="flex-1">
                                <p class="font-bold text-[var(--foreground)]">{{ $l->title }}</p>
                                <p class="text-xs text-[var(--muted)]">{{ $l->type }} · {{ $l->duration_minutes }}m {{ $l->is_preview ? '· preview' : '' }} {{ $l->video_file_url ? '· 🎬 video' : '' }}</p>
                            </div>
                            <a href="/dashboard/teaching/{{ $course->id }}?lesson={{ $l->id }}" class="text-sm font-bold text-[var(--primary)] hover:underline">Edit</a>
                            <form method="POST" action="/dashboard/teaching/{{ $course->id }}/lessons/{{ $l->id }}" onsubmit="return confirm('Delete this lesson?')">
                                @csrf @method('DELETE')
                                <button class="text-sm font-bold text-[var(--danger)] hover:underline">Delete</button>
                            </form>
                        </div>
                    @empty
                        <p class="text-sm text-[var(--muted)]">No lessons yet — add your first below.</p>
                    @endforelse
                </div>
            </div>

            {{-- Lesson add/edit form --}}
            <form method="POST"
                action="/dashboard/teaching/{{ $course->id }}/lessons{{ $editing ? '/' . $editing->id : '' }}"
                class="card space-y-3 p-6"
                x-data="{
                    options: {{ Js::from($eOptions) }},
                    correct: {{ $eCorrect }},
                    title: {{ Js::from(old('title', $editing->title ?? '')) }},
                    type: {{ Js::from($editing->type ?? 'video') }},
                    uploadPct: null,
                    addOption() { if (this.options.length < 5) this.options.push(''); },
                    removeOption(i) { if (this.options.length > 2) { this.options.splice(i, 1); if (this.correct >= this.options.length) this.correct = 0; } },
                    generate() {
                        const shuffle = a => { a = [...a]; for (let i = a.length - 1; i > 0; i--) { const j = Math.floor(Math.random() * (i + 1)); [a[i], a[j]] = [a[j], a[i]]; } return a; };
                        const correctAns = (this.title || '').trim() || 'This lesson\'s topic';
                        const sib = {{ Js::from($siblingTitles) }}.map(t => t.trim()).filter(t => t && t.toLowerCase() !== correctAns.toLowerCase());
                        const generics = ['A different topic', 'None of the above', 'An unrelated subject', 'Something else entirely'];
                        const distractors = shuffle(sib).slice(0, 3);
                        for (const g of generics) { if (distractors.length >= 3) break; if (!distractors.includes(g)) distractors.push(g); }
                        const opts = shuffle([correctAns, ...distractors]);
                        this.options = opts;
                        this.correct = opts.indexOf(correctAns);
                        this.$refs.question.value = 'Which topic does this lesson focus on?';
                    },
                    async upload(file) {
                        const MAX = 200 * 1024 * 1024;
                        if (file.size > MAX) { alert('Max 200MB'); return; }
                        this.uploadPct = 0;
                        const fd = new FormData(); fd.append('video', file);
                        const xhr = new XMLHttpRequest();
                        xhr.open('POST', '/dashboard/teaching/{{ $course->id }}/lessons/{{ $editing->id ?? 0 }}/video');
                        xhr.setRequestHeader('Accept', 'application/json');
                        xhr.setRequestHeader('X-CSRF-TOKEN', document.querySelector('meta[name=csrf-token]').content);
                        xhr.upload.onprogress = e => { if (e.lengthComputable) this.uploadPct = Math.round(e.loaded / e.total * 100); };
                        xhr.onload = () => { this.uploadPct = null; window.location.reload(); };
                        xhr.onerror = () => { this.uploadPct = null; alert('Upload failed'); };
                        xhr.send(fd);
                    }
                }">
                @csrf
                @if ($editing) @method('PUT') @endif
                <h3 class="text-lg">{{ $editing ? 'Edit lesson' : 'Add lesson' }}</h3>
                <input name="title" class="input" placeholder="Lesson title" x-model="title" required>
                <div class="grid grid-cols-2 gap-3">
                    <select name="type" class="input" x-model="type">
                        <option value="video">Video</option>
                        <option value="text">Text</option>
                        <option value="pdf">PDF</option>
                    </select>
                    <input type="number" name="duration_minutes" min="0" class="input" placeholder="Minutes" value="{{ $editing->duration_minutes ?? 10 }}">
                </div>

                <div x-show="type === 'video'">
                    <input name="video_url" class="input" placeholder="Paste any YouTube or Vimeo link" value="{{ $editing->video_url ?? '' }}">
                    <p class="mt-1 text-xs font-semibold text-[var(--muted)]">Paste a normal link — it's converted automatically.</p>
                    @if ($editing)
                        <div class="mt-3 rounded-xl border border-dashed border-[var(--border)] p-3">
                            <p class="text-xs font-bold text-[var(--muted)]">…or upload a video file (mp4/webm/mov, max 200MB)</p>
                            @if ($editingVideoUrl)
                                <p class="mt-1 text-xs font-bold text-[var(--success)]">✓ A video is uploaded — choose a file to replace it.</p>
                            @endif
                            <input type="file" accept="video/mp4,video/webm,video/quicktime" class="mt-2 text-sm" :disabled="uploadPct !== null"
                                @change="if ($event.target.files[0]) upload($event.target.files[0])">
                            <template x-if="uploadPct !== null">
                                <div class="mt-3">
                                    <div class="flex items-center justify-between text-xs font-bold text-[var(--muted)]">
                                        <span x-text="uploadPct < 100 ? 'Uploading…' : 'Processing…'"></span><span x-text="uploadPct + '%'"></span>
                                    </div>
                                    <div class="mt-1 h-2 overflow-hidden rounded-full bg-[var(--border)]"><div class="grad-primary h-full rounded-full transition-all" :style="`width:${uploadPct}%`"></div></div>
                                </div>
                            </template>
                        </div>
                    @else
                        <p class="mt-2 text-xs font-semibold text-[var(--muted)]">💡 Add the lesson first, then upload a video file for it.</p>
                    @endif
                </div>

                <textarea name="content" class="input min-h-20" placeholder="Lesson notes / content">{{ $editing->content ?? '' }}</textarea>

                {{-- MCQ --}}
                <div class="rounded-xl border border-[var(--border)] p-4">
                    <div class="flex items-start justify-between gap-3">
                        <p class="text-sm font-extrabold">🧠 Quiz question <span class="font-semibold text-[var(--muted)]">(optional — unlocks the next lesson)</span></p>
                        <button type="button" @click="generate()" class="btn-ghost shrink-0 !py-1.5 !px-3 text-xs">✨ Generate</button>
                    </div>
                    <textarea name="question" x-ref="question" class="input mt-3 min-h-16" placeholder="Question text">{{ $editing->question ?? '' }}</textarea>
                    <p class="label mt-3">Options (pick the correct one)</p>
                    <template x-for="(opt, i) in options" :key="i">
                        <div class="mb-2 flex items-center gap-2">
                            <input type="radio" name="question_correct_index" :value="i" :checked="correct === i" @change="correct = i" class="h-4 w-4 accent-[var(--success)]">
                            <input class="input" name="question_options[]" :placeholder="`Option ${i + 1}`" x-model="options[i]">
                            <button type="button" x-show="options.length > 2" @click="removeOption(i)" class="text-[var(--danger)]">×</button>
                        </div>
                    </template>
                    <button type="button" x-show="options.length < 5" @click="addOption()" class="text-sm font-bold text-[var(--primary)] hover:underline">+ Add option</button>
                </div>

                <label class="flex items-center gap-2 text-sm font-bold">
                    <input type="checkbox" name="is_preview" value="1" @checked($editing->is_preview ?? false) class="h-4 w-4 accent-[var(--primary)]"> Free preview (visible before enrolling)
                </label>
                <div class="flex gap-2">
                    <button type="submit" class="btn-primary">{{ $editing ? 'Update lesson' : 'Add lesson' }}</button>
                    @if ($editing)
                        <a href="/dashboard/teaching/{{ $course->id }}" class="btn-ghost">Cancel</a>
                    @endif
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
