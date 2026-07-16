@php
    $brand = $site['footer']['brand'] ?? 'LMS';
    $links = [
        ['/courses',     ['en' => 'Courses',     'bn' => 'কোর্স']],
        ['/instructors', ['en' => 'Instructors', 'bn' => 'ইন্সট্রাক্টর']],
        ['/pricing',     ['en' => 'Pricing',     'bn' => 'প্রাইসিং']],
        ['/about',       ['en' => 'About',        'bn' => 'সম্পর্কে']],
        ['/contact',     ['en' => 'Contact',      'bn' => 'যোগাযোগ']],
    ];
@endphp

<header x-data="{ open: false }"
    class="sticky top-0 z-30 border-b border-[var(--border)] bg-[var(--surface)]/85 backdrop-blur">
    <nav class="mx-auto flex h-16 max-w-[1600px] items-center justify-between px-5 md:px-8">
        <a href="/" class="flex items-center gap-2.5">
            <span class="grid h-8 w-8 place-items-center rounded-xl grad-primary text-lg text-white">✦</span>
            <span class="text-xl font-extrabold tracking-tight">{{ $brand }}</span>
        </a>

        <div class="hidden items-center gap-7 md:flex">
            @foreach ($links as [$href, $label])
                <a href="{{ $href }}"
                    class="text-sm font-bold transition-colors {{ request()->is(ltrim($href, '/')) ? 'text-[var(--primary)]' : 'text-[var(--muted)] hover:text-[var(--foreground)]' }}">
                    <x-loc :value="$label" />
                </a>
            @endforeach
        </div>

        <div class="flex items-center gap-2">
            {{-- Language toggle --}}
            <div class="flex items-center rounded-full border border-[var(--border)] p-0.5 text-xs font-bold">
                <button type="button" @click="$store.lang.set('en')"
                    :class="$store.lang.current === 'en' ? 'grad-primary text-white' : 'text-[var(--muted)]'"
                    class="rounded-full px-2.5 py-1 transition-colors">EN</button>
                <button type="button" @click="$store.lang.set('bn')"
                    :class="$store.lang.current === 'bn' ? 'grad-primary text-white' : 'text-[var(--muted)]'"
                    class="rounded-full px-2.5 py-1 transition-colors">বাংলা</button>
            </div>

            {{-- Auth-aware buttons (server-side session) --}}
            @auth
                <a href="/dashboard" class="btn-primary px-4 py-2 text-sm">
                    <x-loc :value="['en' => 'Dashboard', 'bn' => 'ড্যাশবোর্ড']" />
                </a>
            @else
                <a href="/login" class="hidden rounded-lg px-4 py-2 text-sm font-bold text-[var(--foreground)] hover:text-[var(--primary)] sm:block">
                    <x-loc :value="['en' => 'Login', 'bn' => 'লগইন']" />
                </a>
                <a href="/register" class="btn-primary px-4 py-2 text-sm">
                    <x-loc :value="['en' => 'Get started', 'bn' => 'শুরু করুন']" />
                </a>
            @endauth

            <button @click="open = !open" class="rounded-lg border border-[var(--border)] p-2 md:hidden" aria-label="Menu">☰</button>
        </div>
    </nav>

    {{-- Mobile menu --}}
    <div x-show="open" x-cloak class="border-t border-[var(--border)] bg-[var(--surface)] px-5 py-3 md:hidden">
        @foreach ($links as [$href, $label])
            <a href="{{ $href }}" class="block py-2 font-bold text-[var(--foreground)]"><x-loc :value="$label" /></a>
        @endforeach
    </div>
</header>
