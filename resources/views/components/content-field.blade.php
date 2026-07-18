@props(['label', 'model'])
<div>
    <label class="label">{{ $label }}</label>
    <input class="input" x-model="{{ $model }}">
</div>
