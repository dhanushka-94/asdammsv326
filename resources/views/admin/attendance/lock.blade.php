@extends('layouts.app')

@section('title', 'Desk locked')

@section('body')
<div
    id="desk-lock-screen"
    class="flex min-h-screen items-center justify-center bg-slate-950 px-4 py-10"
    data-unlock-url="{{ route('admin.attendance.unlock') }}"
    data-csrf="{{ csrf_token() }}"
>
    <div class="w-full max-w-sm space-y-6 text-center">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Attendance desk</p>
            <h1 class="mt-2 font-display text-3xl font-bold text-white">Screen locked</h1>
            <p class="mt-2 text-sm text-slate-400">Enter your 4-digit PIN to continue. You stay signed in.</p>
        </div>

        <div class="rounded-3xl border border-white/10 bg-white/5 px-4 py-4 text-left backdrop-blur">
            <div class="flex items-center gap-3">
                <div class="flex h-12 w-12 shrink-0 items-center justify-center overflow-hidden rounded-2xl bg-brand-green/20 text-lg font-bold text-brand-green">
                    @if ($user->profileImageUrl())
                        <img src="{{ $user->profileImageUrl() }}" alt="" class="h-full w-full object-cover">
                    @else
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    @endif
                </div>
                <div class="min-w-0">
                    <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-400">Reception officer</p>
                    <p class="truncate font-display text-lg font-bold text-white">{{ $user->name }}</p>
                    <p class="truncate text-xs text-slate-400">{{ $user->roleLabel() }}</p>
                </div>
            </div>
        </div>

        <div class="rounded-3xl border border-white/10 bg-white/5 p-5 backdrop-blur">
            <div class="mb-4 flex justify-center gap-2" data-pin-dots>
                <span class="h-3 w-3 rounded-full bg-slate-600" data-dot></span>
                <span class="h-3 w-3 rounded-full bg-slate-600" data-dot></span>
                <span class="h-3 w-3 rounded-full bg-slate-600" data-dot></span>
                <span class="h-3 w-3 rounded-full bg-slate-600" data-dot></span>
            </div>

            <p data-pin-message class="mb-4 min-h-5 text-sm font-semibold text-slate-300"></p>

            <div class="grid grid-cols-3 gap-2">
                @foreach (['1','2','3','4','5','6','7','8','9','clear','0','del'] as $key)
                    @if ($key === 'clear')
                        <button type="button" data-pin-key="clear" class="rounded-2xl bg-white/10 px-3 py-4 text-sm font-semibold text-slate-200 hover:bg-white/15">Clear</button>
                    @elseif ($key === 'del')
                        <button type="button" data-pin-key="del" class="rounded-2xl bg-white/10 px-3 py-4 text-sm font-semibold text-slate-200 hover:bg-white/15">⌫</button>
                    @else
                        <button type="button" data-pin-key="{{ $key }}" class="rounded-2xl bg-white/10 px-3 py-4 text-2xl font-semibold text-white hover:bg-white/15">{{ $key }}</button>
                    @endif
                @endforeach
            </div>
        </div>

        <form method="POST" action="{{ route('admin.logout') }}">
            @csrf
            <button type="submit" class="text-sm font-semibold text-slate-400 underline decoration-slate-600 underline-offset-4 hover:text-white">
                Sign out instead
            </button>
        </form>
    </div>
</div>
@endsection
