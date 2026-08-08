@extends('layouts.dashboard')

@section('title', 'Attendance reports')
@section('page-title', 'Attendance reports')
@section('page-subtitle', 'Check-ins by day, venue, mode, and officer')

@section('content')
    @include('admin.reports._nav')

    <form method="get" action="{{ route('admin.reports.attendance') }}" class="card mb-6 flex flex-wrap items-end gap-3 p-4">
        <div class="min-w-[220px] flex-1">
            <label for="event" class="mb-1 block text-sm font-medium text-ink">Event</label>
            <select id="event" name="event" class="form-select w-full" onchange="this.form.submit()">
                @forelse ($events as $option)
                    <option value="{{ $option->id }}" @selected($event?->id === $option->id)>
                        {{ $option->name }}
                        ({{ $option->active_enrollments_count }} enrolled)
                    </option>
                @empty
                    <option value="">No events</option>
                @endforelse
            </select>
        </div>
        @if ($event)
            <p class="pb-2 text-sm text-muted">
                {{ \App\Support\SriLankaDate::format($event->start_date, 'd M Y') }}
                @if ($event->end_date && ! $event->end_date->isSameDay($event->start_date))
                    – {{ \App\Support\SriLankaDate::format($event->end_date, 'd M Y') }}
                @endif
            </p>
        @endif
    </form>

    @if (! $event)
        <div class="card p-10 text-center text-muted">Create an event to see attendance analytics.</div>
    @else
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <div class="stat-card">
                <p class="text-sm font-medium text-muted">Enrollments</p>
                <p class="mt-2 font-display text-3xl font-bold text-brand-blue">{{ number_format($summary['enrollments']) }}</p>
            </div>
            <div class="stat-card">
                <p class="text-sm font-medium text-muted">Check-ins</p>
                <p class="mt-2 font-display text-3xl font-bold text-brand-green">{{ number_format($summary['check_ins']) }}</p>
            </div>
            <div class="stat-card">
                <p class="text-sm font-medium text-muted">Event days</p>
                <p class="mt-2 font-display text-3xl font-bold text-brand-orange">{{ number_format($summary['days']) }}</p>
            </div>
            <div class="stat-card">
                <p class="text-sm font-medium text-muted">Venues</p>
                <p class="mt-2 font-display text-3xl font-bold text-brand-blue">{{ number_format($summary['venues']) }}</p>
            </div>
        </div>

        <div class="mt-6 grid gap-6 xl:grid-cols-2">
            @include('admin.reports._chart-card', [
                'title' => 'Check-ins by day',
                'type' => 'bar',
                'labels' => $charts['by_day']['labels'],
                'values' => $charts['by_day']['values'],
            ])
            @include('admin.reports._chart-card', [
                'title' => 'Check-ins by venue',
                'type' => 'doughnut',
                'labels' => $charts['by_venue']['labels'],
                'values' => $charts['by_venue']['values'],
            ])
        </div>

        <div class="mt-6 grid gap-6 xl:grid-cols-2">
            @include('admin.reports._chart-card', [
                'title' => 'Daily check-in trend',
                'type' => 'line',
                'labels' => $charts['daily_trend']['labels'],
                'values' => $charts['daily_trend']['values'],
            ])
            @include('admin.reports._chart-card', [
                'title' => 'Participation mode',
                'subtitle' => 'Active enrollments',
                'type' => 'doughnut',
                'labels' => $charts['by_mode']['labels'],
                'values' => $charts['by_mode']['values'],
            ])
        </div>

        <div class="mt-6">
            @include('admin.reports._chart-card', [
                'title' => 'Check-ins by reception officer',
                'type' => 'bar',
                'labels' => $charts['by_officer']['labels'],
                'values' => $charts['by_officer']['values'],
            ])
        </div>

        <div class="mt-6 grid gap-6 xl:grid-cols-2">
            <div class="card">
                <div class="border-b border-slate-100 px-5 py-4">
                    <h2 class="font-display text-base font-bold text-ink">Day breakdown</h2>
                </div>
                <div class="table-wrap">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Day</th>
                                <th>Notes</th>
                                <th class="text-right">Check-ins</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($dayBreakdown as $row)
                                <tr>
                                    <td class="font-medium text-ink">Day {{ $row->day_number }}</td>
                                    <td class="text-muted">{{ $row->description ?: '—' }}</td>
                                    <td class="text-right tabular-nums">{{ number_format($row->total) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="py-8 text-center text-muted">No check-ins yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card">
                <div class="border-b border-slate-100 px-5 py-4">
                    <h2 class="font-display text-base font-bold text-ink">Venue breakdown</h2>
                </div>
                <div class="table-wrap">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Venue</th>
                                <th class="text-right">Check-ins</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($venueBreakdown as $row)
                                <tr>
                                    <td class="font-medium text-ink">{{ $row->label }}</td>
                                    <td class="text-right tabular-nums">{{ number_format($row->total) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="2" class="py-8 text-center text-muted">No check-ins yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="mt-6 grid gap-6 xl:grid-cols-2">
            <div class="card">
                <div class="border-b border-slate-100 px-5 py-4">
                    <h2 class="font-display text-base font-bold text-ink">Participation modes</h2>
                </div>
                <div class="table-wrap">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Mode</th>
                                <th class="text-right">Enrollments</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($modeBreakdown as $row)
                                <tr>
                                    <td class="font-medium text-ink">{{ ucfirst($row->label) }}</td>
                                    <td class="text-right tabular-nums">{{ number_format($row->total) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="2" class="py-8 text-center text-muted">No enrollments.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card">
                <div class="border-b border-slate-100 px-5 py-4">
                    <h2 class="font-display text-base font-bold text-ink">Officer leaderboard</h2>
                </div>
                <div class="table-wrap">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Officer</th>
                                <th class="text-right">Check-ins</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($officerBreakdown as $row)
                                <tr>
                                    <td class="font-medium text-ink">{{ $row->label }}</td>
                                    <td class="text-right tabular-nums">{{ number_format($row->total) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="2" class="py-8 text-center text-muted">No check-ins yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif
@endsection
