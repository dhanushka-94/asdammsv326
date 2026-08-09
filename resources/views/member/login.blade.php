@extends('layouts.app')

@section('title', 'Member Sign In | ASDA')
@section('meta_description', 'Sign in to the ASDA Member Portal for the Annual Symposium of the Department of Agriculture. Access membership profile, events, and registration services in Sri Lanka.')
@section('meta_robots', 'index, follow')
@section('canonical', route('member.login'))

@push('head')
    <meta name="keywords" content="ASDA, Annual Symposium of the Department of Agriculture, member login, Department of Agriculture Sri Lanka, agricultural professionals, ASDA membership">
    <meta name="author" content="Department of Agriculture">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="ASDA Member Management System">
    <meta property="og:title" content="Member Sign In | Annual Symposium of the Department of Agriculture (ASDA)">
    <meta property="og:description" content="Sign in to the ASDA Member Portal. Access your membership profile, events, and registration services for the Annual Symposium of the Department of Agriculture.">
    <meta property="og:url" content="{{ route('member.login') }}">
    <meta property="og:image" content="{{ asset('images/asda-logo.png') }}">
    <meta property="og:locale" content="en_LK">
    <meta name="twitter:card" content="summary">
    <meta name="twitter:title" content="Member Sign In | ASDA">
    <meta name="twitter:description" content="Sign in to the ASDA Member Portal for the Annual Symposium of the Department of Agriculture.">
    <meta name="twitter:image" content="{{ asset('images/asda-logo.png') }}">
    <script type="application/ld+json">
        {!! json_encode([
            '@context' => 'https://schema.org',
            '@graph' => [
                [
                    '@type' => 'Organization',
                    'name' => 'Annual Symposium of the Department of Agriculture',
                    'alternateName' => 'ASDA',
                    'url' => route('member.login'),
                    'logo' => asset('images/asda-logo.png'),
                    'description' => 'The Annual Symposium of the Department of Agriculture (ASDA), established in 1999, is a leading forum for agricultural professionals in Sri Lanka.',
                ],
                [
                    '@type' => 'WebSite',
                    'name' => 'ASDA Member Portal',
                    'url' => route('member.login'),
                    'publisher' => [
                        '@type' => 'Organization',
                        'name' => 'Department of Agriculture',
                    ],
                ],
                [
                    '@type' => 'WebPage',
                    'name' => 'ASDA Member Sign In',
                    'description' => 'Sign in to the ASDA Member Portal for the Annual Symposium of the Department of Agriculture.',
                    'url' => route('member.login'),
                    'isPartOf' => [
                        '@type' => 'WebSite',
                        'name' => 'ASDA Member Portal',
                        'url' => route('member.login'),
                    ],
                ],
            ],
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!}
    </script>
@endpush

@section('body')
@php
    $asdaTitle = '<span class="asda-mark">A</span>nnual <span class="asda-mark">S</span>ymposium of the <span class="asda-mark">D</span>epartment of <span class="asda-mark">A</span>griculture';
    $asdaAcronym = '<span class="asda-mark">A</span><span class="asda-mark">S</span><span class="asda-mark">D</span><span class="asda-mark">A</span>';
@endphp

<div id="member-login-splash" class="login-splash" role="status" aria-live="polite" aria-label="Loading ASDA member portal">
    <div class="login-splash-bg" aria-hidden="true">
        <div class="login-bg-grid"></div>
        <div class="login-bg-orb login-bg-orb-a"></div>
        <div class="login-bg-orb login-bg-orb-b"></div>
        <div class="login-bg-orb login-bg-orb-c"></div>
    </div>

    <div class="login-splash-inner">
        <div class="login-splash-mark" aria-hidden="true">
            <span class="login-splash-halo"></span>
            <span class="login-splash-ring"></span>
            <span class="login-splash-ring-b"></span>
            <div class="login-splash-logo-wrap">
                <img
                    src="{{ asset('images/asda-logo.png') }}"
                    alt="ASDA Logo"
                    class="login-splash-logo"
                    data-login-splash-logo
                >
            </div>
        </div>

        <p class="login-splash-brand" aria-label="ASDA">
            <span class="asda-mark login-splash-letter" style="--i:0">A</span>
            <span class="asda-mark login-splash-letter" style="--i:1">S</span>
            <span class="asda-mark login-splash-letter" style="--i:2">D</span>
            <span class="asda-mark login-splash-letter" style="--i:3">A</span>
        </p>
        <p class="login-splash-title">Annual Symposium of the Department of Agriculture</p>
        <p class="login-splash-sub">Member Portal</p>

        <div class="login-splash-bar" aria-hidden="true">
            <span></span>
        </div>
        <p class="login-splash-status">Preparing your secure sign-in…</p>
    </div>
</div>

<div class="login-shell">
    <div class="login-bg" aria-hidden="true">
        <div class="login-bg-grid"></div>
        <div class="login-bg-orb login-bg-orb-a"></div>
        <div class="login-bg-orb login-bg-orb-b"></div>
        <div class="login-bg-orb login-bg-orb-c"></div>
    </div>

    <div class="relative mx-auto flex min-h-dvh w-full max-w-6xl flex-col px-4 py-6 sm:px-6 sm:py-8 lg:px-8 lg:py-10">
        <div class="flex flex-1 flex-col justify-center py-4 sm:py-6">
            <div class="grid items-center gap-8 sm:gap-10 lg:grid-cols-2 lg:gap-16">
                <div class="login-hero hidden lg:block">
                    <div class="mb-8">
                        <img src="{{ asset('images/asda-logo.png') }}" alt="ASDA Logo" class="h-28 w-auto object-contain">
                    </div>
                    <p class="mb-3 text-xs font-semibold uppercase tracking-[0.18em] text-brand-green">
                        ASDA Member Portal
                    </p>
                    <h2 class="font-display text-3xl font-bold leading-tight tracking-tight text-ink xl:text-4xl">
                        {!! $asdaTitle !!}
                    </h2>
                    <p class="mt-4 max-w-lg text-base leading-relaxed text-muted">
                        The {!! $asdaTitle !!}
                        ({!! $asdaAcronym !!}),
                        established in 1999, is a leading forum for agricultural professionals to share knowledge, discuss challenges, and develop strategies to strengthen agriculture and national food security in Sri Lanka.
                    </p>
                </div>

                <div class="login-panel mx-auto w-full max-w-md">
                    <div class="mb-5 text-center sm:mb-6 lg:hidden">
                        <img src="{{ asset('images/asda-logo.png') }}" alt="ASDA Logo" class="mx-auto h-20 w-auto object-contain sm:h-24">
                        <h2 class="mt-4 font-display text-xl font-bold leading-snug text-ink sm:text-2xl">
                            {!! $asdaTitle !!}
                        </h2>
                        <p class="mt-2 text-sm leading-relaxed text-muted">
                            Established in 1999 — a leading forum for agricultural professionals to strengthen agriculture and national food security in Sri Lanka.
                        </p>
                    </div>

                    <div class="card p-5 sm:p-8">
                        <div class="mb-5 sm:mb-6">
                            <h1 class="font-display text-xl font-bold text-ink sm:text-2xl">Member sign in</h1>
                            <p class="mt-1 text-sm text-muted">Sign in with your NIC number and password.</p>
                        </div>

                        @if (session('status'))
                            <div class="alert-success mb-5">
                                <span>{{ session('status') }}</span>
                            </div>
                        @endif

                        @if (session('success'))
                            <div class="alert-success mb-5">
                                <span>{{ session('success') }}</span>
                            </div>
                        @endif

                        @if (session('error'))
                            <div class="alert-error mb-5">
                                <span>{{ session('error') }}</span>
                            </div>
                        @endif

                        @if ($errors->any())
                            <div class="alert-error mb-5">
                                <span>{{ $errors->first() }}</span>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('member.login.store') }}" class="space-y-4">
                            @csrf

                            <div>
                                <label for="nic" class="form-label">NIC number</label>
                                <input id="nic" type="text" name="nic" value="{{ old('nic') }}" required autofocus autocomplete="username" class="form-input" placeholder="123456789V or 199012345678" maxlength="12" data-format="sl-nic" inputmode="text">
                            </div>

                            <div>
                                <div class="mb-1.5 flex items-center justify-between gap-3">
                                    <label for="password" class="form-label mb-0">Password</label>
                                    <button
                                        type="button"
                                        data-open-help-modal
                                        class="text-xs font-semibold text-brand-blue hover:text-brand-green"
                                    >
                                        Forgot password?
                                    </button>
                                </div>
                                <input id="password" type="password" name="password" required autocomplete="current-password" class="form-input" placeholder="Enter your password">
                                <p class="mt-1.5 text-xs text-muted">
                                    Hint: first 4 NIC digits + <span class="font-semibold text-ink">@ASDA</span>
                                    (e.g. <span class="font-semibold text-ink">1962@ASDA</span>).
                                </p>
                            </div>

                            <div class="flex items-center justify-between gap-3">
                                <label class="flex items-center gap-2 text-sm text-muted">
                                    <input type="checkbox" name="remember" value="1" @checked(old('remember')) class="h-4 w-4 rounded border-slate-300 text-brand-green focus:ring-brand-green">
                                    Remember me
                                </label>
                            </div>

                            <button type="submit" class="btn-primary w-full py-3">
                                Sign in to profile
                            </button>
                        </form>

                        @if (\App\Support\AppSettings::memberRegistrationEnabled())
                            <div class="mt-5 space-y-2 border-t border-slate-100 pt-5 text-center text-sm">
                                <p class="text-muted">New member?</p>
                                <a href="{{ route('member.register') }}" class="font-semibold text-brand-orange hover:text-brand-orange-dark">
                                    Register for membership
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <footer class="mt-6 border-t border-slate-200/80 pt-4 pb-1 text-center sm:mt-8 sm:pt-5">
            <p class="text-xs font-semibold text-brand-blue sm:text-sm">
                Developed by 1920 &amp; TFBS - Department of Agriculture
            </p>
            <p class="mt-1.5 text-[11px] leading-relaxed text-muted sm:text-xs">
                &copy; {{ now()->year }} Annual Symposium of the Department of Agriculture
                ({!! $asdaAcronym !!}).
                All rights reserved.
            </p>
        </footer>
    </div>
