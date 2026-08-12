@extends('layouts.app')

@section('title', 'Event Register')

@section('body')
<div class="relative flex min-h-screen flex-col overflow-hidden bg-surface">
    <div class="pointer-events-none absolute inset-0">
        <div class="absolute -left-24 -top-24 h-80 w-80 rounded-full bg-brand-green/10 blur-3xl"></div>
        <div class="absolute -right-20 top-1/4 h-72 w-72 rounded-full bg-brand-orange/10 blur-3xl"></div>
        <div class="absolute bottom-0 left-1/3 h-64 w-64 rounded-full bg-brand-blue/10 blur-3xl"></div>
    </div>

    <header class="relative border-b border-slate-200/80 bg-white/90 backdrop-blur">
        <div class="mx-auto flex max-w-5xl items-center justify-between gap-4 px-4 py-4 sm:px-6">
            <div class="flex items-center gap-3">
                <img src="{{ asset('images/asda-logo.png') }}" alt="ASDA" class="h-10 w-auto object-contain">
                <div>
                    <p class="font-display text-sm font-bold text-ink">ASDA MMS</p>
                    <p class="text-xs text-muted">Event Register</p>
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

        <div class="mb-8 overflow-hidden rounded-3xl bg-gradient-to-br from-brand-blue to-brand-blue-dark p-6 text-white shadow-lg sm:p-8">
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-white/80">Events</p>
            <h1 class="mt-2 font-display text-3xl font-bold tracking-tight">Event Register</h1>
            <p class="mt-2 max-w-xl text-sm text-white/85">Open an event, answer the questions, then register.</p>
        </div>

        <div class="space-y-5">
            @forelse ($events as $event)
                @php
                    $isEnrolled = in_array($event->id, $enrolledIds, true);
                    $theme = $event->hasEnded()
                        ? 'slate'
                        : ($event->isOngoing() ? 'green' : 'blue');
                    $shell = match ($theme) {
                        'green' => 'from-brand-green/20 via-brand-green-soft to-white border-brand-green/25',
                        'blue' => 'from-brand-blue/20 via-brand-blue-soft to-white border-brand-blue/25',
                        default => 'from-slate-200/80 via-slate-50 to-white border-slate-200',
                    };
                    $accent = match ($theme) {
                        'green' => 'bg-brand-green text-white',
                        'blue' => 'bg-brand-blue text-white',
                        default => 'bg-slate-500 text-white',
                    };
                @endphp
                <article class="event-card overflow-hidden rounded-3xl border bg-gradient-to-br {{ $shell }} shadow-sm">
                    <div class="p-5 sm:p-7">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="inline-flex rounded-full px-2.5 py-1 text-[11px] font-bold uppercase tracking-wide {{ $accent }}">
                                {{ $event->timelineLabel() }}
                            </span>
                            @if ($event->isBothMethods())
                                <span class="badge-green">Physical</span>
                                <span class="badge-blue">Online</span>
                            @elseif ($event->isOnline())
                                <span class="badge-blue">Online</span>
                            @else
                                <span class="badge-green">Physical</span>
                            @endif
                            @if ($isEnrolled)
                                <span class="badge-orange">Registered</span>
                            @endif
                        </div>

                        <h2 class="mt-3 font-display text-2xl font-bold text-ink">{{ $event->name }}</h2>
                        <p class="mt-2 flex items-start gap-2 text-sm font-medium text-brand-blue">
                            <svg class="mt-0.5 h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            {{ $event->scheduleLabel() }}
                        </p>

                        @if ($event->description)
                            <p class="mt-3 text-sm leading-relaxed text-muted">{{ $event->description }}</p>
                        @endif

                        <div class="mt-5 grid gap-3 sm:grid-cols-2">
                            <div class="rounded-xl bg-white/80 p-3 ring-1 ring-slate-200/80">
                                <p class="text-[11px] font-semibold uppercase tracking-wide text-muted">Start</p>
                                <p class="mt-1 text-sm font-semibold text-ink">
                                    {{ \App\Support\SriLankaDate::date($event->start_date) }}
                                    · {{ \App\Support\SriLankaDate::format($event->start_date->format('Y-m-d').' '.substr((string) $event->start_time, 0, 5), \App\Support\SriLankaDate::TIME) }}
                                </p>
                            </div>
                            <div class="rounded-xl bg-white/80 p-3 ring-1 ring-slate-200/80">
                                <p class="text-[11px] font-semibold uppercase tracking-wide text-muted">End</p>
                                <p class="mt-1 text-sm font-semibold text-ink">
                                    {{ \App\Support\SriLankaDate::date($event->end_date) }}
                                    · {{ \App\Support\SriLankaDate::format($event->end_date->format('Y-m-d').' '.substr((string) $event->end_time, 0, 5), \App\Support\SriLankaDate::TIME) }}
                                </p>
                            </div>
                        </div>

                        @if ($event->days->isNotEmpty())
                            <div class="mt-5">
                                <p class="text-xs font-semibold uppercase tracking-wide text-muted">Event days &amp; sessions</p>
                                <div class="mt-2 space-y-2">
                                    @foreach ($event->days as $day)
                                        <div class="rounded-xl border border-brand-green/15 bg-brand-green-soft/50 px-3 py-2.5">
                                            <div class="flex flex-wrap items-center gap-2">
                                                <span class="badge-green">{{ $day->dayLabel() }}</span>
                                                <span class="text-xs text-muted">{{ $day->sessions->count() }} session{{ $day->sessions->count() === 1 ? '' : 's' }}</span>
                                            </div>
                                            @if ($day->sessions->isNotEmpty())
                                                <ul class="mt-2 space-y-1">
                                                    @foreach ($day->sessions as $session)
                                                        <li class="text-sm font-semibold text-ink">{{ $session->name }}</li>
                                                    @endforeach
                                                </ul>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        @if ($event->venues->isNotEmpty())
                            <div class="mt-5">
                                <p class="text-xs font-semibold uppercase tracking-wide text-muted">Venues</p>
                                <div class="mt-2 space-y-2">
                                    @foreach ($event->venues as $venue)
                                        <div class="rounded-xl border border-brand-blue/15 bg-brand-blue-soft/50 px-3 py-2.5">
                                            <p class="text-sm font-semibold text-ink">{{ $venue->locationSummary() }}</p>
                                            @if ($venue->description)
                                                <p class="mt-1 text-xs text-muted">{{ $venue->description }}</p>
                                            @endif
                                            @if ($venue->googleMapsLink())
                                                <a href="{{ $venue->googleMapsLink() }}" target="_blank" rel="noopener" class="mt-1 inline-flex text-xs font-semibold text-brand-blue hover:underline">Open map</a>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <div class="mt-5 flex flex-col gap-3 border-t border-slate-200/70 pt-4 sm:flex-row sm:items-center sm:justify-between">
                            <p class="text-xs font-semibold text-muted">{{ $event->methodLabel() }}</p>
                            <div class="flex flex-col gap-2 sm:flex-row sm:flex-wrap sm:items-center sm:justify-end">
                                <a href="{{ route('member.events.show', $event) }}" class="btn-secondary justify-center">Full details</a>
                                @if ($event->memberCanRegister($member))
                                    @if ($isEnrolled)
                                        <form method="POST" action="{{ route('member.events.unenroll', $event) }}" data-confirm="Leave {{ $event->name }}? Your registration and answers will be removed.">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn-outline w-full justify-center sm:w-auto">Leave event</button>
                                        </form>
                                    @else
                                        <a href="{{ route('member.events.show', $event) }}#enroll-form" class="btn-enroll w-full justify-center sm:min-w-[10.5rem] sm:w-auto">
                                            <span class="btn-enroll-shine" aria-hidden="true"></span>
                                            <span class="relative z-10">Register</span>
                                        </a>
                                    @endif
                                @elseif ($isEnrolled)
                                    <form method="POST" action="{{ route('member.events.unenroll', $event) }}" data-confirm="Leave {{ $event->name }}? Your registration and answers will be removed.">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-outline w-full justify-center sm:w-auto">Leave event</button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                </article>
            @empty
                <div class="rounded-3xl border border-dashed border-slate-300 bg-white/80 px-6 py-16 text-center">
                    <p class="font-display text-lg font-bold text-ink">No events available</p>
                    <p class="mt-2 text-sm text-muted">Check back later for new ASDA events.</p>
                    <a href="{{ route('member.home') }}" class="btn-primary mt-6">Back to portal</a>
                </div>
            @endforelse
        </div>
    </main>

    <x-member-footer />
</div>
@endsection
