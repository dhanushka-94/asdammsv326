@extends('layouts.app')

@section('title', $event->name)

@section('body')
@php
    $theme = $event->hasEnded()
        ? 'slate'
        : ($event->isOngoing() ? 'green' : 'blue');
    $hero = match ($theme) {
        'green' => 'from-brand-green via-brand-green-dark to-brand-blue',
        'blue' => 'from-brand-blue via-brand-blue-dark to-brand-green',
        default => 'from-slate-600 via-slate-700 to-slate-800',
    };
@endphp
<div class="relative flex min-h-screen flex-col overflow-hidden bg-surface">
    <div class="pointer-events-none absolute inset-0">
        <div class="absolute -left-24 -top-24 h-80 w-80 rounded-full bg-brand-green/10 blur-3xl"></div>
        <div class="absolute -right-16 top-1/3 h-72 w-72 rounded-full bg-brand-orange/10 blur-3xl"></div>
    </div>

    <header class="relative border-b border-slate-200/80 bg-white/90 backdrop-blur">
        <div class="mx-auto flex max-w-5xl items-center justify-between gap-4 px-4 py-4 sm:px-6">
            <div class="flex items-center gap-3">
                <img src="{{ asset('images/asda-logo.png') }}" alt="ASDA" class="h-10 w-auto object-contain">
                <div>
                    <p class="font-display text-sm font-bold text-ink">ASDA MMS</p>
                    <p class="text-xs text-muted">Event details</p>
                </div>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('member.home') }}" class="btn-outline">Portal home</a>
                <a href="{{ route('member.events.index') }}" class="btn-outline">Event Register</a>
                <a href="{{ route('member.profile') }}" class="btn-outline">My profile</a>
            </div>
        </div>
    </header>

    <main class="relative mx-auto w-full max-w-5xl flex-1 space-y-5 px-4 py-6 sm:px-6">
        @if (session('success'))
            <div class="alert-success">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="alert-error">{{ session('error') }}</div>
        @endif

        <section class="overflow-hidden rounded-3xl bg-gradient-to-br {{ $hero }} text-white shadow-lg">
            <div class="p-6 sm:p-8">
                <div class="flex flex-wrap gap-2">
                    <span class="rounded-full bg-white/20 px-3 py-1 text-xs font-bold uppercase tracking-wide">{{ $event->timelineLabel() }}</span>
                    @if ($event->isBothMethods())
                        <span class="rounded-full bg-white/15 px-3 py-1 text-xs font-semibold">Physical</span>
                        <span class="rounded-full bg-white/15 px-3 py-1 text-xs font-semibold">Online</span>
                    @elseif ($event->isOnline())
                        <span class="rounded-full bg-white/15 px-3 py-1 text-xs font-semibold">Online</span>
                    @else
                        <span class="rounded-full bg-white/15 px-3 py-1 text-xs font-semibold">Physical</span>
                    @endif
                    @if ($enrolled)
                        <span class="rounded-full bg-brand-orange px-3 py-1 text-xs font-bold">Registered</span>
                    @endif
                </div>
                <h1 class="mt-4 font-display text-3xl font-bold tracking-tight sm:text-4xl">{{ $event->name }}</h1>
                @php
                    $startDateText = \App\Support\SriLankaDate::dateText($event->start_date);
                    $endDateText = \App\Support\SriLankaDate::dateText($event->end_date);
                    $startTimeLabel = \App\Support\SriLankaDate::format($event->start_date->format('Y-m-d').' '.substr((string) $event->start_time, 0, 5), \App\Support\SriLankaDate::TIME);
                    $endTimeLabel = \App\Support\SriLankaDate::format($event->end_date->format('Y-m-d').' '.substr((string) $event->end_time, 0, 5), \App\Support\SriLankaDate::TIME);
                    $scheduleText = $startDateText === $endDateText
                        ? $startDateText.' · '.$startTimeLabel.' – '.$endTimeLabel
                        : $startDateText.' '.$startTimeLabel.' → '.$endDateText.' '.$endTimeLabel;
                @endphp
                <p class="mt-3 text-base text-white/90">{{ $scheduleText }}</p>

                <div class="mt-6 flex flex-wrap gap-2">
                    @if ($canRegister)
                        @if ($enrolled)
                            <form method="POST" action="{{ route('member.events.unenroll', $event) }}" data-confirm="Leave {{ $event->name }}? Your registration and answers will be removed.">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="inline-flex items-center justify-center rounded-xl border border-white/40 bg-white/10 px-5 py-2.5 text-sm font-semibold text-white backdrop-blur transition hover:bg-white/20">Leave event</button>
                            </form>
                            @if ($enrollment?->participation_mode)
                                <span class="rounded-xl bg-white/15 px-4 py-2 text-sm font-semibold">Participating: {{ $enrollment->participationModeLabel() }}</span>
                            @endif
                        @else
                            <a href="#enroll-form" class="btn-enroll btn-enroll-on-dark">
                                <span class="btn-enroll-shine" aria-hidden="true"></span>
                                <span class="relative z-10">Register for this event</span>
                            </a>
                        @endif
                    @elseif ($enrolled)
                        <form method="POST" action="{{ route('member.events.unenroll', $event) }}" data-confirm="Leave {{ $event->name }}? Your registration and answers will be removed.">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="inline-flex items-center justify-center rounded-xl border border-white/40 bg-white/10 px-5 py-2.5 text-sm font-semibold text-white backdrop-blur transition hover:bg-white/20">Leave event</button>
                        </form>
                    @else
                        <p class="rounded-xl bg-white/10 px-4 py-2 text-sm text-white/85">Registration is closed for this event.</p>
                    @endif
                </div>
            </div>
        </section>

        @if ($event->description)
            <section class="card p-5 sm:p-8">
                <h2 class="font-display text-base font-bold text-ink">About this event</h2>
                <p class="mt-3 whitespace-pre-line text-sm leading-relaxed text-muted">{{ $event->description }}</p>
            </section>
        @endif

        <section class="grid gap-4 sm:grid-cols-3">
            <div class="rounded-2xl border border-brand-green/20 bg-brand-green-soft p-4">
                <p class="text-xs font-semibold uppercase tracking-wide text-brand-green">Method</p>
                <p class="mt-1 font-semibold text-ink">{{ $event->methodLabel() }}</p>
            </div>
            <div class="rounded-2xl border border-brand-blue/20 bg-brand-blue-soft p-4">
                <p class="text-xs font-semibold uppercase tracking-wide text-brand-blue">Timeline</p>
                <p class="mt-1 font-semibold text-ink">{{ $event->timelineLabel() }}</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-4">
                <p class="text-xs font-semibold uppercase tracking-wide text-muted">Registration</p>
                <p class="mt-1 font-semibold text-ink">{{ $enrolled ? 'You are registered' : ($canRegister ? 'Open' : 'Closed') }}</p>
            </div>
        </section>

        <section class="grid gap-4 sm:grid-cols-2">
            <div class="card p-5">
                <p class="text-xs font-semibold uppercase tracking-wide text-muted">Start</p>
                <p class="mt-2 font-semibold text-ink">
                    {{ $startDateText }}
                    · {{ $startTimeLabel }}
                </p>
            </div>
            <div class="card p-5">
                <p class="text-xs font-semibold uppercase tracking-wide text-muted">End</p>
                <p class="mt-2 font-semibold text-ink">
                    {{ $endDateText }}
                    · {{ $endTimeLabel }}
                </p>
            </div>
        </section>

        @if ($event->days->isNotEmpty())
            <section class="card p-5 sm:p-8">
                <h2 class="font-display text-base font-bold text-ink">Event days &amp; sessions</h2>
                <p class="mt-1 text-sm text-muted">Each day can include multiple sessions.</p>
                <div class="mt-4 space-y-4">
                    @foreach ($event->days as $day)
                        <div class="rounded-2xl border border-brand-green/20 bg-gradient-to-br from-brand-green-soft to-white p-4">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="badge-green">{{ $day->dayLabel() }}</span>
                                <span class="text-xs font-semibold text-muted">{{ $day->sessions->count() }} session{{ $day->sessions->count() === 1 ? '' : 's' }}</span>
                            </div>
                            @if ($day->description)
                                <p class="mt-2 text-sm text-muted">{{ $day->description }}</p>
                            @endif
                            @if ($day->sessions->isNotEmpty())
                                <div class="mt-3 space-y-2">
                                    @foreach ($day->sessions as $session)
                                        <div class="rounded-xl border border-white/80 bg-white/90 p-3">
                                            <p class="font-semibold text-ink">{{ $session->name }}</p>
                                            @if ($session->description)
                                                <p class="mt-1 text-sm text-muted">{{ $session->description }}</p>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        @if ($event->venues->isNotEmpty())
            <section class="card p-5 sm:p-8">
                <h2 class="font-display text-base font-bold text-ink">Venues</h2>
                <p class="mt-1 text-sm text-muted">Locations for this event.</p>
                <div class="mt-4 space-y-4">
                    @foreach ($event->venues as $venue)
                        <div class="rounded-2xl border border-slate-200 bg-gradient-to-br from-brand-blue-soft/60 to-white p-4">
                            <p class="font-semibold text-ink">{{ $venue->locationSummary() }}</p>
                            @if ($venue->description)
                                <p class="mt-2 text-sm text-muted">{{ $venue->description }}</p>
                            @endif

                            @if ($venue->embedMapsUrl())
                                <div class="mt-3 overflow-hidden rounded-xl border border-slate-200">
                                    <iframe title="Map {{ $venue->name }}" src="{{ $venue->embedMapsUrl() }}" class="h-52 w-full" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                                </div>
                            @endif

                            @if ($venue->directionsUrl() || $venue->googleMapsLink())
                                <div class="mt-3 flex flex-wrap gap-2">
                                    @if ($venue->directionsUrl())
                                        <a
                                            href="{{ $venue->directionsUrl() }}"
                                            target="_blank"
                                            rel="noopener"
                                            class="inline-flex flex-1 items-center justify-center gap-2 rounded-xl bg-brand-orange px-4 py-3 text-sm font-bold text-white shadow-lg shadow-brand-orange/25 transition hover:bg-brand-orange-dark sm:flex-none"
                                        >
                                            <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l5.447 2.724A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>
                                            Get Direction
                                        </a>
                                    @endif
                                    @if ($venue->googleMapsLink())
                                        <a
                                            href="{{ $venue->googleMapsLink() }}"
                                            target="_blank"
                                            rel="noopener"
                                            class="inline-flex flex-1 items-center justify-center gap-2 rounded-xl border border-brand-blue/30 bg-white px-4 py-3 text-sm font-semibold text-brand-blue transition hover:bg-brand-blue-soft sm:flex-none"
                                        >
                                            Open map
                                        </a>
                                    @endif
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </section>
        @elseif ($event->isOnline())
            <div class="card p-5 text-sm text-muted sm:p-6">This is an online event. Venue details are not required.</div>
        @endif

        @if ($event->hasAnyInvitation())
            <section class="card p-5 sm:p-8">
                <h2 class="font-display text-base font-bold text-ink">Your invitations</h2>
                <p class="mt-1 text-sm text-muted">Download your personalized invitation letter and card as PDF.</p>
                <div class="mt-4 flex flex-col gap-3 sm:flex-row">
                    @if ($event->hasInvitationLetter())
                        <a href="{{ route('member.events.invitation.letter', $event) }}" class="btn-secondary justify-center">
                            Download invitation letter
                        </a>
                    @endif
                    @if ($event->hasInvitationCard())
                        <a href="{{ route('member.events.invitation.card', $event) }}" class="btn-outline justify-center">
                            Download invitation card
                        </a>
                    @endif
                </div>
            </section>
        @endif

        @if ($canRegister && ! $enrolled)
            <section id="enroll-form" class="card p-5 sm:p-8">
                <h2 class="font-display text-base font-bold text-ink">Register for {{ $event->name }}</h2>
                <p class="mt-1 text-sm text-muted">Confirm how you will join. All questionnaire answers are required before registration.</p>

                @if ($errors->any())
                    <div class="alert-error mt-4">
                        <ul class="list-disc space-y-1 pl-5">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('member.events.enroll', $event) }}" class="mt-5 space-y-5">
                    @csrf

                    <div>
                        <p class="form-label">Will you be participating in {{ $event->name }}?</p>
                        @if ($event->isBothMethods())
                            <div class="mt-2 grid gap-3 sm:grid-cols-2">
                                <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-slate-200 bg-white px-4 py-3 has-[:checked]:border-brand-green has-[:checked]:bg-brand-green-soft">
                                    <input type="radio" name="participation_mode" value="physical" class="h-4 w-4 text-brand-green" @checked(old('participation_mode') === 'physical') required>
                                    <span class="text-sm font-semibold text-ink">Physical</span>
                                </label>
                                <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-slate-200 bg-white px-4 py-3 has-[:checked]:border-brand-blue has-[:checked]:bg-brand-blue-soft">
                                    <input type="radio" name="participation_mode" value="online" class="h-4 w-4 text-brand-blue" @checked(old('participation_mode') === 'online') required>
                                    <span class="text-sm font-semibold text-ink">Online</span>
                                </label>
                            </div>
                        @elseif ($event->isOnline() && ! $event->isPhysical())
                            <input type="hidden" name="participation_mode" value="online">
                            <p class="mt-2 rounded-xl border border-brand-blue/20 bg-brand-blue-soft px-4 py-3 text-sm font-semibold text-ink">Online</p>
                        @else
                            <input type="hidden" name="participation_mode" value="physical">
                            <p class="mt-2 rounded-xl border border-brand-green/20 bg-brand-green-soft px-4 py-3 text-sm font-semibold text-ink">Physical</p>
                        @endif
                        @error('participation_mode')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    @php
                        $questionnaireDays = $event->days->filter(fn ($day) => $day->questions->isNotEmpty());
                    @endphp

                    @if ($questionnaireDays->isNotEmpty())
                        <div class="space-y-4">
                            <div>
                                <h3 class="font-display text-sm font-bold text-ink">Questionnaire</h3>
                                <p class="mt-1 text-sm text-muted">Every question must be answered to register.</p>
                            </div>

                            @foreach ($questionnaireDays as $day)
                                <div class="rounded-2xl border border-slate-200 bg-surface/40 p-4">
                                    <p class="text-xs font-semibold uppercase tracking-wide text-muted">{{ $day->dayLabel() }}</p>
                                    <div class="mt-3 space-y-4">
                                        @foreach ($day->questions as $question)
                                            <div>
                                                <p class="text-sm font-semibold text-ink">
                                                    {{ $question->question }}
                                                    <span class="text-red-600">*</span>
                                                </p>
                                                <div class="mt-2 space-y-2">
                                                    @foreach ($question->options as $option)
                                                        <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-slate-200 bg-white px-3 py-2.5 has-[:checked]:border-brand-green has-[:checked]:bg-brand-green-soft">
                                                            <input
                                                                type="radio"
                                                                name="answers[{{ $question->id }}]"
                                                                value="{{ $option->id }}"
                                                                class="h-4 w-4 text-brand-green"
                                                                @checked((string) old('answers.'.$question->id) === (string) $option->id)
                                                                required
                                                            >
                                                            <span class="text-sm text-ink">{{ $option->label }}</span>
                                                        </label>
                                                    @endforeach
                                                </div>
                                                @error('answers.'.$question->id)
                                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                                @enderror
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <button type="submit" class="btn-primary">
                        Confirm registration
                    </button>
                </form>
            </section>
        @endif

        @if ($enrolled && $enrollment && $enrollment->answers->isNotEmpty())
            <section class="card p-5 sm:p-8">
                <h2 class="font-display text-base font-bold text-ink">Your registration answers</h2>
                <div class="mt-4 space-y-3">
                    @foreach ($enrollment->answers as $answer)
                        <div class="rounded-xl border border-slate-200 bg-surface/50 px-4 py-3">
                            <p class="text-sm font-semibold text-ink">{{ $answer->question?->question ?: 'Question' }}</p>
                            <p class="mt-1 text-sm text-muted">{{ $answer->option?->label ?: '—' }}</p>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif
    </main>

    <x-member-footer />
</div>
@endsection
