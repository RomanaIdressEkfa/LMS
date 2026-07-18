@php
    $brand = $site['footer']['brand'] ?? 'LMS';
    $cols = [
        ['Platform', [['Courses', '/courses'], ['Instructors', '/instructors'], ['Pricing', '/pricing']]],
        ['Company',  [['About', '/about'], ['Contact', '/contact']]],
        ['Account',  [['Login', '/login'], ['Register', '/register']]],
    ];
@endphp

<footer class="grad-ph relative overflow-hidden text-white">
    <div class="blob grad-magenta -right-20 -top-20 h-72 w-72"></div>
    <div class="relative mx-auto grid max-w-[1600px] gap-8 px-5 py-14 md:grid-cols-4 md:px-8">
        <div class="md:col-span-1">
            <div class="flex items-center gap-2.5">
                <span class="grid h-9 w-9 place-items-center rounded-xl bg-white/15 text-lg">✦</span>
                <span class="text-xl font-extrabold">{{ $brand }}</span>
            </div>
            <p class="mt-3 max-w-xs text-sm font-semibold text-white/80">
                <x-loc :value="$site['footer']['tagline'] ?? ''" />
            </p>
        </div>

        @foreach ($cols as [$title, $items])
            <div>
                <p class="text-sm font-extrabold">{{ $title }}</p>
                <ul class="mt-3 space-y-2">
                    @foreach ($items as [$label, $href])
                        <li><a href="{{ $href }}" class="text-sm font-semibold text-white/80 hover:text-white">{{ $label }}</a></li>
                    @endforeach
                </ul>
            </div>
        @endforeach
    </div>
    <div class="relative border-t border-white/15">
        <div class="mx-auto max-w-[1600px] px-5 py-6 text-center text-sm font-semibold text-white/75 md:px-8">
            © {{ date('Y') }} {{ $brand }}. All rights reserved.
        </div>
    </div>
</footer>
