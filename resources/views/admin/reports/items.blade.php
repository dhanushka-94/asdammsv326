@extends('layouts.dashboard')

@section('title', 'Check-in item reports')
@section('page-title', 'Check-in item reports')
@section('page-subtitle', 'Handout items given at attendance desks')

@section('content')
    @include('admin.reports._nav')

    <form method="get" action="{{ route('admin.reports.items') }}" class="card mb-6 flex flex-wrap items-end gap-3 p-4">
        <div class="min-w-[220px] flex-1">
            <label for="event" class="mb-1 block text-sm font-medium text-ink">Filter by event</label>
            <select id="event" name="event" class="form-select w-full" onchange="this.form.submit()">
                <option value="">All events</option>
                @foreach ($events as $option)
                    <option value="{{ $option->id }}" @selected($selectedEventId === $option->id)>
                        {{ $option->name }}
                    </option>
                @endforeach
            </select>
        </div>
    </form>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
        <div class="stat-card">
            <p class="text-sm font-medium text-muted">Items given</p>
            <p class="mt-2 font-display text-3xl font-bold text-brand-green">{{ number_format($totalGiven) }}</p>
        </div>
        <div class="stat-card">
            <p class="text-sm font-medium text-muted">Catalog items</p>
            <p class="mt-2 font-display text-3xl font-bold text-brand-blue">{{ number_format($items->count()) }}</p>
        </div>
        <div class="stat-card">
            <p class="text-sm font-medium text-muted">Active items</p>
            <p class="mt-2 font-display text-3xl font-bold text-brand-orange">{{ number_format($activeItems) }}</p>
        </div>
    </div>

    <div class="mt-6">
        @include('admin.reports._chart-card', [
            'title' => 'Items handed out',
            'subtitle' => $selectedEventId ? 'For selected event' : 'Across all events',
            'type' => 'bar',
            'labels' => $charts['items_given']['labels'],
            'values' => $charts['items_given']['values'],
            'height' => 'h-80',
        ])
    </div>

    <div class="mt-6 grid gap-6 xl:grid-cols-2">
        <div class="card">
            <div class="border-b border-slate-100 px-5 py-4">
                <h2 class="font-display text-base font-bold text-ink">Item totals</h2>
            </div>
            <div class="table-wrap">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Status</th>
                            <th class="text-right">Given</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($items as $item)
                            <tr>
                                <td class="font-medium text-ink">{{ $item->name }}</td>
                                <td>
                                    <span class="{{ $item->is_active ? 'badge-green' : 'badge-muted' }}">
                                        {{ $item->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="text-right tabular-nums">{{ number_format($item->given_count) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="py-8 text-center text-muted">No check-in items configured.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card">
            <div class="border-b border-slate-100 px-5 py-4">
                <h2 class="font-display text-base font-bold text-ink">By event & item</h2>
            </div>
            <div class="table-wrap">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Event</th>
                            <th>Item</th>
                            <th class="text-right">Given</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($byEvent as $row)
                            <tr>
                                <td class="font-medium text-ink">{{ $row->event_name }}</td>
                                <td>{{ $row->item_name }}</td>
                                <td class="text-right tabular-nums">{{ number_format($row->total) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="py-8 text-center text-muted">No items handed out yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
