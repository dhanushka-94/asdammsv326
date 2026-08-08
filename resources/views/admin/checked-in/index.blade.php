@extends('layouts.dashboard')

@section('title', 'Checked-in members')
@section('page-title', 'Checked-in members')
@section('page-subtitle', 'Search check-ins and open full member details')

@section('page-actions')
<a href="{{ route('admin.attendance.index') }}" class="btn-outline">Attendance desk</a>
@endsection

@section('content')
<div class="mb-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
    <div class="stat-card">
        <p class="text-sm font-medium text-muted">Check-in records</p>
        <p class="mt-2 font-display text-3xl font-bold text-brand-green">{{ number_format($totalCheckIns) }}</p>
    </div>
    <div class="stat-card">
        <p class="text-sm font-medium text-muted">Unique members</p>
        <p class="mt-2 font-display text-3xl font-bold text-brand-blue">{{ number_format($uniqueMembers) }}</p>
    </div>
    <div class="stat-card">
        <p class="text-sm font-medium text-muted">Showing</p>
        <p class="mt-2 font-display text-3xl font-bold text-brand-orange">{{ number_format($checkIns->total()) }}</p>
        <p class="mt-2 text-xs text-muted">After current filters</p>
    </div>
</div>

<div class="card">
    <form method="GET" action="{{ route('admin.checked-in.index') }}" class="grid gap-3 border-b border-slate-100 p-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="sm:col-span-2">
            <label for="search" class="sr-only">Search</label>
            <input
                id="search"
                type="search"
                name="search"
                value="{{ request('search') }}"
                class="form-input"
                placeholder="Search name, NIC, Unique ID, mobile, event, venue, officer, item…"
                autofocus
            >
        </div>
        <div>
            <select name="event" class="form-select" onchange="this.form.submit()">
                <option value="">All events</option>
                @foreach ($events as $event)
                    <option value="{{ $event->id }}" @selected($selectedEventId === $event->id)>{{ $event->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex gap-2">
            <button type="submit" class="btn-primary flex-1">Search</button>
            @if (request()->hasAny(['search', 'event']))
                <a href="{{ route('admin.checked-in.index') }}" class="btn-outline">Clear</a>
            @endif
        </div>
    </form>

    {{-- Mobile cards --}}
    <div class="divide-y divide-slate-100 lg:hidden">
        @forelse ($checkIns as $attendance)
            @php($member = $attendance->member)
            <a href="{{ route('admin.checked-in.show', $member) }}" class="block px-4 py-4 transition hover:bg-slate-50/80">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <p class="font-semibold text-ink">{{ $member?->displayName() ?? 'Unknown member' }}</p>
                        <p class="text-xs text-muted">{{ $member?->unique_id ?: '—' }} · {{ $member?->nic }}</p>
                        <p class="mt-1 text-sm text-muted">{{ $attendance->event?->name }}</p>
                        <p class="mt-0.5 text-xs text-muted">
                            Day {{ $attendance->day?->day_number ?? '—' }}
                            @if ($attendance->venue)
                                · {{ $attendance->venue->name }}
                            @endif
                        </p>
                        @if ($attendance->checkInItems->isNotEmpty())
                            <p class="mt-2 flex flex-wrap gap-1">
                                @foreach ($attendance->checkInItems as $item)
                                    <span class="badge-blue">{{ $item->name }}</span>
                                @endforeach
                            </p>
                        @endif
                    </div>
                    <div class="shrink-0 text-right">
                        <span class="badge-green">Checked in</span>
                        <p class="mt-2 text-xs text-muted">{{ \App\Support\SriLankaDate::format($attendance->checked_in_at, 'd M, h:i A') }}</p>
                    </div>
                </div>
            </a>
        @empty
            <div class="px-4 py-14 text-center text-muted">No checked-in members found.</div>
        @endforelse
    </div>

    {{-- Desktop table --}}
    <div class="table-wrap hidden lg:block">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Member</th>
                    <th>Event / day</th>
                    <th>Venue</th>
                    <th>Items</th>
                    <th>Checked in</th>
                    <th>Officer</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($checkIns as $attendance)
                    @php($member = $attendance->member)
                    <tr>
                        <td>
                            <p class="font-semibold text-ink">{{ $member?->displayName() ?? 'Unknown' }}</p>
                            <p class="text-xs text-muted">{{ $member?->unique_id ?: '—' }} · {{ $member?->nic }}</p>
                            @if ($member?->designation)
                                <p class="text-xs text-muted">{{ $member->designation->name }}</p>
                            @endif
                        </td>
                        <td>
                            <p class="font-medium text-ink">{{ $attendance->event?->name }}</p>
                            <p class="text-xs text-muted">Day {{ $attendance->day?->day_number ?? '—' }}</p>
                        </td>
                        <td class="text-muted">{{ $attendance->venue?->name ?: '—' }}</td>
                        <td>
                            @forelse ($attendance->checkInItems as $item)
                                <span class="badge-blue mr-1 mb-1 inline-flex">{{ $item->name }}</span>
                            @empty
                                <span class="text-muted">—</span>
                            @endforelse
                        </td>
                        <td class="whitespace-nowrap text-muted">
                            {{ \App\Support\SriLankaDate::format($attendance->checked_in_at, 'd M Y') }}
                            <br>
                            <span class="text-xs">{{ \App\Support\SriLankaDate::format($attendance->checked_in_at, 'h:i A') }}</span>
                        </td>
                        <td class="text-sm text-muted">{{ $attendance->checkedInBy?->name ?: '—' }}</td>
                        <td>
                            <a href="{{ route('admin.checked-in.show', $member) }}" class="btn-outline">View</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="py-14 text-center text-muted">No checked-in members found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($checkIns->hasPages())
        <div class="border-t border-slate-100 px-4 py-3">{{ $checkIns->links() }}</div>
    @endif
</div>
@endsection
