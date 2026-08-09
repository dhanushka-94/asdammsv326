@extends('layouts.app')

@section('title', 'Forgot password')

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
                <h1 class="font-display text-2xl font-bold text-ink">Forgot password</h1>
                <p class="mt-1 text-sm text-muted">Enter your email and we will send a reset link.</p>
            </div>

            @if (session('status'))
                <div class="alert-success mb-5">
                    <span>{{ session('status') }}</span>
                </div>
            @endif

            @if ($errors->any())
                <div class="alert-error mb-5">
                    <span>{{ $errors->first() }}</span>
                </div>
            @endif

            <form method="POST" action="{{ route('admin.password.email') }}" class="space-y-4">
                @csrf

                <div>
                    <label for="email" class="form-label">Email address</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username" class="form-input" placeholder="admin@asdamms.com">
                </div>

                <button type="submit" class="btn-primary w-full py-3">
                    Send reset link
                </button>
            </form>

            <div class="mt-5 text-center">
                <a href="{{ route('admin.login') }}" class="text-sm font-semibold text-brand-blue hover:text-brand-blue-dark">
                    Back to sign in
                </a>
            </div>
        </div>

        <p class="mt-8 text-center text-xs font-semibold text-brand-blue">
            Developed by 1920 &amp; TFBS - Department of Agriculture
        </p>
    </div>
</div>
@endsection
