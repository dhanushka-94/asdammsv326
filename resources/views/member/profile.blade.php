@extends('layouts.app')

@section('title', 'My Profile')

@section('body')
<div class="flex min-h-screen flex-col bg-surface">
    <header class="border-b border-slate-200 bg-white">
        <div class="mx-auto flex max-w-5xl items-center justify-between gap-4 px-4 py-4 sm:px-6">
            <div class="flex items-center gap-3">
                <img src="{{ asset('images/asda-logo.png') }}" alt="ASDA" class="h-10 w-auto object-contain">
                <div>
                    <p class="font-display text-sm font-bold text-ink">ASDA MMS</p>
                    <p class="text-xs text-muted">Member Profile</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('member.home') }}" class="btn-outline">Portal home</a>
                <a href="{{ route('member.profile.edit') }}" class="btn-secondary">Update Profile</a>
                <form method="POST" action="{{ route('member.logout') }}">
                    @csrf
                    <button type="submit" class="btn-outline">Sign out</button>
                </form>
            </div>
        </div>
    </header>

    <main class="mx-auto w-full max-w-5xl flex-1 px-4 py-6 sm:px-6">
        @if (session('success'))
            <div class="alert-success">{{ session('success') }}</div>
        @endif

        <a href="{{ route('member.profile.edit') }}" class="mb-5 flex items-center justify-between gap-4 rounded-2xl border border-brand-green/20 bg-gradient-to-r from-brand-green to-brand-green-dark px-5 py-4 text-white shadow-md shadow-brand-green/15 transition hover:-translate-y-0.5 hover:shadow-lg sm:px-6">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-white/75">Profile</p>
                <p class="mt-1 font-display text-lg font-bold sm:text-xl">Update Profile</p>
                <p class="mt-1 text-sm text-white/85">Edit contact details, organization info, and password.</p>
            </div>
            <span class="shrink-0 rounded-xl bg-white/15 px-3 py-2 text-sm font-semibold ring-1 ring-white/20">Open →</span>
        </a>

        <div class="card overflow-hidden">
            <div class="bg-gradient-to-r from-brand-blue via-brand-green to-brand-orange px-5 py-8 sm:px-8">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center">
                    @if ($member->profileImageUrl())
                        <img src="{{ $member->profileImageUrl() }}" alt="{{ $member->displayName() }}" class="h-20 w-20 rounded-2xl border-2 border-white/40 object-cover">
                    @else
                        <div class="flex h-20 w-20 items-center justify-center rounded-2xl bg-white/15 text-2xl font-bold text-white">
                            {{ strtoupper(substr($member->full_name, 0, 1)) }}
                        </div>
                    @endif
                    <div class="text-white">
                        <h1 class="font-display text-2xl font-bold">{{ $member->displayName() }}</h1>
                        <p class="text-white/85">{{ $member->designation?->name ?? 'Member' }}@if($member->category) · {{ $member->category->name }}@endif</p>
                        <p class="mt-1 text-sm font-semibold text-white/90">{{ $member->unique_id ?? 'ID pending approval' }}</p>
                    </div>
                </div>
            </div>

            <div class="grid gap-4 p-5 sm:grid-cols-2 sm:p-8">
                @if ($qrUrl && $member->unique_id)
                    <div class="rounded-xl border border-slate-200 bg-white p-4 sm:col-span-2">
                        <div class="flex flex-col items-center gap-4 sm:flex-row sm:items-center sm:justify-between">
                            <div class="text-center sm:text-left">
                                <p class="text-xs font-semibold uppercase tracking-wide text-muted">Membership QR</p>
                                <p class="mt-1 font-display text-xl font-bold text-brand-blue">{{ $member->unique_id }}</p>
                                <p class="mt-1 text-sm text-muted">Scan to read your Unique ID</p>
                                <a href="{{ route('member.profile.qr') }}" class="btn-accent mt-3">Download QR</a>
                            </div>
                            <img src="{{ $qrUrl }}" alt="QR code for {{ $member->unique_id }}" class="h-40 w-40 rounded-xl border border-slate-100 bg-white p-2 shadow-sm">
                        </div>
                    </div>
                @endif

                <div class="rounded-xl bg-surface p-4">
                    <p class="text-xs font-semibold uppercase tracking-wide text-muted">NIC</p>
                    <p class="mt-1 font-semibold text-ink">{{ $member->nic }}</p>
                </div>
                <x-member-age :member="$member" />
                <div class="rounded-xl bg-surface p-4">
                    <p class="text-xs font-semibold uppercase tracking-wide text-muted">Status</p>
                    <p class="mt-2">
                        <span class="{{ $member->status === 'active' ? 'badge-green' : 'badge-muted' }}">{{ ucfirst($member->status) }}</span>
                        <span class="{{ $member->registration_status === 'approved' ? 'badge-blue' : 'badge-orange' }} ml-1">{{ ucfirst($member->registration_status) }}</span>
                    </p>
                </div>
                <div class="rounded-xl bg-surface p-4">
                    <p class="text-xs font-semibold uppercase tracking-wide text-muted">Mobile 1</p>
                    <p class="mt-1 font-semibold text-ink">{{ $member->mobile_1 }}</p>
                </div>
                <div class="rounded-xl bg-surface p-4">
                    <p class="text-xs font-semibold uppercase tracking-wide text-muted">Mobile 2</p>
                    <p class="mt-1 font-semibold text-ink">{{ $member->mobile_2 ?: '—' }}</p>
                </div>
                <div class="rounded-xl bg-surface p-4">
                    <p class="text-xs font-semibold uppercase tracking-wide text-muted">WhatsApp</p>
                    <p class="mt-1 font-semibold text-ink">{{ $member->whatsapp ?: '—' }}</p>
                </div>
                <div class="rounded-xl bg-surface p-4">
                    <p class="text-xs font-semibold uppercase tracking-wide text-muted">Office telephone</p>
                    <p class="mt-1 font-semibold text-ink">{{ $member->office_telephone ?: '—' }}</p>
                </div>
                <div class="rounded-xl bg-surface p-4 sm:col-span-2">
                    <p class="text-xs font-semibold uppercase tracking-wide text-muted">Email</p>
                    <p class="mt-1 font-semibold text-ink">{{ $member->email ?: '—' }}</p>
                </div>
                <div class="rounded-xl bg-surface p-4">
                    <p class="text-xs font-semibold uppercase tracking-wide text-muted">Institute</p>
                    <p class="mt-1 font-semibold text-ink">{{ $member->institute ?: '—' }}</p>
                </div>
                <div class="rounded-xl bg-surface p-4">
                    <p class="text-xs font-semibold uppercase tracking-wide text-muted">Sub-institute</p>
                    <p class="mt-1 font-semibold text-ink">{{ $member->sub_institute ?: '—' }}</p>
                </div>
                <div class="rounded-xl bg-surface p-4">
                    <p class="text-xs font-semibold uppercase tracking-wide text-muted">Section</p>
                    <p class="mt-1 font-semibold text-ink">{{ $member->section ?: '—' }}</p>
                </div>
                <div class="rounded-xl bg-surface p-4 sm:col-span-2">
                    <p class="text-xs font-semibold uppercase tracking-wide text-muted">Address</p>
                    <p class="mt-1 font-semibold text-ink">{{ $member->address ?: '—' }}</p>
                </div>
            </div>
        </div>
    </main>

    <x-member-footer />
</div>
@endsection
