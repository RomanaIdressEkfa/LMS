@if (session('ok'))
    <p class="rounded-[var(--radius)] bg-[var(--success)]/10 px-4 py-3 text-sm font-bold text-[var(--success)]">{{ session('ok') }}</p>
@endif
@if (session('err'))
    <p class="rounded-[var(--radius)] bg-[var(--danger)]/10 px-4 py-3 text-sm font-bold text-[var(--danger)]">{{ session('err') }}</p>
@endif
