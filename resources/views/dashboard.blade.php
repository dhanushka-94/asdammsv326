@extends('layouts.dashboard')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')
@section('page-subtitle', 'Live overview of members, events, and attendance')

@section('content')
<div class="card overflow-hidden">
    <div class="flex flex-wrap items-center justify-between gap-4 border-b border-slate-100 bg-gradient-to-r from-brand-blue-soft/80 via-white to-brand-green-soft/50 px-5 py-5">
        <div>
            <p class="text-sm font-medium text-muted">{{ \App\Support\SriLankaDate::format(now(), 'l, d F Y') }}</p>
            <h2 class="mt-1 font-display text-xl font-bold text-ink">
                Welcome back{{ $user?->name ? ', '.$user->name : '' }}
            </h2>
            <p class="mt-1 text-sm text-muted">Here’s what’s happening across ASDA MMS right now.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('admin.reports.index') }}" class="btn-outline">Open reports</a>
            @if (auth()->user()->canAccessAttendance())
                <a href="{{ route('admin.attendance.index') }}" class="btn-primary">Attendance desk</a>
            @endif
        </div>
    </div>

    @if ($stats['pending_members'] > 0)
        <div class="flex flex-wrap items-center justify-between gap-3 bg-brand-orange-soft/70 px-5 py-3.5">
            <p class="text-sm font-semibold text-brand-orange">
                {{ number_format($stats['pending_members']) }} member{{ $stats['pending_members'] === 1 ? '' : 's' }} waiting for approval
            </p>
            <a href="{{ route('admin.waiting-approvals.index') }}" class="btn-accent">Review now</a>
        </div>
    @endif
</div>

<div class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-6">
    <a href="{{ route('admin.members.index') }}" class="stat-card transition hover:border-brand-blue/30 hover:shadow-md">
        <p class="text-sm font-medium text-muted">Total members</p>
        <p class="mt-2 font-display text-3xl font-bold text-brand-blue">{{ number_format($stats['total_members']) }}</p>
        <p class="mt-2 text-xs text-muted">{{ number_format($stats['members_today']) }} registered today</p>
    </a>
    <a href="{{ route('admin.waiting-approvals.index') }}" class="stat-card transition hover:border-brand-orange/30 hover:shadow-md">
        <p class="text-sm font-medium text-muted">Pending approvals</p>
        <p class="mt-2 font-display text-3xl font-bold text-brand-orange">{{ number_format($stats['pending_members']) }}</p>
        <p class="mt-2 text-xs text-muted">Needs review</p>
    </a>
    <div class="stat-card">
        <p class="text-sm font-medium text-muted">Active members</p>
        <p class="mt-2 font-display text-3xl font-bold text-brand-green">{{ number_format($stats['active_members']) }}</p>
        <p class="mt-2 text-xs text-muted">{{ number_format($stats['approved_members']) }} approved</p>
    </div>
    <a href="{{ route('admin.reports.attendance') }}" class="stat-card transition hover:border-brand-green/30 hover:shadow-md">
        <p class="text-sm font-medium text-muted">Check-ins today</p>
        <p class="mt-2 font-display text-3xl font-bold text-brand-green">{{ number_format($stats['check_ins_today']) }}</p>
        <p class="mt-2 text-xs text-muted">{{ number_format($stats['check_ins_total']) }} all time</p>
    </a>
    <a href="{{ route('admin.events.index') }}" class="stat-card transition hover:border-brand-blue/30 hover:shadow-md">
        <p class="text-sm font-medium text-muted">Active events</p>
        <p class="mt-2 font-display text-3xl font-bold text-brand-blue">{{ number_format($stats['active_events']) }}</p>
        <p class="mt-2 text-xs text-muted">{{ number_format($stats['total_enrollments']) }} enrollments</p>
    </a>
    <div class="stat-card">
        <p class="text-sm font-medium text-muted">System users</p>
        <p class="mt-2 font-display text-3xl font-bold text-brand-blue">{{ number_format($stats['total_users']) }}</p>
        <p class="mt-2 text-xs text-muted">{{ number_format($stats['active_users']) }} active</p>
    </div>
