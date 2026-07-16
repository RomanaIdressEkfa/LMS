@extends('layouts.bare')
@section('title', 'Register — LMS')

@section('content')
@php
    $roles = [
        ['student', 'Student', 'Learn & earn certificates', '🎓'],
        ['teacher', 'Instructor', 'Create & sell courses', '🧑‍🏫'],
        ['organization', 'Organization', 'Train your team', '🏢'],
    ];
    $perks = ['Access free & paid courses', 'Learn live with instructors', 'Earn certificates', 'Track your progress'];
@endphp
<main class="flex min-h-dvh w-full items-center justify-center bg-[var(--background)] px-4 py-10">
    <div class="card grid w-full max-w-5xl overflow-hidden md:grid-cols-2" x-data="{ role: '{{ old('role', 'student') }}' }">
        {{-- Form side --}}
        <div class="p-8 sm:p-12">
            <a href="/" class="flex items-center gap-2.5">
                <span class="grid h-9 w-9 place-items-center rounded-xl grad-primary text-lg text-white">✦</span>
                <span class="text-xl font-extrabold">LMS</span>
            </a>
            <div class="mt-8">
                <h1 class="text-3xl">Create your account</h1>
                <p class="mt-1 text-[var(--muted)]">Join us and start learning today.</p>
            </div>

            <form method="POST" action="/register" class="mt-6 space-y-4">
                @csrf
                {{-- Role selector --}}
                <div class="grid grid-cols-3 gap-2">
                    @foreach ($roles as [$value, $label, $hint, $icon])
                        <button type="button" @click="role = '{{ $value }}'"
                            :class="role === '{{ $value }}' ? 'border-[var(--primary)] bg-[var(--primary-soft)]' : 'border-[var(--border)] hover:border-[var(--primary)]'"
                            class="rounded-[var(--radius-sm)] border p-3 text-left transition-colors">
                            <span class="text-lg">{{ $icon }}</span>
                            <span class="mt-1 block text-sm font-bold text-[var(--foreground)]">{{ $label }}</span>
                            <span class="mt-0.5 block text-[11px] leading-tight text-[var(--muted)]">{{ $hint }}</span>
                        </button>
                    @endforeach
                </div>
                <input type="hidden" name="role" :value="role">

                <div>
                    <label class="label">Full name</label>
                    <input name="name" class="input" value="{{ old('name') }}" required>
                    @error('name') <p class="mt-1 text-xs font-bold text-[var(--danger)]">{{ $message }}</p> @enderror
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="label">Email</label>
                        <input type="email" name="email" class="input" value="{{ old('email') }}" required>
                        @error('email') <p class="mt-1 text-xs font-bold text-[var(--danger)]">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="label">Phone <span class="font-normal">(optional)</span></label>
                        <input name="phone" class="input" value="{{ old('phone') }}">
                        @error('phone') <p class="mt-1 text-xs font-bold text-[var(--danger)]">{{ $message }}</p> @enderror
                    </div>
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="label">Password</label>
                        <input type="password" name="password" class="input" required>
                        @error('password') <p class="mt-1 text-xs font-bold text-[var(--danger)]">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="label">Confirm</label>
                        <input type="password" name="password_confirmation" class="input" required>
                    </div>
                </div>

                <button type="submit" class="btn-primary w-full">Create Account</button>
            </form>

            <p class="mt-6 text-center text-sm text-[var(--muted)]">
                Already have an account? <a href="/login" class="font-bold text-[var(--primary)] hover:underline">Log in</a>
            </p>
        </div>

        {{-- Brand side --}}
        <div class="relative hidden flex-col justify-center overflow-hidden bg-gradient-to-br from-[#2563ff] to-[#1b4dd8] p-12 text-white md:flex">
            <div class="absolute inset-0 opacity-20" style="background-image: radial-gradient(circle at 25% 15%, #fff 2px, transparent 0), radial-gradient(circle at 75% 65%, #fff 2px, transparent 0); background-size: 44px 44px"></div>
            <div class="relative">
                <div class="mb-8 flex h-20 w-20 items-center justify-center rounded-2xl bg-white/10 text-4xl backdrop-blur">✦</div>
                <h2 class="font-display text-3xl leading-tight">Start your learning journey today</h2>
                <ul class="mt-8 space-y-4">
                    @foreach ($perks as $p)
                        <li class="flex items-center gap-3 font-semibold text-white/90">
                            <span class="grid h-6 w-6 place-items-center rounded-full bg-white/20 text-sm">✓</span>{{ $p }}
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
</main>
@endsection
