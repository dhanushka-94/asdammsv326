@extends('layouts.dashboard')

@section('title', 'ASDA Events')
@section('page-title', 'ASDA Events')
@section('page-subtitle', 'Manage events in the member enrollment pool')

@section('page-actions')
@if (auth()->user()->canManageEvents())
<a href="{{ route('admin.events.create') }}" class="btn-accent">Add event</a>
@endif
@endsection

@section('content')
<div class="space-y-5">
    @forelse ($events as $event)
        @php
            $theme = $event->hasEnded()
                ? 'slate'
                : ($event->isOngoing() ? 'green' : ($event->isUpcoming() ? 'blue' : 'orange'));
            $shell = match ($theme) {
                'green' => 'border-brand-green/25 from-brand-green/10 via-white to-brand-green-soft/40',
                'blue' => 'border-brand-blue/25 from-brand-blue/10 via-white to-brand-blue-soft/40',
                'orange' => 'border-brand-orange/25 from-brand-orange/10 via-white to-brand-orange-soft/40',
                default => 'border-slate-200 from-slate-100 via-white to-slate-50',
            };
            $timelineBadge = match ($theme) {
                'green' => 'badge-green',
                'blue' => 'badge-blue',
                'orange' => 'badge-orange',
                default => 'badge-muted',
            };
        @endphp

        <article class="overflow-hidden rounded-3xl border bg-gradient-to-br {{ $shell }} shadow-sm">
            <div class="p-5 sm:p-7">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="{{ $timelineBadge }}">{{ $event->timelineLabel() }}</span>
                            <span class="{{ $event->isActive() ? 'badge-green' : 'badge-muted' }}">{{ $event->statusLabel() }}</span>
                            @if ($event->isBothMethods())
                                <span class="badge-green">Physical</span>
                                <span class="badge-blue">Online</span>
                            @elseif ($event->isOnline())
                                <span class="badge-blue">Online</span>
                            @else
                                <span class="badge-green">Physical</span>
                            @endif
                        </div>

                        <h2 class="mt-3 font-display text-xl font-bold tracking-tight text-ink sm:text-2xl">
                            <a href="{{ route('admin.events.show', $event) }}" class="hover:text-brand-blue">{{ $event->name }}</a>
                        </h2>

                        @if ($event->description)
                            <p class="mt-2 max-w-3xl text-sm leading-relaxed text-muted">{{ $event->description }}</p>
                        @endif
                    </div>

                    <div class="flex shrink-0 flex-wrap gap-2">
                        <a href="{{ route('admin.events.show', $event) }}" class="btn-outline">View details</a>
                        @if (auth()->user()->canManageEvents())
                            <a href="{{ route('admin.events.invites.edit', $event) }}" class="btn-accent">Invite members</a>
                            <a href="{{ route('admin.events.edit', $event) }}" class="btn-secondary">Edit</a>
                        @endif
                    </div>
                </div>

                <div class="mt-5 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                    <div class="rounded-2xl border border-white/80 bg-white/80 p-4 shadow-sm">
                        <p class="text-[11px] font-semibold uppercase tracking-wide text-muted">Schedule</p>
                        <p class="mt-1 text-sm font-semibold text-ink">{{ $event->scheduleLabel() }}</p>
                        <p class="mt-2 text-xs text-muted">
                            Start: {{ \App\Support\SriLankaDate::date($event->start_date) }}
                            · {{ \App\Support\SriLankaDate::format($event->start_date->format('Y-m-d').' '.substr((string) $event->start_time, 0, 5), \App\Support\SriLankaDate::TIME) }}
                        </p>
                        <p class="mt-1 text-xs text-muted">
                            End: {{ \App\Support\SriLankaDate::date($event->end_date) }}
                            · {{ \App\Support\SriLankaDate::format($event->end_date->format('Y-m-d').' '.substr((string) $event->end_time, 0, 5), \App\Support\SriLankaDate::TIME) }}
                        </p>
                    </div>

                    <div class="rounded-2xl border border-white/80 bg-white/80 p-4 shadow-sm">
                        <p class="text-[11px] font-semibold uppercase tracking-wide text-muted">Enrollment</p>
                        <p class="mt-1 font-display text-2xl font-bold text-ink">{{ $event->active_enrollments_count }}</p>
                        <p class="mt-1 text-xs text-muted">Active member{{ $event->active_enrollments_count === 1 ? '' : 's' }} enrolled</p>
                        <p class="mt-2 text-xs text-muted">Method: {{ $event->methodLabel() }}</p>
                    </div>

                    <div class="rounded-2xl border border-white/80 bg-white/80 p-4 shadow-sm">
                        <p class="text-[11px] font-semibold uppercase tracking-wide text-muted">Event days</p>
                        <p class="mt-1 font-display text-2xl font-bold text-ink">{{ $event->days_count }}</p>
                        @if ($event->days->isNotEmpty())
                            <ul class="mt-2 space-y-1">
                                @foreach ($event->days->take(3) as $day)
                                    <li class="truncate text-xs text-muted">
                                        <span class="font-semibold text-ink">{{ $day->dayLabel() }}</span>
                                        · {{ $day->sessions->count() }} session{{ $day->sessions->count() === 1 ? '' : 's' }}
                                    </li>
                                @endforeach
                                @if ($event->days_count > 3)
                                    <li class="text-xs font-medium text-brand-blue">+{{ $event->days_count - 3 }} more</li>
                                @endif
                            </ul>
                        @else
                            <p class="mt-2 text-xs text-muted">No day sessions added</p>
                        @endif
                    </div>

                    <div class="rounded-2xl border border-white/80 bg-white/80 p-4 shadow-sm">
                        <p class="text-[11px] font-semibold uppercase tracking-wide text-muted">Venues</p>
                        <p class="mt-1 font-display text-2xl font-bold text-ink">{{ $event->venues_count }}</p>
                        @if ($event->venues->isNotEmpty())
                            <ul class="mt-2 space-y-1">
                                @foreach ($event->venues->take(3) as $venue)
                                    <li class="truncate text-xs text-muted">{{ $venue->locationSummary() }}</li>
                                @endforeach
                                @if ($event->venues_count > 3)
                                    <li class="text-xs font-medium text-brand-blue">+{{ $event->venues_count - 3 }} more</li>
                                @endif
                            </ul>
                        @elseif ($event->isOnline())
                            <p class="mt-2 text-xs text-muted">Online event — no venue required</p>
                        @else
                            <p class="mt-2 text-xs text-muted">No venues added</p>
                        @endif
                    </div>
                </div>
            </div>
        </article>
    @empty
        <div class="card p-10 text-center">
            <p class="font-display text-lg font-bold text-ink">No ASDA events yet</p>
            <p class="mt-2 text-sm text-muted">Create an event to open enrollment for members.</p>
            @if (auth()->user()->canManageEvents())
                <a href="{{ route('admin.events.create') }}" class="btn-accent mt-5 inline-flex">Add event</a>
            @endif
        </div>
    @endforelse

    @if ($events->hasPages())
        <div class="card px-4 py-3">{{ $events->links() }}</div>
    @endif
</div>
@endsection