</div>

<div class="mt-6 grid gap-6 xl:grid-cols-3">
    <div class="xl:col-span-2">
        @include('admin.reports._chart-card', [
            'title' => 'New registrations',
            'subtitle' => 'Last 14 days',
            'type' => 'line',
            'labels' => $charts['registration_trend']['labels'],
            'values' => $charts['registration_trend']['values'],
            'height' => 'h-64',
        ])
    </div>
    <div>
        @include('admin.reports._chart-card', [
            'title' => 'Registration mix',
            'subtitle' => 'Approved / pending / rejected',
            'type' => 'doughnut',
            'labels' => $charts['registration_status']['labels'],
            'values' => $charts['registration_status']['values'],
            'height' => 'h-64',
        ])
    </div>
</div>

<div class="mt-6 grid gap-6 xl:grid-cols-3">
    <div class="card xl:col-span-2">
        <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
            <div>
                <h2 class="font-display text-base font-bold text-ink">Spotlight events</h2>
                <p class="text-sm text-muted">Active upcoming and ongoing programmes</p>
            </div>
            <a href="{{ route('admin.events.index') }}" class="btn-outline">All events</a>
        </div>
        <div class="divide-y divide-slate-100">
            @forelse ($spotlightEvents as $event)
                <a href="{{ route('admin.events.show', $event) }}" class="flex flex-wrap items-start justify-between gap-3 px-5 py-4 transition hover:bg-slate-50/80">
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <p class="font-semibold text-ink">{{ $event->name }}</p>
                            <span class="{{ $event->isOngoing() ? 'badge-green' : ($event->isUpcoming() ? 'badge-blue' : 'badge-muted') }}">
                                {{ $event->timelineLabel() }}
                            </span>
                        </div>
                        <p class="mt-1 text-sm text-muted">{{ $event->scheduleLabel() }}</p>
                        <p class="mt-1 text-xs text-muted">{{ $event->methodLabel() }} · {{ $event->days_count }} day{{ $event->days_count === 1 ? '' : 's' }}</p>
                    </div>
                    <div class="flex gap-4 text-right">
                        <div>
                            <p class="font-display text-lg font-bold text-brand-blue">{{ number_format($event->active_enrollments_count) }}</p>
                            <p class="text-[11px] uppercase tracking-wide text-muted">Enrolled</p>
                        </div>
                        <div>
                            <p class="font-display text-lg font-bold text-brand-green">{{ number_format($event->attendances_count) }}</p>
                            <p class="text-[11px] uppercase tracking-wide text-muted">Check-ins</p>
                        </div>
                    </div>
                </a>
            @empty
                <div class="px-5 py-10 text-center text-muted">No active events to spotlight yet.</div>
            @endforelse
        </div>
    </div>

    <div class="card p-5">
        <h2 class="font-display text-base font-bold text-ink">Quick actions</h2>
        <div class="mt-5 space-y-2.5">
            <a href="{{ route('admin.waiting-approvals.index') }}" class="btn-accent w-full justify-start">
                Pending approvals
                @if ($stats['pending_members'] > 0)
                    <span class="ml-auto rounded-md bg-white/20 px-2 py-0.5 text-xs">{{ $stats['pending_members'] }}</span>
                @endif
            </a>
            @if (auth()->user()->canManageMembers())
                <a href="{{ route('admin.members.create') }}" class="btn-outline w-full justify-start">Add member</a>
            @endif
            @if (auth()->user()->canManageEvents())
                <a href="{{ route('admin.events.create') }}" class="btn-outline w-full justify-start">Add event</a>
            @endif
            @if (auth()->user()->canAccessAttendance())
                <a href="{{ route('admin.attendance.index') }}" class="btn-outline w-full justify-start">Attendance desk</a>
            @endif
            <a href="{{ route('admin.reports.index') }}" class="btn-outline w-full justify-start">Analytics reports</a>
            <a href="{{ route('admin.events.index') }}" class="btn-outline w-full justify-start">ASDA Events</a>
            @if (auth()->user()->canManageSettings())
                <a href="{{ route('admin.settings.edit') }}" class="btn-outline w-full justify-start">System settings</a>
            @endif
            @if (auth()->user()->canManageUsers())
                <a href="{{ route('admin.users.index') }}" class="btn-outline w-full justify-start">System users</a>
            @endif
        </div>
    </div>
