@extends('layouts.app')

@section('title', 'Reset password')
@section('meta_robots', 'noindex, nofollow, noarchive, nosnippet')

@section('body')
<div class="relative min-h-screen overflow-hidden bg-surface">
    <div class="pointer-events-none absolute inset-0">
        <div class="absolute -left-24 -top-24 h-80 w-80 rounded-full bg-brand-green/10 blur-3xl"></div>
        <div class="absolute -right-16 top-1/4 h-72 w-72 rounded-full bg-brand-orange/10 blur-3xl"></div>
        <div class="absolute bottom-0 left-1/3 h-64 w-64 rounded-full bg-brand-blue/10 blur-3xl"></div>
    </div>

    <div class="relative mx-auto flex min-h-screen max-w-md flex-col justify-center px-4 py-10 sm:px-6">
        <div class="mb-6 text-center">
            <img src="{{ asset('images/asda-logo.png') }}" alt="ASDA Logo" class="mx-auto h-20 w-auto object-contain">
        </div>

        <div class="card p-6 sm:p-8">
            <div class="mb-6">
                <h1 class="font-display text-2xl font-bold text-ink">Reset password</h1>
                <p class="mt-1 text-sm text-muted">Choose a new password for your account.</p>
            </div>

            @if ($errors->any())
                <div class="alert-error mb-5">
                    <div>
                        <ul class="list-disc pl-4">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            <form method="POST" action="{{ route('admin.password.update') }}" class="space-y-4">
                @csrf

                <input type="hidden" name="token" value="{{ $token }}">

                <div>
                    <label for="email" class="form-label">Email address</label>
                    <input id="email" type="email" name="email" value="{{ old('email', $email) }}" required autofocus autocomplete="username" class="form-input">
                </div>

                <div>
                    <label for="password" class="form-label">New password</label>
                    <input id="password" type="password" name="password" required autocomplete="new-password" class="form-input" placeholder="••••••••" minlength="8">
                    <p class="mt-1 text-xs text-muted">At least 8 characters.</p>
                </div>

                <div>
                    <label for="password_confirmation" class="form-label">Confirm new password</label>
                    <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" class="form-input" placeholder="••••••••" minlength="8">
                </div>

                <div class="rounded-xl border border-brand-orange/25 bg-brand-orange-soft px-4 py-3 text-sm text-brand-orange">
                    Make sure to remember this password for future logins.
                </div>

                <button type="submit" class="btn-primary w-full py-3">
                    Update password
                </button>
            </form>

            <div class="mt-5 text-center">
                <a href="{{ route('admin.login') }}" class="text-sm font-semibold text-brand-blue hover:text-brand-blue-dark">
                    Back to sign in
                </a>
            </div>
        </div>

        <x-developer-credits class="mt-8 text-center" />
    </div>
</div>
@endsection
