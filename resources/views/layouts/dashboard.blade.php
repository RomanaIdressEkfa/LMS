<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard — LMS')</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700,800|hind-siliguri:400,500,600,700" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
@php
    $u = auth()->user();
    $nav = [
        ['side.main', [
            ['side.dashboard', '/dashboard', '🏠', 'dashboard.view'],
        ]],
        ['side.education', [
            ['side.catalog', '/dashboard/courses', '🎓', 'courses.view'],
            ['side.learning', '/dashboard/learn', '📚', 'courses.view'],
            ['side.purchases', '/dashboard/purchases', '🧾', 'courses.view'],
            ['side.teaching', '/dashboard/teaching', '✏️', 'courses.create'],
            ['side.live', '/dashboard/live', '🎥', 'live.view'],
            ['side.quizzes', '/dashboard/quizzes', '📝', 'quizzes.view'],
        ]],
        ['side.administration', [
            ['side.users', '/dashboard/users', '👥', 'users.view'],
            ['side.roles', '/dashboard/roles', '🛡️', 'roles.view'],
            ['side.modules', '/dashboard/modules', '🧩', 'modules.view'],
            ['side.gateways', '/dashboard/gateways', '💳', 'gateways.view'],
            ['side.content', '/dashboard/content', '📰', 'settings.view'],
            ['side.settings', '/dashboard/settings', '⚙️', 'settings.view'],
        ]],
        ['side.platform', [
            ['side.tenants', '/dashboard/platform/tenants', '🏢', 'tenants.view'],
            ['side.plans', '/dashboard/platform/plans', '💠', 'tenants.view'],
        ]],
    ];
@endphp
<body x-data="{ menuOpen: false }" class="antialiased">
    <div class="flex min-h-dvh bg-[var(--background)]">
        {{-- Mobile backdrop --}}
        <div x-show="menuOpen" x-cloak @click="menuOpen = false" class="fixed inset-0 z-30 bg-black/40 md:hidden"></div>

        {{-- Sidebar --}}
        <aside :class="menuOpen ? 'translate-x-0' : '-translate-x-full'"
            class="fixed z-40 h-dvh w-72 shrink-0 overflow-y-auto border-r border-[var(--border)] bg-[var(--surface)] px-5 py-7 transition-transform md:static md:translate-x-0 md:!translate-x-0">
            <a href="/" class="flex items-center gap-2.5 px-2">
                <span class="grid h-8 w-8 place-items-center rounded-xl grad-primary text-lg text-white">✦</span>
                <span class="text-xl font-extrabold tracking-tight">LMS</span>
            </a>

            {{-- Profile mini-card --}}
            <div class="mt-6 rounded-[var(--radius)] border border-[var(--border)] bg-[var(--background)] p-4">
                <p class="truncate text-sm font-bold text-[var(--foreground)]">{{ $u->name }}</p>
                <p class="mt-0.5 truncate text-xs text-[var(--muted)]">{{ $u->email }}</p>
                <div class="mt-2 flex flex-wrap gap-1">
                    @foreach ($u->getRoleNames() as $r)
                        <span class="rounded-full bg-[var(--primary)]/10 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-[var(--primary)]">{{ $r }}</span>
                    @endforeach
                </div>
            </div>

            <nav class="mt-6 space-y-6">
                @foreach ($nav as [$sectionKey, $items])
                    @php $visible = array_filter($items, fn ($i) => $u->can($i[3])); @endphp
                    @if (count($visible))
                        <div>
                            <p class="px-2 text-[11px] font-bold uppercase tracking-wider text-[var(--muted)]"><x-t :k="$sectionKey" /></p>
                            <div class="mt-2 space-y-1">
                                @foreach ($visible as [$tkey, $href, $icon, $perm])
                                    @php $active = $href === '/dashboard' ? request()->is('dashboard') : request()->is(ltrim($href, '/') . '*'); @endphp
                                    <a href="{{ $href }}" @click="menuOpen = false"
                                        class="flex items-center gap-3 rounded-[var(--radius)] px-3 py-2.5 text-sm font-bold transition-colors {{ $active ? 'bg-[var(--primary)] text-white' : 'text-[var(--foreground)] hover:bg-[var(--primary)]/8' }}">
                                        <span class="text-base">{{ $icon }}</span>
                                        <x-t :k="$tkey" />
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif
                @endforeach
            </nav>
        </aside>

        {{-- Main column --}}
        <div class="flex min-w-0 flex-1 flex-col">
            {{-- Topbar --}}
            <header class="sticky top-0 z-20 flex h-16 items-center justify-between border-b border-[var(--border)] bg-[var(--surface)]/85 px-5 backdrop-blur md:px-8">
                <button @click="menuOpen = true" class="rounded-lg border border-[var(--border)] p-2 md:hidden" aria-label="Menu">☰</button>
                <div class="flex flex-1 items-center justify-end gap-3">
                    {{-- Language toggle --}}
                    <div class="flex items-center rounded-full border border-[var(--border)] p-0.5 text-xs font-bold">
                        <button type="button" @click="$store.lang.set('en')" :class="$store.lang.current === 'en' ? 'grad-primary text-white' : 'text-[var(--muted)]'" class="rounded-full px-2.5 py-1 transition-colors">EN</button>
                        <button type="button" @click="$store.lang.set('bn')" :class="$store.lang.current === 'bn' ? 'grad-primary text-white' : 'text-[var(--muted)]'" class="rounded-full px-2.5 py-1 transition-colors">বাংলা</button>
                    </div>
                    <form method="POST" action="/logout">
                        @csrf
                        <button class="btn-ghost !py-2 !px-4 text-sm"><x-t k="topbar.logout" /></button>
                    </form>
                </div>
            </header>

            <main class="flex-1 px-5 py-6 md:px-8 md:py-7">
                @yield('content')
            </main>
        </div>
    </div>
</body>
</html>