</div>

<div class="mt-6 grid gap-6 xl:grid-cols-3">
    <div class="card">
        <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
            <div>
                <h2 class="font-display text-base font-bold text-ink">Recent members</h2>
                <p class="text-sm text-muted">Latest registrations</p>
            </div>
            <a href="{{ route('admin.members.index') }}" class="btn-outline">View all</a>
        </div>
        <div class="divide-y divide-slate-100">
            @forelse ($recentMembers as $member)
                <a href="{{ route('admin.members.show', $member) }}" class="block px-5 py-3.5 transition hover:bg-slate-50/80">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="truncate font-semibold text-ink">{{ $member->displayName() }}</p>
                            <p class="truncate text-xs text-muted">{{ $member->nic }}@if ($member->designation) · {{ $member->designation->name }}@endif</p>
                        </div>
                        <span class="{{ $member->registration_status === 'approved' ? 'badge-green' : ($member->registration_status === 'pending' ? 'badge-orange' : 'badge-muted') }}">
                            {{ ucfirst($member->registration_status) }}
                        </span>
                    </div>
                    <p class="mt-1 text-xs text-muted">{{ \App\Support\SriLankaDate::date($member->created_at) }}</p>
                </a>
            @empty
                <div class="px-5 py-10 text-center text-muted">No members yet.</div>
            @endforelse
        </div>
    </div>

    <div class="card">
        <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
            <div>
                <h2 class="font-display text-base font-bold text-ink">Recent check-ins</h2>
                <p class="text-sm text-muted">Latest desk activity</p>
            </div>
            <a href="{{ route('admin.reports.attendance') }}" class="btn-outline">Report</a>
        </div>
        <div class="divide-y divide-slate-100">
            @forelse ($recentCheckIns as $attendance)
                <div class="px-5 py-3.5">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="truncate font-semibold text-ink">{{ $attendance->member?->displayName() ?? 'Unknown member' }}</p>
                            <p class="truncate text-xs text-muted">
                                {{ $attendance->event?->name ?? 'Event' }}
                                @if ($attendance->venue)
                                    · {{ $attendance->venue->name }}
                                @endif
                            </p>
                        </div>
                        <span class="badge-green">In</span>
                    </div>
                    <p class="mt-1 text-xs text-muted">
                        {{ \App\Support\SriLankaDate::format($attendance->checked_in_at, 'd M, h:i A') }}
                        @if ($attendance->checkedInBy)
                            · {{ $attendance->checkedInBy->name }}
                        @endif
                    </p>
                </div>
            @empty
                <div class="px-5 py-10 text-center text-muted">No check-ins recorded yet.</div>
            @endforelse
        </div>
    </div>

    <div class="card">
        <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
            <div>
                <h2 class="font-display text-base font-bold text-ink">Recent activity</h2>
                <p class="text-sm text-muted">System & staff actions</p>
            </div>
            @if (auth()->user()->canManageUsers())
                <a href="{{ route('admin.activity-logs.index') }}" class="btn-outline">Logs</a>
            @endif
        </div>
        <div class="divide-y divide-slate-100">
            @forelse ($recentActivity as $log)
                <div class="px-5 py-3.5">
                    <div class="flex items-start justify-between gap-3">
                        <p class="min-w-0 text-sm font-medium text-ink">{{ $log->description }}</p>
                        <span class="{{ $log->badgeClass() }} shrink-0">{{ $log->actionLabel() }}</span>
                    </div>
                    <p class="mt-1 text-xs text-muted">
                        {{ $log->causer_name ?: 'System' }}
                        · {{ \App\Support\SriLankaDate::format($log->created_at, 'd M, h:i A') }}
                    </p>
                </div>
            @empty
                <div class="px-5 py-10 text-center text-muted">No activity logged yet.</div>
            @endforelse
        </div>
    </div>
</div>
@endsection
