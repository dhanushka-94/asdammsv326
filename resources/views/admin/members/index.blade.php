@extends('layouts.dashboard')

@section('title', 'Members')
@section('page-title', 'Members')
@section('page-subtitle', 'Approved members')

@section('page-actions')
@if (auth()->user()->canBulkManageMembers())
<a href="{{ route('admin.members.import') }}" class="btn-outline">
    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
    <span class="hidden sm:inline">Import CSV</span>
</a>
@endif
@if (auth()->user()->canManageMembers())
<a href="{{ route('admin.members.create') }}" class="btn-accent">
    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
    <span class="hidden sm:inline">Add member</span>
</a>
@endif
@endsection

@section('content')
@if ($pendingCount > 0)
    <div class="mb-4 rounded-xl border border-brand-orange/20 bg-brand-orange-soft px-4 py-3 text-sm text-brand-orange">
        {{ $pendingCount }} registration{{ $pendingCount > 1 ? 's' : '' }} waiting for approval.
        <a href="{{ route('admin.waiting-approvals.index') }}" class="font-semibold underline">Review now</a>
    </div>
@endif
@if ($rejectedCount > 0)
    <div class="mb-4 rounded-xl border border-slate-200 bg-surface px-4 py-3 text-sm text-muted">
        {{ $rejectedCount }} rejected member{{ $rejectedCount > 1 ? 's' : '' }}.
        <a href="{{ route('admin.rejected-members.index') }}" class="font-semibold text-brand-blue underline">Open rejected list</a>
    </div>
@endif

<div class="card">
    <form id="members-filter-form" method="GET" action="{{ route('admin.members.index') }}" class="grid gap-3 border-b border-slate-100 p-4 sm:grid-cols-2 lg:grid-cols-5" data-auto-filter>
        <div class="sm:col-span-2">
            <input type="search" name="search" value="{{ request('search') }}" class="form-input" placeholder="Search name, NIC, Unique ID, email, mobile" data-auto-filter-search>
        </div>
        <div>
            <select name="status" class="form-select" data-auto-filter-change>
                <option value="">All status</option>
                <option value="active" @selected(request('status') === 'active')>Active</option>
                <option value="inactive" @selected(request('status') === 'inactive')>Inactive</option>
            </select>
        </div>
        <div>
            <select name="member_category_id" class="form-select" data-auto-filter-change>
                <option value="">All categories</option>
                <option value="none" @selected(request('member_category_id') === 'none')>No category</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" @selected((string) request('member_category_id') === (string) $category->id)>{{ $category->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <select name="over_61" class="form-select" data-auto-filter-change>
                <option value="">All ages</option>
                <option value="1" @selected(request('over_61') === '1')>Over 61 years</option>
            </select>
        </div>
    </form>

    @if (auth()->user()->canBulkManageMembers() && $members->count())
        <form id="members-bulk-form" method="POST" action="{{ route('admin.members.bulk') }}" class="border-b border-slate-100 bg-surface/70 px-4 py-3" data-member-bulk-form>
            @csrf
            <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                <p class="text-sm text-muted">
                    <span class="bulk-selected-count font-semibold text-ink">0</span> selected
                </p>
                <div class="flex flex-col gap-2 sm:flex-row sm:flex-wrap sm:items-center">
                    <select name="action" required class="bulk-action form-select sm:w-56" disabled>
                        <option value="">Bulk action…</option>
                        <option value="activate">Set active</option>
                        <option value="deactivate">Set inactive</option>
                        <option value="require_password_change">Require password change</option>
                        <option value="reset_password">Reset password to default</option>
                        <option value="delete">Delete</option>
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
                    @if (auth()->user()->canBulkManageMembers() && $members->count())
                        <th class="w-10">
                            <input type="checkbox" class="bulk-select-all h-4 w-4 rounded border-slate-300 text-brand-green focus:ring-brand-green" title="Select all on this page" form="members-bulk-form">
                        </th>
                    @endif
                    <th>Member</th>
                    <th>Unique ID</th>
                    <th>Designation</th>
                    <th>Category</th>
                    <th>Enrolled events</th>
                    <th>Status</th>
                    <th class="text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($members as $member)
                    <tr>
                        @if (auth()->user()->canBulkManageMembers())
                            <td>
                                <input type="checkbox" name="member_ids[]" value="{{ $member->id }}" class="member-bulk-checkbox h-4 w-4 rounded border-slate-300 text-brand-green focus:ring-brand-green" form="members-bulk-form">
                            </td>
                        @endif
                        <td>
                            <div class="flex items-center gap-3">
                                @if ($member->profileImageUrl())
                                    <img src="{{ $member->profileImageUrl() }}" alt="" class="h-10 w-10 rounded-full object-cover">
                                @else
                                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-brand-green-soft text-sm font-bold text-brand-green">
                                        {{ strtoupper(substr($member->full_name, 0, 1)) }}
                                    </div>
                                @endif
                                <div>
                                    <p class="font-semibold text-ink">{{ $member->displayName() }}</p>
                                    <p class="text-xs text-muted">{{ $member->nic }}</p>
                                    <div class="mt-1">
                                        <x-member-age :member="$member" compact />
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="font-medium text-brand-blue">{{ $member->unique_id ?: '—' }}</td>
                        <td class="text-muted">{{ $member->designation?->name ?: '—' }}</td>
                        <td class="text-muted">{{ $member->category?->name ?: '—' }}</td>
                        <td class="text-xs text-muted">
                            @if ($member->active_event_enrollments_count > 0)
                                <span class="font-semibold text-ink">{{ $member->active_event_enrollments_count }} enrolled</span>
                                <ul class="mt-1 space-y-0.5">
                                    @foreach ($member->activeEventEnrollments as $enrollment)
                                        <li class="leading-snug">{{ $enrollment->event?->name ?: 'Event' }}</li>
                                    @endforeach
                                </ul>
                            @else
                                —
                            @endif
                        </td>
                        <td>
                            <span class="{{ $member->status === 'active' ? 'badge-green' : 'badge-muted' }}">{{ ucfirst($member->status) }}</span>
                        </td>
                        <td>
                            <div class="flex justify-end gap-1">
                                <a href="{{ route('admin.members.show', $member) }}" class="btn-ghost px-2.5 py-1.5">View</a>
                                @if (auth()->user()->canManageMembers())
                                    <a href="{{ route('admin.members.edit', $member) }}" class="btn-ghost px-2.5 py-1.5 text-brand-blue">Edit</a>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ auth()->user()->canBulkManageMembers() ? 8 : 7 }}" class="py-14 text-center text-muted">No approved members found.</td>
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
