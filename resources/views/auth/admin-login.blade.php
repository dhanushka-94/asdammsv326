@extends('layouts.app')

@section('title', 'System Access')
@section('meta_robots', 'noindex, nofollow, noarchive, nosnippet')

@section('body')

<div class="login-shell is-ready">
    <div class="login-bg" aria-hidden="true">
        <div class="login-bg-grid"></div>
        <div class="login-bg-orb login-bg-orb-a"></div>
        <div class="login-bg-orb login-bg-orb-b"></div>
        <div class="login-bg-orb login-bg-orb-c"></div>
    </div>

    <div class="relative mx-auto flex min-h-dvh w-full max-w-md flex-col px-4 py-6 sm:px-6 sm:py-8">
        <div class="flex flex-1 flex-col items-center justify-center py-4 sm:py-6">
            <div class="login-panel w-full">
                <div class="mb-5 text-center sm:mb-6">
                    <img
                        src="{{ asset('images/asda-logo.png') }}"
                        alt="ASDA Logo"
                        class="mx-auto h-16 w-auto object-contain sm:h-20"
                    >
                    <p class="mt-3 text-xs font-semibold uppercase tracking-[0.18em] text-brand-green">
                        System Access
                    </p>
                </div>

                <div class="card p-5 sm:p-8">
                    <div class="mb-5 text-center sm:mb-6">
                        <h1 class="font-display text-xl font-bold text-ink sm:text-2xl">System access login</h1>
                        <p class="mt-1 text-sm text-muted">Administrators and system users only</p>
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

                    <form method="POST" action="{{ route('admin.login.store') }}" class="space-y-4">
                        @csrf

                        <div>
                            <label for="email" class="form-label">Email address</label>
                            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username" class="form-input" placeholder="admin@asdamms.com">
                        </div>

                        <div>
                            <div class="mb-1.5 flex items-center justify-between gap-3">
                                <label for="password" class="form-label mb-0">Password</label>
                                <a href="{{ route('admin.password.request') }}" class="text-xs font-semibold text-brand-orange hover:text-brand-orange-dark">
                                    Forgot password?
                                </a>
                            </div>
                            <input id="password" type="password" name="password" required autocomplete="current-password" class="form-input" placeholder="••••••••">
                        </div>

                        <div class="flex items-center justify-between gap-3">
                            <label class="flex items-center gap-2 text-sm text-muted">
                                <input type="checkbox" name="remember" value="1" @checked(old('remember')) class="h-4 w-4 rounded border-slate-300 text-brand-green focus:ring-brand-green">
                                Remember me
                            </label>
                        </div>

                        <button type="submit" class="btn-primary w-full py-3">
                            Sign in to admin panel
                        </button>
                    </form>

                    <div class="mt-5 border-t border-slate-100 pt-5 text-center text-sm">
                        <a href="{{ route('member.login') }}" class="font-semibold text-brand-blue hover:text-brand-blue-dark">
                            Go to member login
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <footer class="mt-6 border-t border-slate-200/80 pt-4 pb-1 text-center sm:mt-8 sm:pt-5">
            <x-developer-credits
                :show-copyright="true"
                credit-class="text-xs font-semibold text-brand-blue sm:text-sm"
            />
        </footer>
    </div>
</div>
@endsection
