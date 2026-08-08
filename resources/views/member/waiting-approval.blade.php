@extends('layouts.app')

@section('title', 'Waiting Approval')

@section('body')
<div class="relative min-h-screen overflow-hidden bg-surface">
    <div class="pointer-events-none absolute inset-0">
        <div class="absolute -left-20 -top-20 h-72 w-72 rounded-full bg-brand-orange/10 blur-3xl"></div>
        <div class="absolute -right-16 bottom-10 h-72 w-72 rounded-full bg-brand-blue/10 blur-3xl"></div>
        <div class="absolute left-1/3 top-1/3 h-64 w-64 rounded-full bg-brand-green/10 blur-3xl"></div>
    </div>

    <div class="relative mx-auto flex min-h-screen max-w-3xl flex-col px-4 py-8 sm:px-6">
        <header class="mb-6 flex items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <img src="{{ asset('images/asda-logo.png') }}" alt="ASDA" class="h-12 w-auto object-contain">
                <div>
                    <p class="font-display text-sm font-bold text-ink">ASDA MMS</p>
                    <p class="text-xs text-muted">Waiting Approval</p>
                </div>
            </div>
            <form method="POST" action="{{ route('member.logout') }}">
                @csrf
                <button type="submit" class="btn-outline">Sign out</button>
            </form>
        </header>

        <div class="card overflow-hidden" id="waiting-approval-card"
             data-status-url="{{ route('member.waiting-approval.status') }}"
             data-poll-ms="8000">
            @if (session('success'))
                <div class="alert-success m-4 mb-0">{{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="alert-error m-4 mb-0">{{ session('error') }}</div>
            @endif

            <div class="bg-gradient-to-r from-brand-blue via-brand-green to-brand-orange px-5 py-6 text-white sm:px-8">
                <p class="text-sm text-white/80">Welcome,</p>
                <h1 class="font-display text-2xl font-bold sm:text-3xl">{{ $member->displayName() }}</h1>
                <p class="mt-1 text-sm text-white/85">{{ $member->designation?->name }} · {{ $member->unique_id }}</p>
            </div>

            <div class="space-y-5 p-5 sm:p-8">
                @if ($member->registration_status === 'pending')
                    <div class="rounded-2xl border border-brand-orange/20 bg-brand-orange-soft p-5 text-center">
                        <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-brand-orange/15">
                            <svg class="h-7 w-7 animate-spin text-brand-orange" style="animation-duration: 2.2s" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-90" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                            </svg>
                        </div>
                        <h2 class="font-display text-xl font-bold text-brand-orange">Waiting for approval</h2>
                        <p class="mt-2 text-sm leading-relaxed text-brand-orange/90">
                            Your registration is under review. An ASDA administrator will approve your membership soon.
                            This page updates automatically when you are approved.
                        </p>
                        <p class="mt-3 text-xs font-semibold uppercase tracking-wide text-brand-orange/80" id="waiting-live-label">
                            Waiting for approval…
                        </p>
                    </div>
                @elseif ($member->registration_status === 'rejected')
                    <div class="rounded-2xl border border-red-200 bg-red-50 p-5 text-center">
                        <h2 class="font-display text-xl font-bold text-red-700">Registration rejected</h2>
                        <p class="mt-2 text-sm text-red-700/90">
                            {{ $member->rejection_reason ?: 'Please contact ASDA administration for more information.' }}
                        </p>
                    </div>
                @else
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5 text-center">
                        <h2 class="font-display text-xl font-bold text-ink">Membership inactive</h2>
                        <p class="mt-2 text-sm text-muted">
                            Your account is approved but currently inactive. Please contact ASDA administration.
                        </p>
                    </div>
                @endif

                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="rounded-xl bg-surface p-4">
                        <p class="text-xs font-semibold uppercase tracking-wide text-muted">Unique ID</p>
                        <p class="mt-1 font-display text-lg font-bold text-brand-blue">{{ $member->unique_id }}</p>
                    </div>
                    <div class="rounded-xl bg-surface p-4">
                        <p class="text-xs font-semibold uppercase tracking-wide text-muted">NIC</p>
                        <p class="mt-1 font-semibold text-ink">{{ $member->nic }}</p>
                    </div>
                    <div class="rounded-xl bg-surface p-4">
                        <p class="text-xs font-semibold uppercase tracking-wide text-muted">Registration</p>
                        <p class="mt-2">
                            <span class="{{ $member->registration_status === 'pending' ? 'badge-orange' : ($member->registration_status === 'rejected' ? 'badge-orange' : 'badge-green') }}">
                                {{ ucfirst($member->registration_status) }}
                            </span>
                        </p>
                    </div>
                    <div class="rounded-xl bg-surface p-4">
                        <p class="text-xs font-semibold uppercase tracking-wide text-muted">Account status</p>
                        <p class="mt-2">
                            <span class="{{ $member->status === 'active' ? 'badge-green' : 'badge-muted' }}">{{ ucfirst($member->status) }}</span>
                        </p>
                    </div>
                </div>

                @if ($qrUrl)
                    <div class="flex flex-col items-center rounded-2xl border border-slate-200 bg-white p-5">
                        <p class="mb-3 text-sm font-semibold text-ink">Your membership QR</p>
                        <img src="{{ $qrUrl }}" alt="QR {{ $member->unique_id }}" class="h-48 w-48 rounded-xl border border-slate-100 p-2">
                        <a href="{{ route('member.waiting-approval.qr') }}" class="btn-accent mt-4">Download QR</a>
                    </div>
                @endif

                <button type="button" id="waiting-refresh" class="btn-secondary w-full">
                    Check approval status
                </button>
            </div>
        </div>

        <p class="mt-6 text-center text-xs font-semibold text-brand-blue">
            Developed by 1920 &amp; TFBS - DOA
        </p>
    </div>
</div>
@endsection
