@extends('layouts.dashboard')

@section('title', 'My Profile')
@section('page-title', 'My Profile')
@section('page-subtitle', 'Your account and personal activity')

@section('page-actions')
<a href="{{ route('admin.profile.edit') }}" class="btn-secondary">Edit profile</a>
@endsection

@section('content')
<div class="mx-auto max-w-6xl space-y-5">
    <div class="card overflow-hidden">
        <div class="bg-gradient-to-r from-brand-blue via-brand-green to-brand-orange px-5 py-8 sm:px-8">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center">
                @if ($user->profileImageUrl())
                    <img src="{{ $user->profileImageUrl() }}" alt="" class="h-16 w-16 rounded-2xl border-2 border-white/40 object-cover">
                @else
                    <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-white/15 text-2xl font-bold text-white backdrop-blur">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </div>
                @endif
                <div class="text-white">
                    <h2 class="font-display text-2xl font-bold">{{ $user->name }}</h2>
                    <p class="text-white/80">{{ $user->email }}</p>
                </div>
            </div>
        </div>

        <div class="grid gap-4 p-5 sm:grid-cols-2 sm:p-8">
            <div class="rounded-xl bg-surface p-4">
                <p class="text-xs font-semibold uppercase tracking-wide text-muted">Phone</p>
                <p class="mt-1 font-semibold text-ink">{{ $user->phone ?: 'Not provided' }}</p>
            </div>
            <div class="rounded-xl bg-surface p-4">
                <p class="text-xs font-semibold uppercase tracking-wide text-muted">Role</p>
                <p class="mt-2">
                    @if ($user->role === \App\Support\UserRole::SUPER_ADMIN)
                        <span class="badge-orange">{{ $user->roleLabel() }}</span>
                    @elseif ($user->role === \App\Support\UserRole::ADMIN)
                        <span class="badge-blue">{{ $user->roleLabel() }}</span>
                    @else
                        <span class="badge-green">{{ $user->roleLabel() }}</span>
                    @endif
                </p>
            </div>
            <div class="rounded-xl bg-surface p-4">
                <p class="text-xs font-semibold uppercase tracking-wide text-muted">Status</p>
                <p class="mt-2">
                    @if ($user->status === 'active')
                        <span class="badge-green">Active</span>
                    @else
                        <span class="badge-muted">Inactive</span>
                    @endif
                </p>
            </div>
            <div class="rounded-xl bg-surface p-4">
                <p class="text-xs font-semibold uppercase tracking-wide text-muted">Member since</p>
                <p class="mt-1 font-semibold text-ink">{{ \App\Support\SriLankaDate::datetime($user->created_at) }}</p>
            </div>
            @if ($user->canAccessAttendance())
                <div class="rounded-xl bg-surface p-4 sm:col-span-2">
                    <p class="text-xs font-semibold uppercase tracking-wide text-muted">Attendance desk PIN</p>
                    <p class="mt-1 font-semibold text-ink">
                        @if ($user->hasDeskPin())
                            Set — use Lock desk on the attendance screen for a quick lock
                        @else
                            Not set
                        @endif
                    </p>
                    <a href="{{ route('admin.profile.edit') }}" class="mt-2 inline-flex text-sm font-semibold text-brand-blue underline">
                        {{ $user->hasDeskPin() ? 'Change desk PIN' : 'Set desk PIN' }}
                    </a>
                </div>
            @endif
        </div>
    </div>

    <div class="card" id="activity">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 px-5 py-4">
            <div>
                <h2 class="font-display text-base font-bold text-ink">My activity log</h2>
                <p class="text-sm text-muted">Actions you performed in the system</p>
            </div>
            @if ($user->canManageUsers())
                <a href="{{ route('admin.activity-logs.index') }}" class="btn-outline">Full system log</a>
            @endif
        </div>

        <form method="GET" action="{{ route('admin.profile.show') }}" class="grid gap-3 border-b border-slate-100 p-4 sm:grid-cols-2 lg:grid-cols-5">
            <div class="sm:col-span-2 lg:col-span-2">
                <label for="activity-search" class="sr-only">Search activity</label>
                <input
                    id="activity-search"
                    type="search"
                    name="search"
                    value="{{ request('search') }}"
                    class="form-input"
                    placeholder="Search description, subject, IP…"
                >
            </div>
            <div>
                <select name="action" class="form-select">
                    <option value="">All actions</option>
                    @foreach ($activityActions as $action)
                        <option value="{{ $action }}" @selected(request('action') === $action)>
                            {{ ucfirst(str_replace('_', ' ', $action)) }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <input type="date" name="date_from" value="{{ request('date_from') }}" class="form-input" title="From date">
            </div>
            <div class="flex gap-2">
                <input type="date" name="date_to" value="{{ request('date_to') }}" class="form-input" title="To date">
                <button type="submit" class="btn-primary shrink-0">Filter</button>
                @if (request()->hasAny(['search', 'action', 'date_from', 'date_to']))
                    <a href="{{ route('admin.profile.show') }}#activity" class="btn-outline shrink-0">Clear</a>
                @endif
            </div>
        </form>

        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Date &amp; time</th>
                        <th>Action</th>
                        <th>Description</th>
                        <th>Subject</th>
                        <th>IP</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($activityLogs as $log)
                        <tr>
                            <td class="whitespace-nowrap text-muted">
                                <span class="font-medium text-ink">{{ \App\Support\SriLankaDate::date($log->created_at) }}</span>
                                <br>
                                <span class="text-xs">{{ \App\Support\SriLankaDate::format($log->created_at, \App\Support\SriLankaDate::TIME) }}</span>
                            </td>
                            <td>
                                <span class="{{ $log->badgeClass() }}">{{ $log->actionLabel() }}</span>
                            </td>
                            <td class="max-w-sm text-sm text-ink">{{ $log->description }}</td>
                            <td class="text-sm text-muted">{{ $log->subject_label ?: '—' }}</td>
                            <td class="whitespace-nowrap text-xs text-muted">{{ $log->ip_address ?: '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-14 text-center text-muted">No activity recorded for your account yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($activityLogs->hasPages())
            <div class="border-t border-slate-100 px-4 py-3">{{ $activityLogs->fragment('activity')->links() }}</div>
        @endif
    </div>
</div>
@endsection
