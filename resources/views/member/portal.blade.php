@extends('layouts.app')

@section('title', 'Member Portal')

@section('body')
<div class="relative flex min-h-screen flex-col overflow-hidden bg-surface">
    <div class="pointer-events-none absolute inset-0">
        <div class="absolute -left-24 -top-24 h-80 w-80 rounded-full bg-brand-green/10 blur-3xl"></div>
        <div class="absolute -right-20 top-1/4 h-72 w-72 rounded-full bg-brand-orange/10 blur-3xl"></div>
        <div class="absolute bottom-0 left-1/3 h-64 w-64 rounded-full bg-brand-blue/10 blur-3xl"></div>
    </div>

    <header class="relative border-b border-slate-200/80 bg-white/90 backdrop-blur">
        <div class="mx-auto flex max-w-5xl items-center justify-between gap-4 px-4 py-4 sm:px-6">
            <div class="flex items-center gap-3">
                <img src="{{ asset('images/asda-logo.png') }}" alt="ASDA" class="h-10 w-auto object-contain">
                <div>
                    <p class="font-display text-sm font-bold text-ink">ASDA MMS</p>
                    <p class="text-xs text-muted">Member Portal</p>
                </div>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <form method="POST" action="{{ route('member.logout') }}">
                    @csrf
                    <button type="submit" class="btn-outline">Sign out</button>
                </form>
            </div>
        </div>
    </header>

    <main class="relative mx-auto w-full max-w-5xl flex-1 px-4 py-6 sm:px-6">
        @if (session('success'))
            <div class="alert-success">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="alert-error">{{ session('error') }}</div>
        @endif

        <div class="mb-8 overflow-hidden rounded-3xl border border-slate-200/80 bg-white shadow-lg">
            <div class="bg-gradient-to-br from-brand-blue via-brand-green to-brand-orange px-5 py-6 text-white sm:px-8 sm:py-8">
                <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-center">
                        @if ($member->profileImageUrl())
                            <img src="{{ $member->profileImageUrl() }}" alt="{{ $member->displayName() }}" class="h-20 w-20 rounded-2xl border-2 border-white/40 object-cover shadow-md">
                        @else
                            <div class="flex h-20 w-20 items-center justify-center rounded-2xl bg-white/15 text-2xl font-bold text-white ring-1 ring-white/25">
                                {{ strtoupper(substr($member->full_name, 0, 1)) }}
                            </div>
                        @endif
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-white/80">Welcome</p>
                            <h1 class="mt-1 font-display text-2xl font-bold tracking-tight sm:text-3xl">{{ $member->displayName() }}</h1>
                            <p class="mt-1 text-sm text-white/85">
                                {{ $member->designation?->name ?? 'Member' }}
                                @if ($member->category)
                                    · {{ $member->category->name }}
                                @endif
                            </p>
                            <p class="mt-2 text-sm font-semibold text-white">{{ $member->unique_id ?? 'ID pending approval' }}</p>
                        </div>
                    </div>

                    <div class="flex flex-wrap gap-2">
                        <a href="{{ route('member.profile') }}" class="inline-flex items-center justify-center rounded-xl bg-white/15 px-4 py-2.5 text-sm font-semibold text-white ring-1 ring-white/25 transition hover:bg-white/25">
                            View full profile
                        </a>
                        <a href="{{ route('member.profile.edit') }}" class="inline-flex items-center justify-center rounded-xl bg-white px-4 py-2.5 text-sm font-semibold text-brand-green transition hover:bg-white/90">
                            Update Profile
                        </a>
                    </div>
                </div>
            </div>

            <div class="p-5 sm:p-6">
                @if ($qrUrl && $member->unique_id)
                    <div class="rounded-2xl border border-brand-blue/15 bg-brand-blue-soft/40 p-4">
                        <div class="flex flex-col items-center gap-4 sm:flex-row sm:items-center sm:justify-between">
                            <div class="text-center sm:text-left">
                                <p class="text-xs font-semibold uppercase tracking-wide text-brand-blue">Membership QR</p>
                                <p class="mt-1 font-display text-xl font-bold text-ink">{{ $member->unique_id }}</p>
                                <p class="mt-1 text-sm text-muted">Scan to read your Unique ID. Keep this QR ready for event check-in.</p>
                                <a href="{{ route('member.profile.qr') }}" class="btn-accent mt-3">Download QR</a>
                            </div>
                            <img src="{{ $qrUrl }}" alt="QR code for {{ $member->unique_id }}" class="h-36 w-36 rounded-xl border border-slate-100 bg-white p-2 shadow-sm sm:h-40 sm:w-40">
                        </div>
                    </div>
                @else
                    <div class="rounded-2xl border border-dashed border-slate-300 bg-surface p-4">
                        <p class="text-sm font-semibold text-ink">Membership QR not available yet</p>
                        <p class="mt-1 text-sm text-muted">Your Unique ID / QR will appear here once issued.</p>
                    </div>
                @endif
            </div>
        </div>

        <div class="mb-4">
            <h2 class="font-display text-xl font-bold text-ink">Quick actions</h2>
            <p class="mt-1 text-sm text-muted">Choose a feature below to continue.</p>
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            @if ($hasEvents)
                <a href="{{ route('member.events.index') }}" class="group relative overflow-hidden rounded-3xl border border-brand-blue/20 bg-gradient-to-br from-brand-blue to-brand-blue-dark p-6 text-white shadow-lg shadow-brand-blue/20 transition hover:-translate-y-0.5 hover:shadow-xl sm:min-h-[220px] sm:p-8">
                    <div class="absolute -right-8 -top-8 h-28 w-28 rounded-full bg-white/10 blur-2xl transition group-hover:bg-white/15"></div>
                    <div class="relative flex h-full flex-col">
                        <div class="flex items-center justify-between gap-3">
                            <span class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-white/15 ring-1 ring-white/20">
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            </span>
                            <span class="rounded-full bg-white/20 px-2.5 py-1 text-[11px] font-bold uppercase tracking-wide">Active</span>
                        </div>
                        <p class="mt-5 text-xs font-semibold uppercase tracking-[0.18em] text-white/70">Events</p>
                        <p class="mt-2 font-display text-2xl font-bold tracking-tight sm:text-3xl">Event Register</p>
                        <p class="mt-2 text-sm text-white/80">Browse ASDA events and complete registration.</p>
                        <span class="mt-auto pt-6 text-sm font-semibold text-white/90">Open page →</span>
                    </div>
                </a>
            @else
                <div class="relative overflow-hidden rounded-3xl border border-slate-200 bg-gradient-to-br from-slate-200 via-slate-100 to-white p-6 text-muted opacity-80 grayscale sm:min-h-[220px] sm:p-8" aria-disabled="true">
                    <div class="relative flex h-full flex-col">
                        <div class="flex items-center justify-between gap-3">
                            <span class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-300/70 text-slate-500">
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            </span>
                            <span class="rounded-full bg-slate-300 px-2.5 py-1 text-[11px] font-bold uppercase tracking-wide text-slate-600">No Events Available</span>
                        </div>
                        <p class="mt-5 text-xs font-semibold uppercase tracking-[0.18em]">Events</p>
                        <p class="mt-2 font-display text-2xl font-bold tracking-tight text-ink sm:text-3xl">Event Register</p>
                        <p class="mt-2 text-sm">No events available right now.</p>
                        <span class="mt-auto pt-6 text-sm font-semibold">No Events Available</span>
                    </div>
                </div>
            @endif

            <a href="{{ route('member.profile') }}" class="group relative overflow-hidden rounded-3xl border border-brand-green/20 bg-gradient-to-br from-brand-green to-brand-green-dark p-6 text-white shadow-lg shadow-brand-green/20 transition hover:-translate-y-0.5 hover:shadow-xl sm:min-h-[220px] sm:p-8">
                <div class="absolute -right-8 -top-8 h-28 w-28 rounded-full bg-white/10 blur-2xl transition group-hover:bg-white/15"></div>
                <div class="relative flex h-full flex-col">
                    <div class="flex items-center justify-between gap-3">
                        <span class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-white/15 ring-1 ring-white/20">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        </span>
                        <span class="rounded-full bg-white/20 px-2.5 py-1 text-[11px] font-bold uppercase tracking-wide">Active</span>
                    </div>
                    <p class="mt-5 text-xs font-semibold uppercase tracking-[0.18em] text-white/70">Profile</p>
                    <p class="mt-2 font-display text-2xl font-bold tracking-tight sm:text-3xl">My Profile</p>
                    <p class="mt-2 text-sm text-white/80">View your membership details and QR code.</p>
                    <span class="mt-auto pt-6 text-sm font-semibold text-white/90">Open page →</span>
                </div>
            </a>

            @if ($hasInvitationLetter)
                <a href="{{ route('member.invitations.letter') }}" class="group relative overflow-hidden rounded-3xl border border-brand-orange/20 bg-gradient-to-br from-brand-orange to-brand-orange-dark p-6 text-white shadow-lg shadow-brand-orange/20 transition hover:-translate-y-0.5 hover:shadow-xl sm:min-h-[220px] sm:p-8">
                    <div class="absolute -right-8 -top-8 h-28 w-28 rounded-full bg-white/10 blur-2xl transition group-hover:bg-white/15"></div>
                    <div class="relative flex h-full flex-col">
                        <div class="flex items-center justify-between gap-3">
                            <span class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-white/15 ring-1 ring-white/20">
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            </span>
                            <span class="rounded-full bg-white/20 px-2.5 py-1 text-[11px] font-bold uppercase tracking-wide">Active</span>
                        </div>
                        <p class="mt-5 text-xs font-semibold uppercase tracking-[0.18em] text-white/70">Download</p>
                        <p class="mt-2 font-display text-2xl font-bold tracking-tight sm:text-3xl">Invitation Letter</p>
                        <p class="mt-2 text-sm text-white/80">Open your invitation letter downloads.</p>
                        <span class="mt-auto pt-6 text-sm font-semibold text-white/90">Open page →</span>
                    </div>
                </a>
            @else
                <div class="relative overflow-hidden rounded-3xl border border-slate-200 bg-gradient-to-br from-slate-200 via-slate-100 to-white p-6 text-muted opacity-80 grayscale sm:min-h-[220px] sm:p-8" aria-disabled="true">
                    <div class="relative flex h-full flex-col">
                        <div class="flex items-center justify-between gap-3">
                            <span class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-300/70 text-slate-500">
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            </span>
                            <span class="rounded-full bg-slate-300 px-2.5 py-1 text-[11px] font-bold uppercase tracking-wide text-slate-600">No Invitation Available</span>
                        </div>
                        <p class="mt-5 text-xs font-semibold uppercase tracking-[0.18em]">Download</p>
                        <p class="mt-2 font-display text-2xl font-bold tracking-tight text-ink sm:text-3xl">Invitation Letter</p>
                        <p class="mt-2 text-sm">No invitation letter available yet.</p>
                        <span class="mt-auto pt-6 text-sm font-semibold">No Invitation Available</span>
                    </div>
                </div>
            @endif

            @if ($hasInvitationCard)
                <a href="{{ route('member.invitations.card') }}" class="group relative overflow-hidden rounded-3xl border border-brand-blue/15 bg-gradient-to-br from-[#2a4a73] via-brand-blue to-brand-orange p-6 text-white shadow-lg shadow-brand-blue/15 transition hover:-translate-y-0.5 hover:shadow-xl sm:min-h-[220px] sm:p-8">
                    <div class="absolute -right-8 -top-8 h-28 w-28 rounded-full bg-white/10 blur-2xl transition group-hover:bg-white/15"></div>
                    <div class="relative flex h-full flex-col">
                        <div class="flex items-center justify-between gap-3">
                            <span class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-white/15 ring-1 ring-white/20">
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                            </span>
                            <span class="rounded-full bg-white/20 px-2.5 py-1 text-[11px] font-bold uppercase tracking-wide">Active</span>
                        </div>
                        <p class="mt-5 text-xs font-semibold uppercase tracking-[0.18em] text-white/70">Download</p>
                        <p class="mt-2 font-display text-2xl font-bold tracking-tight sm:text-3xl">Invitation Card</p>
                        <p class="mt-2 text-sm text-white/80">Open your invitation card downloads.</p>
                        <span class="mt-auto pt-6 text-sm font-semibold text-white/90">Open page →</span>
                    </div>
                </a>
            @else
                <div class="relative overflow-hidden rounded-3xl border border-slate-200 bg-gradient-to-br from-slate-200 via-slate-100 to-white p-6 text-muted opacity-80 grayscale sm:min-h-[220px] sm:p-8" aria-disabled="true">
                    <div class="relative flex h-full flex-col">
                        <div class="flex items-center justify-between gap-3">
                            <span class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-300/70 text-slate-500">
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                            </span>
                            <span class="rounded-full bg-slate-300 px-2.5 py-1 text-[11px] font-bold uppercase tracking-wide text-slate-600">No Card Available</span>
                        </div>
                        <p class="mt-5 text-xs font-semibold uppercase tracking-[0.18em]">Download</p>
                        <p class="mt-2 font-display text-2xl font-bold tracking-tight text-ink sm:text-3xl">Invitation Card</p>
                        <p class="mt-2 text-sm">No invitation card available yet.</p>
                        <span class="mt-auto pt-6 text-sm font-semibold">No Card Available</span>
                    </div>
                </div>
            @endif
        </div>
    </main>

    <x-member-footer />
</div>
@endsection
