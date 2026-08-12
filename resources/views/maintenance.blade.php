@extends('layouts.app')

@section('title', 'Maintenance')

@section('body')
<div class="relative flex min-h-dvh w-full flex-col overflow-hidden bg-surface">
    <div class="pointer-events-none absolute inset-0 overflow-hidden" aria-hidden="true">
        <div class="maintenance-orb maintenance-orb-green absolute -left-24 -top-24 h-72 w-72 rounded-full bg-brand-green/15 blur-3xl sm:h-96 sm:w-96"></div>
        <div class="maintenance-orb maintenance-orb-orange absolute -right-20 top-[18%] h-64 w-64 rounded-full bg-brand-orange/15 blur-3xl sm:h-80 sm:w-80"></div>
        <div class="maintenance-orb maintenance-orb-blue absolute bottom-[-10%] left-[20%] h-56 w-56 rounded-full bg-brand-blue/15 blur-3xl sm:h-72 sm:w-72"></div>
        <div class="maintenance-orb maintenance-orb-soft absolute right-[15%] bottom-[20%] h-40 w-40 rounded-full bg-brand-green/10 blur-2xl"></div>
    </div>

    <div class="relative z-10 flex min-h-dvh flex-1 flex-col">
        <main class="flex flex-1 flex-col items-center justify-center px-4 py-10 text-center sm:px-6 sm:py-14">
            <img src="{{ asset('images/asda-logo.png') }}" alt="ASDA" class="h-20 w-auto object-contain sm:h-28 md:h-32">

            <p class="mt-6 text-[11px] font-semibold uppercase tracking-[0.22em] text-brand-green sm:text-xs">ASDA MMS</p>
            <h1 class="mt-3 max-w-xl font-display text-3xl font-bold tracking-tight text-ink sm:text-4xl md:text-5xl">
                Under maintenance
            </h1>
            <p class="mt-4 max-w-lg text-sm leading-relaxed text-muted sm:text-base">
                {{ $message }}
            </p>

            <div class="mt-8 w-full max-w-md rounded-2xl border border-slate-200/80 bg-white/80 px-5 py-4 text-sm text-muted shadow-sm backdrop-blur">
                <p class="font-medium text-ink">Annual Symposium of the Department of Agriculture</p>
                <p class="mt-1">Member Management System</p>
            </div>
        </main>

        <footer class="relative z-10 border-t border-slate-200/70 bg-white/70 px-4 py-4 backdrop-blur sm:px-6">
            <div class="mx-auto flex w-full max-w-3xl flex-col items-center gap-1 text-center text-xs text-muted sm:flex-row sm:justify-between sm:text-left">
                <p>&copy; {{ now()->year }} {{ \App\Support\AppSettings::footerCopyright() }}</p>
                <x-developer-credits credit-class="font-semibold text-brand-blue" />
            </div>
        </footer>
    </div>
</div>
@endsection
