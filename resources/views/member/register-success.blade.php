@extends('layouts.app')

@section('title', 'Registration Successful')

@section('body')
<div class="relative min-h-screen bg-surface px-4 py-10 sm:px-6">
    <div class="mx-auto max-w-lg">
        <div class="mb-6 text-center">
            <img src="{{ asset('images/asda-logo.png') }}" alt="ASDA Logo" class="mx-auto h-16 w-auto object-contain">
        </div>

        <div class="card overflow-hidden">
            <div class="bg-brand-green px-5 py-5 text-center text-white">
                <h1 class="font-display text-2xl font-bold">Registration submitted</h1>
                <p class="mt-1 text-sm text-white/85">Your membership request is pending admin approval.</p>
            </div>

            <div class="space-y-5 p-5 sm:p-8">
                <div class="rounded-xl bg-surface p-4 text-center">
                    <p class="text-xs font-semibold uppercase tracking-wide text-muted">Your Unique ID</p>
                    <p class="mt-1 font-display text-2xl font-bold text-brand-blue">{{ $member->unique_id }}</p>
                    <p class="mt-1 text-sm text-muted">{{ $member->displayName() }}</p>
                </div>

                <div class="flex flex-col items-center rounded-2xl border border-slate-200 bg-white p-5">
                    <p class="mb-3 text-sm font-semibold text-ink">Your membership QR code</p>
                    <img src="{{ $qrUrl }}" alt="QR code for {{ $member->unique_id }}" class="h-64 w-64 rounded-xl border border-slate-100 bg-white p-2 shadow-sm">
                    <p class="mt-3 max-w-xs text-center text-xs text-muted">
                        Scan this QR code to read your Unique ID. Save or download it for future use.
                    </p>
                </div>

                <div class="flex flex-col gap-3 sm:flex-row">
                    <a href="{{ route('member.register.qr') }}" class="btn-accent flex-1">
                        Download QR
                    </a>
                    <a href="{{ route('member.login') }}" class="btn-primary flex-1">
                        Go to sign in
                    </a>
                </div>

                <div class="rounded-xl bg-brand-orange-soft px-4 py-3 text-sm text-brand-orange">
                    After approval, sign in with your NIC as username. Default password is the first 4 digits of your NIC + @ASDA.
                </div>
            </div>
        </div>

        <p class="mt-6 text-center text-xs font-semibold text-brand-blue">
            Developed by 1920 &amp; TFBS - Department of Agriculture
        </p>
    </div>
</div>
@endsection
