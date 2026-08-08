@extends('layouts.dashboard')

@section('title', 'Activity Log')
@section('page-title', 'Activity Log')
@section('page-subtitle', 'Full system activity with Sri Lanka date & time')

@section('content')
<div class="card">
    <form method="GET" action="{{ route('admin.activity-logs.index') }}" class="grid gap-3 border-b border-slate-100 p-4 sm:grid-cols-2 lg:grid-cols-6">
        <div class="sm:col-span-2 lg:col-span-2">
            <label for="search" class="sr-only">Search</label>
            <input id="search" type="search" name="search" value="{{ request('search') }}" class="form-input" placeholder="Search description, user, IP…">
        </div>
        <div>
            <select name="action" class="form-select">
                <option value="">All actions</option>
                @foreach ($actions as $action)
                    <option value="{{ $action }}" @selected(request('action') === $action)>{{ ucfirst(str_replace('_', ' ', $action)) }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <select name="guard" class="form-select">
                <option value="">All areas</option>
                <option value="web" @selected(request('guard') === 'web')>System users</option>
                <option value="member" @selected(request('guard') === 'member')>Members</option>
            </select>
        </div>
        <div>
            <input type="date" name="date_from" value="{{ request('date_from') }}" class="form-input" title="From date">
        </div>
        <div class="flex gap-2">
            <input type="date" name="date_to" value="{{ request('date_to') }}" class="form-input" title="To date">
            <button type="submit" class="btn-primary shrink-0">Filter</button>
        </div>
    </form>

    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Date &amp; time</th>
                    <th>Actor</th>
                    <th>Action</th>
                    <th>Description</th>
                    <th>Subject</th>
                    <th>IP</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($logs as $log)
                    <tr>
                        <td class="whitespace-nowrap text-muted">
                            <span class="font-medium text-ink">{{ \App\Support\SriLankaDate::date($log->created_at) }}</span>
                            <br>
                            <span class="text-xs">{{ \App\Support\SriLankaDate::format($log->created_at, \App\Support\SriLankaDate::TIME) }}</span>
                        </td>
                        <td>
                            <p class="font-semibold text-ink">{{ $log->causer_name ?: 'Guest / system' }}</p>
                            <p class="text-xs text-muted">
                                {{ $log->guardLabel() }}
                                @if ($log->causer_role)
                                    · {{ $log->causer_role }}
                                @endif
                            </p>
                        </td>
                        <td>
                            @php
                                $badge = match ($log->action) {
                                    'login' => 'badge-green',
                                    'logout' => 'badge-muted',
                                    'login_failed' => 'badge-orange',
                                    'created', 'registered' => 'badge-blue',
                                    'deleted', 'rejected' => 'badge-orange',
                                    'approved' => 'badge-green',
                                    'password_changed', 'password_reset' => 'badge-blue',
                                    default => 'badge-muted',
                                };
                            @endphp
                            <span class="{{ $badge }}">{{ $log->actionLabel() }}</span>
                        </td>
                        <td class="max-w-xs text-sm text-ink">{{ $log->description }}</td>
                        <td class="text-sm text-muted">{{ $log->subject_label ?: '—' }}</td>
                        <td class="whitespace-nowrap text-xs text-muted">{{ $log->ip_address ?: '—' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="py-14 text-center text-muted">No activity recorded yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($logs->hasPages())
        <div class="border-t border-slate-100 px-4 py-3">{{ $logs->links() }}</div>
    @endif
</div>
@endsection
