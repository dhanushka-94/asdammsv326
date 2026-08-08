@extends('layouts.dashboard')

@section('title', 'Attendance — '.$event->name)
@section('page-title', 'Attendance desk')
@section('page-subtitle', $event->name)

@section('page-actions')
<a href="{{ route('admin.checked-in.index', ['event' => $event->id]) }}" class="btn-outline shrink-0 whitespace-nowrap">Checked-in list</a>
@if (auth()->user()->hasDeskPin())
<form method="POST" action="{{ route('admin.attendance.lock.store') }}" class="inline">
    @csrf
    <input type="hidden" name="return" value="{{ url()->current() }}">
    <button type="submit" class="btn-secondary shrink-0 whitespace-nowrap" title="Lock desk (Ctrl+L)">
        Lock desk
    </button>
</form>
@else
<a href="{{ route('admin.profile.edit') }}" class="btn-outline shrink-0 whitespace-nowrap" title="Set a 4-digit PIN in your profile first">Set desk PIN</a>
@endif
<a href="{{ route('admin.attendance.setup', ['event' => $event, 'day' => $day->id, 'venue' => $venue?->id]) }}" class="btn-outline shrink-0 whitespace-nowrap">Change day / venue</a>
<a href="{{ route('admin.attendance.index') }}" class="btn-outline shrink-0 whitespace-nowrap">All events</a>
@endsection

@section('content')
<div
    id="attendance-desk"
    class="mx-auto w-full max-w-[1600px] space-y-4 sm:space-y-5"
    data-lookup-url="{{ route('admin.attendance.lookup', $event) }}"
    data-checkin-url="{{ route('admin.attendance.check-in', $event) }}"
    data-update-items-url="{{ route('admin.attendance.update-items', $event) }}"
    data-day-id="{{ $day->id }}"
    data-venue-required="{{ $event->venues->isNotEmpty() ? '1' : '0' }}"
    data-list-page="{{ $checkedIn->currentPage() }}"
    data-list-search="{{ $checkedInSearch }}"
    data-csrf="{{ csrf_token() }}"
