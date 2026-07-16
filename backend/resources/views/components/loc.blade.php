@props(['value'])
{{-- Renders a bilingual { en, bn } value (or a plain string). Server-renders
     English for SEO/no-JS, then Alpine swaps to the active language instantly. --}}
@php
    $en = is_array($value) ? ($value['en'] ?? '') : (string) ($value ?? '');
    $bn = is_array($value) ? ($value['bn'] ?? $en) : $en;
@endphp
<span x-text="$store.lang.isBn ? {{ \Illuminate\Support\Js::from($bn) }} : {{ \Illuminate\Support\Js::from($en) }}">{{ $en }}</span>
