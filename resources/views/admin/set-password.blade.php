@extends('layouts.app')

@section('title', 'Set New Password')

@section('body')
<div class="relative min-h-screen overflow-hidden bg-surface">
    <div class="pointer-events-none absolute inset-0">
        <div class="absolute -left-24 -top-24 h-80 w-80 rounded-full bg-brand-green/10 blur-3xl"></div>
        <div class="absolute -right-16 top-1/4 h-72 w-72 rounded-full bg-brand-orange/10 blur-3xl"></div>
    </div>

    <div class="relative mx-auto flex min-h-screen max-w-lg flex-col justify-center px-4 py-10 sm:px-6">
        <div class="mb-6 text-center">
            <img src="{{ asset('images/asda-logo.png') }}" alt="ASDA Logo" class="mx-auto h-20 w-auto object-contain">
        </div>

        <div class="card overflow-hidden">
            <div class="bg-brand-blue px-5 py-5 text-white">
                <h1 class="font-display text-2xl font-bold">Set a new password</h1>
                <p class="mt-1 text-sm text-white/85">For security, change your temporary password before continuing.</p>
            </div>

            <form method="POST" action="{{ route('admin.set-password.update') }}" class="space-y-5 p-5 sm:p-8">
                @csrf
                @method('PUT')

                <div class="rounded-xl bg-surface p-4">
                    <p class="text-xs font-semibold uppercase tracking-wide text-muted">Signed in as</p>
                    <p class="mt-1 font-semibold text-ink">{{ $user->name }}</p>
                    <p class="text-sm text-muted">{{ $user->email }} · {{ $user->roleLabel() }}</p>
                </div>

                @if ($errors->any())
                    <div class="alert-error">
                        <div>
                            @foreach ($errors->all() as $error)
                                <p>{{ $error }}</p>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div>
                    <label for="password" class="form-label">New password</label>
                    <input id="password" type="password" name="password" required class="form-input" autocomplete="new-password" minlength="8">
                    <p class="mt-1 text-xs text-muted">At least 8 characters. Do not reuse the default password.</p>
                </div>

                <div>
                    <label for="password_confirmation" class="form-label">Confirm new password</label>
                    <input id="password_confirmation" type="password" name="password_confirmation" required class="form-input" autocomplete="new-password" minlength="8">
                </div>

                <div class="rounded-xl border border-brand-orange/25 bg-brand-orange-soft px-4 py-3 text-sm text-brand-orange">
                    Make sure to remember this password for future logins.
                </div>

                <button type="submit" class="btn-primary w-full">Save password &amp; continue</button>
            </form>
        </div>

        <form method="POST" action="{{ route('admin.logout') }}" class="mt-4 text-center">
            @csrf
            <button type="submit" class="text-sm font-semibold text-brand-blue hover:underline">Sign out</button>
        </form>
    </div>
</div>
@endsection