>
    <section class="card p-4 sm:p-5">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="min-w-0">
                <p class="text-xs font-semibold uppercase tracking-wide text-muted">Step 2 of 2 · Attending</p>
                <h2 class="mt-1 font-display text-lg font-bold text-ink sm:text-xl">Reception session defaults</h2>
                <p class="mt-1 text-sm text-muted">
                    New check-ins use this day
                    @if ($venue)
                        and venue
                    @endif
                    until you change them.
                </p>
            </div>
            <div class="flex flex-wrap gap-2">
                <span class="badge-blue">{{ $day->dayLabel() }}</span>
                @if ($venue)
                    <span class="badge-green">{{ $venue->locationSummary() }}</span>
                @else
                    <span class="badge-muted">No venue required</span>
                @endif
                <span class="badge-muted">
                    Checked in today:
                    <span class="font-semibold text-ink" data-checked-count>{{ $checkedInTotal }}</span>
                </span>
            </div>
        </div>
        @if ($venue)
            <select id="venue" name="venue" class="sr-only" data-venue-select aria-hidden="true" tabindex="-1">
                <option value="{{ $venue->id }}" selected>{{ $venue->locationSummary() }}</option>
            </select>
        @endif
    </section>

    <div class="grid gap-4 sm:gap-5 xl:grid-cols-2 xl:items-start">
        <section class="card order-1 space-y-4 p-4 sm:p-5 xl:order-2 xl:sticky xl:top-4">
            <div>
                <h2 class="font-display text-base font-bold text-ink">Member preview</h2>
                <p class="mt-1 text-sm text-muted">Review details, tick items given, then check in.</p>
            </div>

            <div data-result-banner class="hidden rounded-xl px-3 py-3 text-sm font-semibold sm:px-4"></div>

            <div data-member-empty class="rounded-2xl border border-dashed border-slate-200 bg-surface/60 px-4 py-10 text-center text-sm text-muted sm:py-12">
                Scan a QR code or search a member to load details.
            </div>

            <div data-member-card class="hidden space-y-4">
                <div class="flex items-start gap-3 sm:gap-4">
                    <div data-member-photo-wrap class="hidden h-16 w-16 shrink-0 overflow-hidden rounded-2xl border border-slate-200 bg-white sm:h-20 sm:w-20">
                        <img data-member-photo alt="" class="h-full w-full object-cover">
                    </div>
                    <div class="min-w-0 flex-1">
                        <p data-member-name class="break-words font-display text-lg font-bold text-ink sm:text-xl"></p>
                        <p data-member-unique class="mt-1 break-all text-sm font-semibold text-brand-blue"></p>
                        <p data-member-meta class="mt-2 break-words text-sm text-muted"></p>
                    </div>
                </div>

                <div class="grid gap-3 sm:grid-cols-2">
                    <div class="rounded-xl bg-surface p-3">
                        <p class="text-[11px] font-semibold uppercase tracking-wide text-muted">NIC</p>
                        <p data-member-nic class="mt-1 break-all text-sm font-semibold text-ink"></p>
                    </div>
                    <div class="rounded-xl bg-surface p-3">
                        <p class="text-[11px] font-semibold uppercase tracking-wide text-muted">Participation</p>
                        <p data-member-mode class="mt-1 text-sm font-semibold text-ink"></p>
                    </div>
                    <div class="rounded-xl bg-surface p-3 sm:col-span-2">
                        <p class="text-[11px] font-semibold uppercase tracking-wide text-muted">Institute</p>
                        <p data-member-institute class="mt-1 break-words text-sm font-semibold text-ink"></p>
                    </div>
                </div>

                @if ($event->venues->isNotEmpty() && $venue)
                    <div class="rounded-xl bg-surface px-3 py-2.5 text-sm">
                        <p class="text-[11px] font-semibold uppercase tracking-wide text-muted">Check-in venue</p>
                        <p class="mt-1 font-semibold text-ink">{{ $venue->locationSummary() }}</p>
                    </div>
                    <select id="checkin-venue" data-checkin-venue class="sr-only" aria-hidden="true" tabindex="-1">
                        <option value="{{ $venue->id }}" selected>{{ $venue->locationSummary() }}</option>
                    </select>
                @endif

                @if ($checkInItems->isNotEmpty())
                    <div data-checkin-items>
                        <div class="flex items-center justify-between gap-2">
                            <p class="form-label mb-0">Items given at check-in</p>
                            <button type="button" data-items-select-all class="text-xs font-semibold text-brand-blue hover:underline">Select all</button>
                        </div>
                        <div class="mt-2 grid gap-2 sm:grid-cols-2">
                            @foreach ($checkInItems as $item)
                                <label class="flex cursor-pointer items-start gap-2 rounded-xl border border-slate-200 bg-surface/50 px-3 py-2.5 text-sm text-ink hover:border-brand-green/40">
                                    <input
                                        type="checkbox"
                                        data-checkin-item
                                        value="{{ $item->id }}"
                                        class="mt-0.5 h-4 w-4 rounded border-slate-300 text-brand-green focus:ring-brand-green"
                                    >
                                    <span>{{ $item->name }}</span>
                                </label>
                            @endforeach
                        </div>
                        <p class="mt-1 text-xs text-muted">Tick each item handed to this member.</p>
                    </div>
                @endif

                <button
                    type="button"
                    data-checkin-btn
                    data-label-checkin="Check in for {{ $day->dayLabel() }}"
                    data-label-update="Update items given"
                    class="btn-primary w-full justify-center py-3 text-sm sm:text-base"
                    disabled
                >
                    Check in for {{ $day->dayLabel() }}
                </button>
            </div>
        </section>

        <div class="order-2 space-y-4 sm:space-y-5 xl:order-1">
            <section class="card space-y-3 p-4 sm:space-y-4 sm:p-5">
                <div>
                    <h2 class="font-display text-base font-bold text-ink">Scan QR code</h2>
                    <p class="mt-1 text-sm text-muted">Point the camera at the member’s unique ID QR code.</p>
                </div>

                <div id="qr-reader" class="attendance-qr-reader overflow-hidden rounded-2xl border border-slate-200 bg-slate-950"></div>
                <p data-scan-status class="text-xs font-semibold text-muted">Starting camera…</p>
            </section>

            <section class="card space-y-3 p-4 sm:space-y-4 sm:p-5">
                <div>
                    <h2 class="font-display text-base font-bold text-ink">Manual member search</h2>
                    <p class="mt-1 text-sm text-muted">Find by unique ID, NIC, name, or mobile.</p>
                </div>

                <div class="flex flex-col gap-2 sm:flex-row">
                    <input
                        type="text"
                        data-manual-code
                        class="form-input min-w-0 flex-1"
                        placeholder="Unique ID, NIC, name, or mobile…"
                        autocomplete="off"
                        spellcheck="false"
                    >
                    <button type="button" data-manual-lookup class="btn-secondary w-full shrink-0 justify-center sm:w-auto">Search</button>
                </div>

                <div data-match-list class="hidden max-h-64 space-y-2 overflow-y-auto rounded-xl border border-slate-200 bg-surface/40 p-3 sm:max-h-80"></div>
            </section>
        </div>
    </div>

    <section class="card overflow-hidden">
        <div class="border-b border-slate-100 px-4 py-4 sm:px-5">
            <div class="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
                <div class="min-w-0">
                    <h2 class="font-display text-base font-bold text-ink">All check-ins — {{ $day->dayLabel() }}</h2>
                    <p class="mt-1 text-sm text-muted">
                        @if ($checkedInSearch !== '')
                            Showing {{ $checkedIn->total() }} match{{ $checkedIn->total() === 1 ? '' : 'es' }} of {{ $checkedInTotal }} check-in{{ $checkedInTotal === 1 ? '' : 's' }} across all venues.
                        @else
                            All desks and venues for this day. Reception officer is who processed each check-in.
                        @endif
                    </p>
                </div>

                <form method="GET" action="{{ route('admin.attendance.desk', $event) }}" class="flex w-full flex-col gap-2 sm:flex-row lg:w-auto lg:min-w-[24rem]" data-auto-filter>
                    <input
                        type="search"
                        name="search"
                        value="{{ $checkedInSearch }}"
                        class="form-input min-w-0 flex-1"
                        placeholder="Search member, venue, or officer…"
                        data-auto-filter-search
                    >
                    @if ($checkedInSearch !== '')
                        <a
                            href="{{ route('admin.attendance.desk', $event) }}"
                            class="btn-outline w-full shrink-0 justify-center sm:w-auto"
                        >Clear</a>
                    @endif
                </form>
            </div>
        </div>

        <div class="space-y-3 p-4 md:hidden" data-checked-cards>
            @forelse ($checkedIn as $row)
                <article class="rounded-xl border border-slate-200 bg-surface/40 p-3.5" data-attendance-id="{{ $row->id }}">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="truncate font-semibold text-ink">{{ $row->member?->displayName() ?: '—' }}</p>
                            <p class="mt-0.5 break-all text-xs font-semibold text-brand-blue">{{ $row->member?->unique_id ?: '—' }}</p>
                        </div>
                        <div class="shrink-0 text-right">
                            <p class="text-xs text-muted">{{ \App\Support\SriLankaDate::datetime($row->checked_in_at) }}</p>
                            @if ($row->member)
                                <a href="{{ route('admin.checked-in.show', $row->member) }}" class="mt-2 inline-flex text-xs font-semibold text-brand-blue underline">View profile</a>
                            @endif
                        </div>
                    </div>
                    <dl class="mt-3 grid gap-2 text-sm">
                        <div class="flex gap-2">
                            <dt class="w-20 shrink-0 text-xs font-semibold uppercase tracking-wide text-muted">Venue</dt>
                            <dd class="min-w-0 break-words text-ink">{{ $row->venue?->locationSummary() ?: '—' }}</dd>
                        </div>
                        <div class="flex gap-2">
                            <dt class="w-20 shrink-0 text-xs font-semibold uppercase tracking-wide text-muted">Items</dt>
                            <dd class="min-w-0 break-words text-ink" data-attendance-items>
                                @if ($row->checkInItems->isNotEmpty())
                                    {{ $row->checkInItems->pluck('name')->join(', ') }}
                                @else
                                    —
                                @endif
                            </dd>
                        </div>
                        <div class="flex gap-2">
                            <dt class="w-20 shrink-0 text-xs font-semibold uppercase tracking-wide text-muted">Officer</dt>
                            <dd class="min-w-0">
                                <span class="font-semibold text-ink">{{ $row->checkedInBy?->name ?: '—' }}</span>
                                @if ($row->checkedInBy?->role)
                                    <span class="block text-xs text-muted">{{ \App\Support\UserRole::label($row->checkedInBy->role) }}</span>
                                @endif
                            </dd>
                        </div>
                    </dl>
                </article>
            @empty
                <p data-empty-cards class="rounded-xl border border-dashed border-slate-200 px-4 py-10 text-center text-sm text-muted">
                    @if ($checkedInSearch !== '')
                        No checked-in members match “{{ $checkedInSearch }}”.
                    @else
                        No check-ins for this day yet from any attendance desk.
                    @endif
                </p>
            @endforelse
        </div>

        <div class="table-wrap hidden md:block">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Member</th>
                        <th>Unique ID</th>
                        <th>Venue / desk</th>
                        <th>Items given</th>
                        <th>Checked in</th>
                        <th>Reception officer</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody data-checked-list>
                    @forelse ($checkedIn as $row)
                        <tr data-attendance-id="{{ $row->id }}">
                            <td class="font-semibold text-ink">{{ $row->member?->displayName() ?: '—' }}</td>
                            <td class="text-muted">{{ $row->member?->unique_id ?: '—' }}</td>
                            <td class="text-muted">{{ $row->venue?->locationSummary() ?: '—' }}</td>
                            <td class="text-muted" data-attendance-items>
                                @if ($row->checkInItems->isNotEmpty())
                                    <div class="flex max-w-xs flex-wrap gap-1">
                                        @foreach ($row->checkInItems as $item)
                                            <span class="badge-muted">{{ $item->name }}</span>
                                        @endforeach
                                    </div>
                                @else
                                    —
                                @endif
                            </td>
                            <td class="text-muted whitespace-nowrap">{{ \App\Support\SriLankaDate::datetime($row->checked_in_at) }}</td>
                            <td>
                                <span class="font-semibold text-ink">{{ $row->checkedInBy?->name ?: '—' }}</span>
                                @if ($row->checkedInBy?->role)
                                    <span class="mt-0.5 block text-xs text-muted">{{ \App\Support\UserRole::label($row->checkedInBy->role) }}</span>
                                @endif
                            </td>
                            <td>
                                @if ($row->member)
                                    <a href="{{ route('admin.checked-in.show', $row->member) }}" class="btn-outline">View</a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr data-empty-row>
                            <td colspan="7" class="py-10 text-center text-muted">
                                @if ($checkedInSearch !== '')
                                    No checked-in members match “{{ $checkedInSearch }}”.
                                @else
                                    No check-ins for this day yet from any attendance desk.
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($checkedIn->hasPages())
            <div class="overflow-x-auto border-t border-slate-100 px-3 py-3 sm:px-5">
                {{ $checkedIn->links() }}
            </div>
        @endif
    </section>
</div>
@endsection
