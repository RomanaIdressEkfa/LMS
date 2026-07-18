@props(['title', 'list', 'blank'])
<div class="card p-6">
    <div class="flex items-center justify-between">
        <h3 class="text-lg">{{ $title }}</h3>
        <button type="button" @click="{{ $list }}.push({{ $blank }})" class="btn-ghost !py-1.5 !px-3 text-sm">+ Add</button>
    </div>
    <div class="mt-3 space-y-3">
        <template x-if="{{ $list }}.length === 0"><p class="text-sm text-[var(--muted)]">No items. Click “Add”.</p></template>
        <template x-for="(row, i) in {{ $list }}" :key="i">
            <div class="rounded-[var(--radius-sm)] border border-[var(--border)] bg-[var(--background)] p-4">
                <div class="mb-2 flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wide text-[var(--muted)]" x-text="'#' + (i + 1)"></span>
                    <button type="button" @click="{{ $list }}.splice(i, 1)" class="rounded px-2 py-1 text-sm font-bold text-[var(--danger)] hover:bg-[var(--danger)]/10">Remove</button>
                </div>
                <div class="grid grid-cols-1 gap-3">
                    {{ $slot }}
                </div>
            </div>
        </template>
    </div>
</div>