</div>

<div id="member-help-modal" class="fixed inset-0 z-[100] hidden items-center justify-center p-4" aria-hidden="true">
    <div data-help-modal-backdrop class="absolute inset-0 bg-brand-blue-dark/50 backdrop-blur-sm"></div>

    <div
        role="dialog"
        aria-modal="true"
        aria-labelledby="member-help-modal-title"
        class="relative z-10 w-full max-w-md rounded-2xl border border-slate-200 bg-white p-5 shadow-xl sm:p-6"
    >
        <div class="mb-4 flex h-11 w-11 items-center justify-center rounded-xl bg-brand-blue/10 text-brand-blue">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
            </svg>
        </div>

        <h2 id="member-help-modal-title" class="font-display text-lg font-bold text-ink">Need assistance?</h2>
        <p class="mt-2 text-sm leading-relaxed text-muted">
            If you have forgotten your password, or are experiencing any other issues with the online registration system, please contact the Help Desk.
        </p>

        <div class="mt-5 rounded-xl border border-slate-200 bg-surface px-4 py-4">
            <p class="text-xs font-semibold uppercase tracking-wide text-muted">Help Desk</p>
            <div class="mt-3 space-y-2">
                <a href="tel:0703731949" class="flex items-center gap-2 text-sm font-semibold text-brand-blue hover:text-brand-green">
                    <span class="text-muted">Call</span>
                    <span>070 373 1949</span>
                </a>
                <a href="tel:0716661797" class="flex items-center gap-2 text-sm font-semibold text-brand-blue hover:text-brand-green">
                    <span class="text-muted">Call</span>
                    <span>071 666 1797</span>
                </a>
            </div>
        </div>

        <div class="mt-6 flex justify-end">
            <button type="button" data-close-help-modal class="btn-primary">
                Close
            </button>
        </div>
    </div>
</div>
@endsection
