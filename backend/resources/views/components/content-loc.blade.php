@props(['label', 'model', 'textarea' => false])
<div>
    <label class="label">{{ $label }}</label>
    <div class="grid gap-2 sm:grid-cols-2">
        @if ($textarea)
            <textarea class="input min-h-16" placeholder="English" x-model="{{ $model }}.en"></textarea>
            <textarea class="input min-h-16" placeholder="বাংলা" x-model="{{ $model }}.bn"></textarea>
        @else
            <input class="input" placeholder="English" x-model="{{ $model }}.en">
            <input class="input" placeholder="বাংলা" x-model="{{ $model }}.bn">
        @endif
    </div>
</div>
