@extends('layouts.dashboard')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')
@section('page-subtitle', 'Members and system access overview')

@section('content')
<div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
    <div class="stat-card">
        <p class="text-sm font-medium text-muted">Total Members</p>
        <p class="mt-2 font-display text-3xl font-bold text-brand-blue">{{ $stats['total_members'] }}</p>
    </div>
    <div class="stat-card">
        <p class="text-sm font-medium text-muted">Pending Approvals</p>
        <p class="mt-2 font-display text-3xl font-bold text-brand-orange">{{ $stats['pending_members'] }}</p>
    </div>
    <div class="stat-card">
        <p class="text-sm font-medium text-muted">Active Members</p>
        <p class="mt-2 font-display text-3xl font-bold text-brand-green">{{ $stats['active_members'] }}</p>
    </div>
    <div class="stat-card">
        <p class="text-sm font-medium text-muted">Approved Members</p>
        <p class="mt-2 font-display text-3xl font-bold text-brand-blue">{{ $stats['approved_members'] }}</p>
    </div>
    <div class="stat-card">
        <p class="text-sm font-medium text-muted">System Users</p>
        <p class="mt-2 font-display text-3xl font-bold text-brand-blue">{{ $stats['total_users'] }}</p>
    </div>
    <div class="stat-card">
        <p class="text-sm font-medium text-muted">Active System Users</p>
        <p class="mt-2 font-display text-3xl font-bold text-brand-green">{{ $stats['active_users'] }}</p>
    </div>
</div>

<div class="mt-6 grid gap-6 xl:grid-cols-3">
    <div class="card xl:col-span-2">
        <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
            <div>
                <h2 class="font-display text-base font-bold text-ink">Recent members</h2>
                <p class="text-sm text-muted">Latest registration activity</p>
            </div>
            <a href="{{ route('admin.members.index') }}" class="btn-outline">View all</a>
        </div>
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Member</th>
                        <th>Registration</th>
                        <th>Status</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($recentMembers as $member)
                        <tr>
                            <td>
                                <p class="font-semibold text-ink">{{ $member->displayName() }}</p>
                                <p class="text-xs text-muted">{{ $member->nic }}</p>
                                <div class="mt-1">
                                    <x-member-age :member="$member" compact />
                                </div>
                            </td>
                            <td><span class="badge-blue">{{ ucfirst($member->registration_status) }}</span></td>
                            <td><span class="{{ $member->status === 'active' ? 'badge-green' : 'badge-muted' }}">{{ ucfirst($member->status) }}</span></td>
                            <td class="text-muted">{{ \App\Support\SriLankaDate::date($member->created_at) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="py-10 text-center text-muted">No members yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card p-5">
        <h2 class="font-display text-base font-bold text-ink">Quick actions</h2>
        <div class="mt-5 space-y-3">
            <a href="{{ route('admin.waiting-approvals.index') }}" class="btn-accent w-full justify-start">Pending approvals</a>
            @if (auth()->user()->canManageMembers())
                <a href="{{ route('admin.members.create') }}" class="btn-outline w-full justify-start">Add member</a>
            @endif
            @if (auth()->user()->canManageEvents())
                <a href="{{ route('admin.events.create') }}" class="btn-outline w-full justify-start">Add event</a>
            @endif
            <a href="{{ route('admin.events.index') }}" class="btn-outline w-full justify-start">ASDA Events</a>
            <a href="{{ route('admin.settings.edit') }}" class="btn-outline w-full justify-start">System Settings</a>
            @if (auth()->user()->canManageUsers())
                <a href="{{ route('admin.users.index') }}" class="btn-outline w-full justify-start">System users</a>
            @endif
        </div>
    </div>
</div>
@endsection
