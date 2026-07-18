@extends('layouts.dashboard')
@section('title', 'Settings — LMS')

@section('content')
<form method="POST" action="/dashboard/settings" class="max-w-2xl space-y-6">
    @csrf @method('PUT')
    <div>
        <h1 class="text-3xl">Settings</h1>
        <p class="mt-1 text-[var(--muted)]">Configure your platform.</p>
    </div>

    @include('dashboard.admin._flash')

    @foreach ($groups as $group => $items)
        <div class="card space-y-4 p-6">
            <h2 class="text-xl capitalize">{{ $group }}</h2>
            @foreach ($items as $s)
                @if ($s['type'] === 'bool')
                    <label class="flex items-center gap-3 text-sm font-bold">
                        <input type="checkbox" name="settings[{{ $s['key'] }}]" value="1" @checked($s['value']) @disabled(! $canManage) class="h-4 w-4 accent-[var(--primary)]">
                        {{ $s['label'] }}
                    </label>
                @elseif ($s['type'] === 'color')
                    <div>
                        <label class="label">{{ $s['label'] }}</label>
                        <input type="color" name="settings[{{ $s['key'] }}]" value="{{ $s['value'] ?: '#2563ff' }}" @disabled(! $canManage) class="h-10 w-20 rounded-lg border border-[var(--border)]">
                    </div>
                @else
                    <div>
                        <label class="label">{{ $s['label'] }}</label>
                        <input name="settings[{{ $s['key'] }}]" value="{{ $s['value'] }}" @disabled(! $canManage) class="input">
                    </div>
                @endif
            @endforeach
        </div>
    @endforeach

    @if ($canManage)
        <button type="submit" class="btn-primary">Save settings</button>
    @else
        <p class="text-sm font-bold text-[var(--warning)]">You need <code>settings.manage</code> to change settings.</p>
    @endif
</form>
@endsection
