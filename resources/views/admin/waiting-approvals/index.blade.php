@extends('layouts.dashboard')

@section('title', 'Waiting Approvals')
@section('page-title', 'Waiting Approvals')
@section('page-subtitle', 'Review and approve pending member registrations')

@section('page-actions')
<a href="{{ route('admin.members.index') }}" class="btn-outline">Members</a>
<a href="{{ route('admin.rejected-members.index') }}" class="btn-outline">Rejected</a>
@endsection

@section('content')
<div class="mb-4 rounded-xl border border-brand-orange/20 bg-brand-orange-soft px-4 py-3 text-sm text-brand-orange">
    {{ $pendingCount }} member{{ $pendingCount === 1 ? '' : 's' }} waiting for approval.
</div>

<div class="card">
    <form id="waiting-filter-form" method="GET" action="{{ route('admin.waiting-approvals.index') }}" class="border-b border-slate-100 p-4" data-auto-filter>
        <input type="search" name="search" value="{{ request('search') }}" class="form-input" placeholder="Search name, NIC, Unique ID, mobile" data-auto-filter-search>
    </form>

    @if (auth()->user()->canManageMembers() && $members->count())
        <form id="waiting-bulk-form" method="POST" action="{{ route('admin.waiting-approvals.bulk') }}" class="border-b border-slate-100 bg-surface/70 px-4 py-3" data-member-bulk-form>
            @csrf
            <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                <p class="text-sm text-muted">
                    <span class="bulk-selected-count font-semibold text-ink">0</span> selected
                </p>
                <div class="flex flex-col gap-2 sm:flex-row sm:flex-wrap sm:items-center">
                    <select name="action" required class="bulk-action form-select sm:w-56" disabled>
                        <option value="">Bulk action…</option>
                        <option value="approve">Approve</option>
                        <option value="reject">Reject</option>
                    </select>
                    <button type="submit" class="bulk-apply btn-primary" disabled>Apply</button>
                </div>
            </div>
        </form>
    @endif

    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    @if (auth()->user()->canManageMembers() && $members->count())
                        <th class="w-10">
                            <input type="checkbox" class="bulk-select-all h-4 w-4 rounded border-slate-300 text-brand-green focus:ring-brand-green" title="Select all on this page" form="waiting-bulk-form">
                        </th>
                    @endif
                    <th>Member</th>
                    <th>Unique ID</th>
                    <th>Designation</th>
                    <th>Submitted</th>
                    <th class="text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($members as $member)
                    <tr>
                        @if (auth()->user()->canManageMembers())
                            <td>
                                <input type="checkbox" name="member_ids[]" value="{{ $member->id }}" class="member-bulk-checkbox h-4 w-4 rounded border-slate-300 text-brand-green focus:ring-brand-green" form="waiting-bulk-form">
                            </td>
                        @endif
                        <td>
                            <div class="flex items-center gap-3">
                                @if ($member->profileImageUrl())
                                    <img src="{{ $member->profileImageUrl() }}" alt="" class="h-10 w-10 rounded-full object-cover">
                                @else
                                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-brand-orange-soft text-sm font-bold text-brand-orange">
                                        {{ strtoupper(substr($member->full_name, 0, 1)) }}
                                    </div>
                                @endif
                                <div>
                                    <p class="font-semibold text-ink">{{ $member->displayName() }}</p>
                                    <p class="text-xs text-muted">{{ $member->nic }} · {{ $member->mobile_1 }}</p>
                                    <div class="mt-1">
                                        <x-member-age :member="$member" compact />
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="font-medium text-brand-blue">{{ $member->unique_id ?: '—' }}</td>
                        <td class="text-muted">{{ $member->designation?->name ?: '—' }}</td>
                        <td class="text-muted">{{ \App\Support\SriLankaDate::datetime($member->created_at) }}</td>
                        <td>
                            <div class="flex flex-wrap items-center justify-end gap-2">
                                <a href="{{ route('admin.members.show', $member) }}" class="btn-ghost px-2.5 py-1.5">View</a>
                                @if (auth()->user()->canManageMembers())
                                    <form method="POST" action="{{ route('admin.members.approve', $member) }}" data-confirm="Approve {{ $member->displayName() }}? This will activate their membership access.">
                                        @csrf
                                        <button type="submit" class="btn-primary px-3 py-1.5">Approve</button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.members.reject', $member) }}" class="flex items-center gap-1" data-confirm="Reject {{ $member->displayName() }}? They will remain blocked from membership access.">
                                        @csrf
                                        <input type="hidden" name="rejection_reason" value="Rejected from waiting approvals queue.">
                                        <button type="submit" class="btn-accent px-3 py-1.5">Reject</button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ auth()->user()->canManageMembers() ? 6 : 5 }}" class="py-14 text-center text-muted">
                            No members are waiting for approval.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($members->hasPages())
        <div class="border-t border-slate-100 px-4 py-3">{{ $members->links() }}</div>
    @endif
</div>
@endsection
