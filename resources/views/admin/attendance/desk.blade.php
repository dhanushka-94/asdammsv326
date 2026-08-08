@extends('layouts.dashboard')

@section('title', 'Attendance — '.$event->name)
@section('page-title', 'Attendance desk')
@section('page-subtitle', $event->name)

@section('page-actions')
<a href="{{ route('admin.attendance.index') }}" class="btn-outline">All events</a>
@endsection

@section('content')
<div
    id="attendance-desk"
    class="mx-auto max-w-6xl space-y-5"
    data-lookup-url="{{ route('admin.attendance.lookup', $event) }}"
    data-checkin-url="{{ route('admin.attendance.check-in', $event) }}"
    data-day-id="{{ $day->id }}"
    data-venue-required="{{ $event->venues->isNotEmpty() ? '1' : '0' }}"
    data-csrf="{{ csrf_token() }}"
>
    <section class="card p-4 sm:p-5">
        <form method="GET" action="{{ route('admin.attendance.desk', $event) }}" class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3 lg:items-end">
            <div>
                <label for="day" class="form-label">Event day</label>
                <select id="day" name="day" class="form-select" onchange="this.form.submit()">
                    @foreach ($event->days as $eventDay)
                        <option value="{{ $eventDay->id }}" @selected($eventDay->id === $day->id)>{{ $eventDay->dayLabel() }}</option>
                    @endforeach
                </select>
            </div>

            @if ($event->venues->isNotEmpty())
                <div>
                    <label for="venue" class="form-label">Check-in venue</label>
                    <select id="venue" name="venue" class="form-select" data-venue-select onchange="this.form.submit()">
                        @foreach ($event->venues as $eventVenue)
                            <option value="{{ $eventVenue->id }}" @selected($venue && $eventVenue->id === $venue->id)>
                                {{ $eventVenue->locationSummary() }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @else
                <div class="rounded-xl border border-dashed border-slate-200 bg-surface/60 px-4 py-3 text-sm text-muted">
                    No venues on this event — venue is not required for check-in.
                </div>
            @endif

            <p class="text-sm text-muted lg:pb-2">
                Checked in on this day: <span class="font-semibold text-ink" data-checked-count>{{ $checkedIn->count() }}</span>
            </p>
        </form>
    </section>

    <div class="grid gap-5 lg:grid-cols-2">
        <div class="space-y-5">
            <section class="card space-y-4 p-5">
                <div>
                    <h2 class="font-display text-base font-bold text-ink">Scan QR code</h2>
                    <p class="mt-1 text-sm text-muted">Point the camera at the member’s unique ID QR code.</p>
                </div>

                <div id="qr-reader" class="overflow-hidden rounded-2xl border border-slate-200 bg-slate-950"></div>
                <p data-scan-status class="text-xs font-semibold text-muted">Starting camera…</p>
            </section>

            <section class="card space-y-4 p-5">
                <div>
                    <h2 class="font-display text-base font-bold text-ink">Manual member search</h2>
                    <p class="mt-1 text-sm text-muted">Find a member by unique ID, NIC, name, or mobile number.</p>
                </div>

                <div class="flex gap-2">
                    <input
                        type="text"
                        data-manual-code
                        class="form-input flex-1"
                        placeholder="Unique ID, NIC, name, or mobile…"
                        autocomplete="off"
                        spellcheck="false"
                    >
                    <button type="button" data-manual-lookup class="btn-secondary shrink-0">Search</button>
                </div>

                <div data-match-list class="hidden space-y-2 rounded-xl border border-slate-200 bg-surface/40 p-3"></div>
            </section>
        </div>

        <section class="card space-y-4 p-5">
            <div>
                <h2 class="font-display text-base font-bold text-ink">Member preview</h2>
                <p class="mt-1 text-sm text-muted">Review details, confirm venue, then check in.</p>
            </div>

            <div data-result-banner class="hidden rounded-xl px-4 py-3 text-sm font-semibold"></div>

            <div data-member-empty class="rounded-2xl border border-dashed border-slate-200 bg-surface/60 px-4 py-12 text-center text-sm text-muted">
                Scan a QR code or search a member to load details.
            </div>

            <div data-member-card class="hidden space-y-4">
                <div class="flex items-start gap-4">
                    <div data-member-photo-wrap class="hidden h-20 w-20 shrink-0 overflow-hidden rounded-2xl border border-slate-200 bg-white">
                        <img data-member-photo alt="" class="h-full w-full object-cover">
                    </div>
                    <div class="min-w-0 flex-1">
                        <p data-member-name class="font-display text-xl font-bold text-ink"></p>
                        <p data-member-unique class="mt-1 text-sm font-semibold text-brand-blue"></p>
                        <p data-member-meta class="mt-2 text-sm text-muted"></p>
                    </div>
                </div>

                <div class="grid gap-3 sm:grid-cols-2">
                    <div class="rounded-xl bg-surface p-3">
                        <p class="text-[11px] font-semibold uppercase tracking-wide text-muted">NIC</p>
                        <p data-member-nic class="mt-1 text-sm font-semibold text-ink"></p>
                    </div>
                    <div class="rounded-xl bg-surface p-3">
                        <p class="text-[11px] font-semibold uppercase tracking-wide text-muted">Participation</p>
                        <p data-member-mode class="mt-1 text-sm font-semibold text-ink"></p>
                    </div>
                    <div class="rounded-xl bg-surface p-3 sm:col-span-2">
                        <p class="text-[11px] font-semibold uppercase tracking-wide text-muted">Institute</p>
                        <p data-member-institute class="mt-1 text-sm font-semibold text-ink"></p>
                    </div>
                </div>

                @if ($event->venues->isNotEmpty())
                    <div>
                        <label for="checkin-venue" class="form-label">Venue for this check-in</label>
                        <select id="checkin-venue" data-checkin-venue class="form-select">
                            @foreach ($event->venues as $eventVenue)
                                <option value="{{ $eventVenue->id }}" @selected($venue && $eventVenue->id === $venue->id)>
                                    {{ $eventVenue->locationSummary() }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <button type="button" data-checkin-btn class="btn-primary w-full justify-center py-3 text-base" disabled>
                    Check in for {{ $day->dayLabel() }}
                </button>
            </div>
        </section>
    </div>

    <section class="card overflow-hidden">
        <div class="border-b border-slate-100 px-5 py-4">
            <h2 class="font-display text-base font-bold text-ink">Checked in — {{ $day->dayLabel() }}</h2>
            <p class="mt-1 text-sm text-muted">Latest check-ins on this reception desk day.</p>
        </div>
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Member</th>
                        <th>Unique ID</th>
                        <th>Venue</th>
                        <th>Checked in</th>
                        <th>Officer</th>
                    </tr>
                </thead>
                <tbody data-checked-list>
                    @forelse ($checkedIn as $row)
                        <tr>
                            <td class="font-semibold text-ink">{{ $row->member?->displayName() ?: '—' }}</td>
                            <td class="text-muted">{{ $row->member?->unique_id ?: '—' }}</td>
                            <td class="text-muted">{{ $row->venue?->locationSummary() ?: '—' }}</td>
                            <td class="text-muted">{{ \App\Support\SriLankaDate::datetime($row->checked_in_at) }}</td>
                            <td class="text-muted">{{ $row->checkedInBy?->name ?: '—' }}</td>
                        </tr>
                    @empty
                        <tr data-empty-row>
                            <td colspan="5" class="py-10 text-center text-muted">No check-ins for this day yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
@endsection
