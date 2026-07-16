@extends('layouts.dashboard')
@section('title', 'Payment Gateways — LMS')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-3xl">Payment Gateways</h1>
        <p class="mt-1 text-[var(--muted)]">Enable the payment methods your academy accepts.</p>
    </div>

    @include('dashboard.admin._flash')

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @foreach ($gateways as $g)
            <div class="card flex items-center justify-between p-5">
                <div>
                    <h3 class="text-lg">{{ $g->name }}</h3>
                    <p class="text-xs font-semibold uppercase text-[var(--muted)]">{{ $g->code ?? $g->provider ?? '' }}</p>
                </div>
                <form method="POST" action="/dashboard/gateways/{{ $g->id }}/toggle">
                    @csrf
                    <button class="{{ $g->enabled ? 'btn-ghost' : 'btn-primary' }} !py-2 !px-4 text-sm">{{ $g->enabled ? 'On' : 'Off' }}</button>
                </form>
            </div>
        @endforeach
    </div>
</div>
@endsection
