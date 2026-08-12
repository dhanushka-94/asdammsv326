@extends('layouts.app')

@section('title', $pageTitle)

@section('body')
<div class="relative flex min-h-screen flex-col overflow-hidden bg-surface">
    <div class="pointer-events-none absolute inset-0">
        <div class="absolute -left-24 -top-24 h-80 w-80 rounded-full bg-brand-orange/10 blur-3xl"></div>
        <div class="absolute -right-20 top-1/3 h-72 w-72 rounded-full bg-brand-blue/10 blur-3xl"></div>
    </div>

    <header class="relative border-b border-slate-200/80 bg-white/90 backdrop-blur">
        <div class="mx-auto flex max-w-5xl items-center justify-between gap-4 px-4 py-4 sm:px-6">
            <div class="flex items-center gap-3">
                <img src="{{ asset('images/asda-logo.png') }}" alt="ASDA" class="h-10 w-auto object-contain">
                <div>
                    <p class="font-display text-sm font-bold text-ink">ASDA MMS</p>
                    <p class="text-xs text-muted">{{ $pageTitle }}</p>
                </div>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('member.home') }}" class="btn-outline">Portal home</a>
                <form method="POST" action="{{ route('member.logout') }}">
                    @csrf
                    <button type="submit" class="btn-outline">Sign out</button>
                </form>
            </div>
        </div>
    </header>

    <main class="relative mx-auto w-full max-w-5xl flex-1 px-4 py-6 sm:px-6">
        @if (session('success'))
            <div class="alert-success">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="alert-error">{{ session('error') }}</div>
        @endif

        <div class="mb-8 overflow-hidden rounded-3xl {{ $isCard ? 'bg-gradient-to-br from-[#2a4a73] via-brand-blue to-brand-orange' : 'bg-gradient-to-br from-brand-orange to-brand-orange-dark' }} p-6 text-white shadow-lg sm:p-8">
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-white/80">Download</p>
            <h1 class="mt-2 font-display text-3xl font-bold tracking-tight">{{ $pageTitle }}</h1>
            <p class="mt-2 max-w-xl text-sm text-white/85">{{ $pageSubtitle }}</p>
        </div>

        <div class="space-y-4">
            @forelse ($events as $event)
                @php $isEnrolled = in_array($event->id, $enrolledIds, true); @endphp
                <article class="rounded-3xl border border-slate-200/80 bg-white p-5 shadow-sm sm:p-7">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <div class="flex flex-wrap items-center gap-2">
                                @if ($isEnrolled)
                                    <span class="badge-orange">Registered</span>
                                @endif
                                <span class="badge-blue">{{ $event->timelineLabel() }}</span>
                            </div>
                            <h2 class="mt-2 font-display text-xl font-bold text-ink">{{ $event->name }}</h2>
                            <p class="mt-1 text-sm text-muted">{{ $event->scheduleLabel() }}</p>
                        </div>
                        <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
                            <a href="{{ route('member.events.show', $event) }}" class="btn-outline justify-center">Event details</a>
                            <a
                                href="{{ route($isCard ? 'member.events.invitation.card' : 'member.events.invitation.letter', $event) }}"
                                class="{{ $isCard ? 'btn-secondary' : 'btn-accent' }} justify-center"
                            >
                                Download {{ $isCard ? 'card' : 'letter' }}
                            </a>
                        </div>
                    </div>
                </article>
            @empty
                <div class="rounded-3xl border border-dashed border-slate-300 bg-white/80 px-6 py-16 text-center">
                    <p class="font-display text-lg font-bold text-ink">No {{ strtolower($pageTitle) }} available</p>
                    <p class="mt-2 text-sm text-muted">Templates will appear here when an event publishes them.</p>
                    <div class="mt-6 flex flex-wrap justify-center gap-3">
                        <a href="{{ route('member.home') }}" class="btn-outline">Back to portal</a>
                        <a href="{{ route('member.events.index') }}" class="btn-primary">Event Register</a>
                    </div>
                </div>
            @endforelse
        </div>
    </main>

    <x-member-footer />
</div>
@endsection
