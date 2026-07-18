@props(['label', 'model', 'options'])
<div>
    <label class="label">{{ $label }}</label>
    <select class="input" x-model="{{ $model }}">
        <template x-for="o in {{ $options }}" :key="o"><option :value="o" x-text="o"></option></template>
    </select>
</div>
