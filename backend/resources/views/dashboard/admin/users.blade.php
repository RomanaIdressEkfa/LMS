@extends('layouts.dashboard')
@section('title', 'Users — LMS')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-3xl">Users</h1>
        <p class="mt-1 text-[var(--muted)]">Manage accounts, roles and access.</p>
    </div>

    @include('dashboard.admin._flash')

    {{-- Filters --}}
    <form method="GET" class="card flex flex-wrap items-center gap-3 p-4">
        <input name="search" value="{{ $search }}" class="input max-w-xs" placeholder="Search name or email…">
        <select name="role" class="input max-w-[200px]">
            <option value="">All roles</option>
            @foreach ($roles as $r)
                <option value="{{ $r }}" @selected($roleFilter === $r)>{{ $r }}</option>
            @endforeach
        </select>
        <button class="btn-primary">Filter</button>
    </form>

    <div class="card overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead class="border-b border-[var(--border)] text-xs font-bold uppercase tracking-wide text-[var(--muted)]">
                <tr>
                    <th class="p-4">User</th>
                    <th class="p-4">Role</th>
                    <th class="p-4">Status</th>
                    <th class="p-4">Joined</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[var(--border)]">
                @foreach ($users as $u)
                    <tr>
                        <td class="p-4">
                            <p class="font-bold text-[var(--foreground)]">{{ $u->name }}</p>
                            <p class="text-xs text-[var(--muted)]">{{ $u->email }}</p>
                        </td>
                        <td class="p-4">
                            <form method="POST" action="/dashboard/users/{{ $u->id }}/role" class="flex items-center gap-2">
                                @csrf
                                <select name="role" class="input !py-1.5 !px-2 max-w-[150px] text-sm" onchange="this.form.submit()">
                                    @foreach ($roles as $r)
                                        <option value="{{ $r }}" @selected($u->roles->pluck('name')->contains($r))>{{ $r }}</option>
                                    @endforeach
                                </select>
                            </form>
                        </td>
                        <td class="p-4">
                            <span class="pill {{ $u->status === 'active' ? 'bg-[var(--success)]/15 text-[var(--success)]' : 'bg-[var(--danger)]/15 text-[var(--danger)]' }}">{{ $u->status }}</span>
                        </td>
                        <td class="p-4 text-[var(--muted)]">
                            <div class="flex items-center gap-3">
                                <span>{{ $u->created_at->format('M j, Y') }}</span>
                                <form method="POST" action="/dashboard/users/{{ $u->id }}/status">
                                    @csrf
                                    <button class="text-xs font-bold {{ $u->status === 'active' ? 'text-[var(--danger)]' : 'text-[var(--success)]' }} hover:underline">
                                        {{ $u->status === 'active' ? 'Suspend' : 'Activate' }}
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{ $users->links() }}
</div>
@endsection
