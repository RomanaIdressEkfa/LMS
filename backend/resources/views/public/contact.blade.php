@extends('layouts.public')
@section('title', 'Contact — ' . ($site['footer']['brand'] ?? 'LMS'))

@section('content')
@php $contact = $site['contact']; @endphp
<div>
    <section class="grad-hero border-b border-[var(--border)]">
        <div class="mx-auto max-w-4xl px-5 py-14 text-center md:px-8">
            <h1 class="text-4xl md:text-5xl"><x-loc :value="$contact['hero']['titleA']" /> <span class="gradient-text"><x-loc :value="$contact['hero']['titleHl']" /></span></h1>
            <p class="mx-auto mt-3 max-w-xl text-lg font-semibold text-[var(--muted)]"><x-loc :value="$contact['hero']['subtitle']" /></p>
        </div>
    </section>

    <div class="mx-auto grid max-w-6xl gap-8 px-5 py-14 md:px-8 lg:grid-cols-[1fr_1.3fr]">
        <div class="space-y-4">
            @foreach ($contact['channels'] as $c)
                <div class="card flex items-center gap-4 p-5">
                    <div class="grid h-12 w-12 shrink-0 place-items-center rounded-2xl text-xl text-white {{ $c['grad'] }}">{{ $c['icon'] }}</div>
                    <div>
                        <p class="font-extrabold"><x-loc :value="$c['title']" /></p>
                        <p class="text-sm font-semibold text-[var(--muted)]"><x-loc :value="$c['value']" /></p>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Demo contact form (front-end only, like the old app) --}}
        <div class="card p-8" x-data="{ sent: false, name: '' }">
            <div x-show="sent" x-cloak class="grid place-items-center py-12 text-center">
                <span class="text-5xl">🎉</span>
                <h2 class="mt-4 text-2xl">Message sent!</h2>
                <p class="mt-2 font-semibold text-[var(--muted)]">Thanks <span x-text="name || 'there'"></span> — we'll get back to you soon.</p>
                <button @click="sent = false; name = ''" class="btn-ghost mt-6">Send another</button>
            </div>
            <form x-show="!sent" @submit.prevent="sent = true" class="space-y-4">
                <h2 class="text-2xl">Send us a message</h2>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="label">Your name</label>
                        <input class="input" x-model="name" required>
                    </div>
                    <div>
                        <label class="label">Email</label>
                        <input type="email" class="input" required>
                    </div>
                </div>
                <div>
                    <label class="label">Message</label>
                    <textarea class="input min-h-36" required></textarea>
                </div>
                <button type="submit" class="btn-primary w-full">Send message</button>
            </form>
        </div>
    </div>
</div>
@endsection
