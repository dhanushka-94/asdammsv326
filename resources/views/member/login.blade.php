@extends('layouts.app')

@section('title', 'Member Sign in')

@section('body')
<div class="relative min-h-screen overflow-hidden bg-surface">
    <div class="pointer-events-none absolute inset-0">
        <div class="absolute -left-24 -top-24 h-80 w-80 rounded-full bg-brand-green/10 blur-3xl"></div>
        <div class="absolute -right-16 top-1/4 h-72 w-72 rounded-full bg-brand-orange/10 blur-3xl"></div>
        <div class="absolute bottom-0 left-1/3 h-64 w-64 rounded-full bg-brand-blue/10 blur-3xl"></div>
    </div>

    <div class="relative mx-auto flex min-h-screen max-w-6xl flex-col px-4 py-8 sm:px-6 lg:px-8">
        <div class="flex flex-1 flex-col justify-center">
            <div class="grid items-center gap-10 lg:grid-cols-2 lg:gap-16">
                <div class="hidden lg:block">
                    <div class="mb-8">
                        <img src="{{ asset('images/asda-logo.png') }}" alt="ASDA Logo" class="h-28 w-auto object-contain">
                    </div>
                    <h2 class="font-display text-3xl font-bold leading-tight tracking-tight text-ink xl:text-4xl">
                        Annual Symposium of the Department of Agriculture
                    </h2>
                    <p class="mt-4 max-w-lg text-base leading-relaxed text-muted">
                        The Annual Symposium of the Department of Agriculture (ASDA) is the largest annual forum of professionals in Agriculture. Since its inception in 1999.
                    </p>
                </div>

                <div class="mx-auto w-full max-w-md">
                    <div class="mb-6 text-center lg:hidden">
                        <img src="{{ asset('images/asda-logo.png') }}" alt="ASDA Logo" class="mx-auto h-24 w-auto object-contain">
                        <p class="mt-4 text-sm leading-relaxed text-muted">
                            The Annual Symposium of the Department of Agriculture (ASDA) is the largest annual forum of professionals in Agriculture. Since its inception in 1999.
                        </p>
                    </div>

                    <div class="card p-6 sm:p-8">
                        <div class="mb-6">
                            <h1 class="font-display text-2xl font-bold text-ink">Member sign in</h1>
                            <p class="mt-1 text-sm text-muted">Use your NIC as username. Default password is the first 4 digits of your NIC + @ASDA</p>
                            <p class="mt-2 text-xs text-muted">Example: NIC 196204512345 → password 1962@ASDA. After approval, first sign in asks you to set a new password.</p>
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
                                <label for="password" class="form-label">Password</label>
                                <input id="password" type="password" name="password" required autocomplete="current-password" class="form-input" placeholder="e.g. 1962@ASDA">
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
                            <div class="mt-5 space-y-2 text-center text-sm">
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

        <footer class="mt-10 border-t border-slate-200/80 pt-5 pb-2 text-center">
            <p class="mb-2 text-xs leading-relaxed text-muted sm:text-sm lg:hidden">
                Annual Symposium of the Department of Agriculture (ASDA) - Member Management System
            </p>
            <p class="text-xs font-semibold text-brand-blue">
                Developed by 1920 &amp; TFBS - Department of Agriculture
            </p>
        </footer>
    </div>
</div>
@endsection
