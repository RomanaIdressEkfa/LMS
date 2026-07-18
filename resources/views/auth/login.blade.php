@extends('layouts.bare')
@section('title', 'Login — LMS')

@section('content')
<main class="flex min-h-dvh w-full items-center justify-center bg-[var(--background)] px-4 py-10">
    <div class="card grid w-full max-w-5xl overflow-hidden md:grid-cols-2" x-data="{ login: '{{ old('login') }}', password: '', show: false }">
        {{-- Form side --}}
        <div class="p-8 sm:p-12">
            <a href="/" class="flex items-center gap-2.5">
                <span class="grid h-9 w-9 place-items-center rounded-xl grad-primary text-lg text-white">✦</span>
                <span class="text-xl font-extrabold">LMS</span>
            </a>

            <div class="mt-10">
                <p class="text-[var(--muted)]">Welcome back 👋</p>
                <h1 class="mt-1 text-3xl">Log in to your account</h1>
            </div>

            <form method="POST" action="/login" class="mt-8 space-y-5">
                @csrf
                <div>
                    <label class="label" for="login">Email or Phone</label>
                    <input id="login" name="login" class="input" placeholder="you@example.com" x-model="login" autocomplete="username" required>
                </div>
                <div>
                    <label class="label" for="password">Password</label>
                    <div class="relative">
                        <input id="password" name="password" :type="show ? 'text' : 'password'" class="input pr-12" placeholder="••••••••" x-model="password" autocomplete="current-password" required>
                        <button type="button" @click="show = !show" class="absolute right-3 top-1/2 -translate-y-1/2 text-sm font-bold text-[var(--muted)] hover:text-[var(--primary)]" x-text="show ? 'Hide' : 'Show'"></button>
                    </div>
                </div>

                @if ($errors->any())
                    <p class="rounded-[var(--radius)] bg-[var(--danger)]/10 px-4 py-3 text-sm font-bold text-[var(--danger)]">{{ $errors->first() }}</p>
                @endif

                <button type="submit" class="btn-primary w-full">Login</button>
            </form>

            {{-- Demo accounts --}}
            <div class="mt-8">
                <p class="text-center text-sm text-[var(--muted)]">Quick demo login</p>
                <div class="mt-3 grid grid-cols-2 gap-2 sm:grid-cols-4">
                    @foreach ([['Super', 'super@novalms.test'], ['Admin', 'admin@novalms.test'], ['Teacher', 'teacher@novalms.test'], ['Student', 'student@novalms.test']] as [$label, $email])
                        <button type="button" @click="login = '{{ $email }}'; password = 'password'"
                            class="rounded-[var(--radius)] border border-[var(--border)] px-3 py-2 text-sm font-bold text-[var(--foreground)] transition-colors hover:border-[var(--primary)] hover:text-[var(--primary)]">{{ $label }}</button>
                    @endforeach
                </div>
                <p class="mt-2 text-center text-xs text-[var(--muted)]">password: <b>password</b></p>
            </div>

            <p class="mt-8 text-center text-sm text-[var(--muted)]">
                Don't have an account? <a href="/register" class="font-bold text-[var(--primary)] hover:underline">Sign Up</a>
            </p>
        </div>

        {{-- Illustration side --}}
        <div class="relative hidden flex-col items-center justify-center bg-gradient-to-br from-[#2563ff] to-[#1d4ed8] p-12 text-white md:flex">
            <div class="absolute inset-0 opacity-20" style="background-image: radial-gradient(circle at 20% 20%, #fff 2px, transparent 0), radial-gradient(circle at 70% 60%, #fff 2px, transparent 0); background-size: 48px 48px"></div>
            <div class="relative text-center">
                <div class="mx-auto mb-8 flex h-40 w-40 items-center justify-center rounded-full bg-white/10 text-6xl backdrop-blur">✦</div>
                <h2 class="font-display text-3xl">Learn Without Limits</h2>
                <p class="mt-3 max-w-xs text-white/80">Create, sell and teach courses. Host live classes. Grow your academy — all in one bold platform.</p>
            </div>
        </div>
    </div>
</main>
@endsection
