@props(['k'])
{{-- Bilingual UI string by dictionary key (with admin overrides), instant EN/BN. --}}
<x-loc :value="\App\Support\Translations::get($k)" />
