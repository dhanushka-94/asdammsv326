@extends('layouts.dashboard')

@section('title', $member->displayName())
@section('page-title', 'Member details')
@section('page-subtitle', $member->unique_id ?: 'Unique ID pending')

@section('page-actions')
@if (auth()->user()->canManageMembers())
    <a href="{{ route('admin.members.edit', $member) }}" class="btn-secondary">Edit</a>
@endif
<a href="{{ route('admin.members.index') }}" class="btn-outline hidden sm:inline-flex">Back</a>
@endsection

@section('content')
@php
    $defaultTab = request('tab', request()->hasAny(['activity_page', 'activity_search', 'activity_action', 'activity_date_from', 'activity_date_to']) ? 'activity' : 'details');
    if (! in_array($defaultTab, ['details', 'events', 'activity', 'password', 'delete'], true)) {
        $defaultTab = 'details';
    }
    if (in_array($defaultTab, ['password', 'delete'], true) && ! auth()->user()->canManageMembers()) {
        $defaultTab = 'details';
    }
@endphp
<div class="mx-auto max-w-4xl">
    <div class="card overflow-hidden" data-member-tabs data-default-tab="{{ $defaultTab }}">
        <div class="bg-gradient-to-r from-brand-blue via-brand-green to-brand-orange px-5 py-6 sm:px-8 sm:py-8">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center">
                @if ($member->profileImageUrl())
                    <img src="{{ $member->profileImageUrl() }}" alt="" class="h-16 w-16 rounded-2xl border-2 border-white/40 object-cover">
                @else
                    <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-white/15 text-2xl font-bold text-white">
                        {{ strtoupper(substr($member->full_name, 0, 1)) }}
                    </div>
                @endif
                <div class="text-white">
                    <h2 class="font-display text-2xl font-bold">{{ $member->displayName() }}</h2>
                    <p class="text-white/80">{{ $member->designation?->name }} · {{ $member->nic }}</p>
                    @if ($member->category)
                        <p class="mt-2 inline-flex rounded-full bg-white/15 px-3 py-1 text-xs font-semibold text-white">
                            {{ $member->category->name }}
                        </p>
                    @endif
                    @if ($member->isOverSixtyOne())
                        <p class="mt-2 inline-flex rounded-full bg-brand-orange px-3 py-1 text-xs font-bold text-white">
                            Age {{ $member->age() }} · Over 61 years
                        </p>
                    @endif
                </div>
            </div>
        </div>

        <div class="flex flex-wrap gap-1 border-b border-slate-100 bg-surface/60 px-2 pt-2 sm:px-3" role="tablist" aria-label="Member detail sections">
            <button type="button" class="member-tab-btn" data-tab-btn="details" role="tab" aria-selected="true">
                Details
            </button>
            <button type="button" class="member-tab-btn" data-tab-btn="events" role="tab" aria-selected="false">
                Enrolled events
                <span class="ml-1.5 rounded-full bg-brand-green-soft px-1.5 py-0.5 text-[10px] font-bold text-brand-green">{{ $member->activeEventEnrollments->count() }}</span>
            </button>
            <button type="button" class="member-tab-btn" data-tab-btn="activity" role="tab" aria-selected="false">
                Activity log
            </button>
            @if (auth()->user()->canManageMembers())
                <button type="button" class="member-tab-btn" data-tab-btn="password" role="tab" aria-selected="false">
                    Password
                </button>
                <button type="button" class="member-tab-btn" data-tab-btn="delete" role="tab" aria-selected="false">
                    Delete
                </button>
            @endif
        </div>

        <div data-tab-panel="details" role="tabpanel">
            <div class="grid gap-4 p-5 sm:grid-cols-2 sm:p-8">
                @if ($member->unique_id)
                    <div class="rounded-xl border border-slate-200 bg-white p-4 sm:col-span-2">
                        <div class="flex flex-col items-center gap-4 sm:flex-row sm:justify-between">
                            <div>
                                <p class="text-xs font-semibold uppercase text-muted">Membership QR</p>
                                <p class="mt-1 font-display text-xl font-bold text-brand-blue">{{ $member->unique_id }}</p>
                                <p class="mt-1 text-sm text-muted">High quality QR generated from Unique ID</p>
                                <div class="mt-4 flex flex-col gap-2 sm:flex-row sm:flex-wrap">
                                    <a href="{{ route('admin.members.qr', $member) }}" class="btn-accent">
                                        Download high quality QR
                                    </a>
                                    @if (auth()->user()->canManageMembers())
                                        <form method="POST" action="{{ route('admin.members.qr.regenerate', $member) }}" data-confirm="Regenerate QR code for {{ $member->displayName() }}? The Unique ID stays the same; only the image file is recreated.">
                                            @csrf
                                            <button type="submit" class="btn-secondary">Regenerate QR</button>
                                        </form>
                                    @endif
                                </div>
                                <p class="mt-3 text-xs text-muted">
                                    Member downloads:
                                    @if ($member->hasDownloadedQr())
                                        {{ $member->qr_download_count }} (last {{ \App\Support\SriLankaDate::datetime($member->qr_last_downloaded_at) }})
                                    @else
                                        not downloaded yet
                                    @endif
                                </p>
                            </div>
                            @if ($qrUrl)
                                <img src="{{ $qrUrl }}?v={{ now()->timestamp }}" alt="QR {{ $member->unique_id }}" class="h-44 w-44 rounded-xl border border-slate-100 bg-white p-2">
                            @endif
                        </div>
                    </div>
                @endif
                <div class="rounded-xl bg-surface p-4"><p class="text-xs font-semibold uppercase text-muted">Unique ID</p><p class="mt-1 font-semibold text-brand-blue">{{ $member->unique_id ?: '—' }}</p></div>
                <div class="rounded-xl bg-surface p-4">
                    <p class="text-xs font-semibold uppercase text-muted">Statuses</p>
                    <p class="mt-2 space-x-1">
                        <span class="{{ $member->registration_status === 'approved' ? 'badge-green' : ($member->registration_status === 'rejected' ? 'badge-orange' : 'badge-blue') }}">{{ ucfirst($member->registration_status) }}</span>
                        <span class="{{ $member->status === 'active' ? 'badge-green' : 'badge-muted' }}">{{ ucfirst($member->status) }}</span>
                    </p>
                </div>
                <div class="rounded-xl bg-surface p-4"><p class="text-xs font-semibold uppercase text-muted">Last login</p><p class="mt-1 font-semibold">{{ $member->last_login_at ? \App\Support\SriLankaDate::datetime($member->last_login_at) : 'Never' }}</p></div>
                <div class="rounded-xl bg-surface p-4">
                    <p class="text-xs font-semibold uppercase text-muted">QR downloads</p>
                    @if ($member->hasDownloadedQr())
                        <p class="mt-1 font-semibold text-brand-green">Downloaded · {{ $member->qr_download_count }} time{{ $member->qr_download_count === 1 ? '' : 's' }}</p>
                        <p class="mt-1 text-xs text-muted">Last: {{ \App\Support\SriLankaDate::datetime($member->qr_last_downloaded_at) }}</p>
                    @else
                        <p class="mt-1 font-semibold text-muted">Not downloaded</p>
                    @endif
                </div>
                <div class="rounded-xl bg-surface p-4"><p class="text-xs font-semibold uppercase text-muted">Designation</p><p class="mt-1 font-semibold">{{ $member->designation?->name ?: '—' }}</p></div>
                <div class="rounded-xl bg-surface p-4"><p class="text-xs font-semibold uppercase text-muted">Category</p><p class="mt-1 font-semibold">{{ $member->category?->name ?: '—' }}</p></div>
                <x-member-age :member="$member" />
                <div class="rounded-xl bg-surface p-4"><p class="text-xs font-semibold uppercase text-muted">NIC</p><p class="mt-1 font-semibold">{{ $member->nic }}</p></div>
                <div class="rounded-xl bg-surface p-4"><p class="text-xs font-semibold uppercase text-muted">Mobile 1</p><p class="mt-1 font-semibold">{{ $member->mobile_1 }}</p></div>
                <div class="rounded-xl bg-surface p-4"><p class="text-xs font-semibold uppercase text-muted">Mobile 2</p><p class="mt-1 font-semibold">{{ $member->mobile_2 ?: '—' }}</p></div>
                <div class="rounded-xl bg-surface p-4"><p class="text-xs font-semibold uppercase text-muted">WhatsApp</p><p class="mt-1 font-semibold">{{ $member->whatsapp ?: '—' }}</p></div>
                <div class="rounded-xl bg-surface p-4"><p class="text-xs font-semibold uppercase text-muted">Office telephone</p><p class="mt-1 font-semibold">{{ $member->office_telephone ?: '—' }}</p></div>
                <div class="rounded-xl bg-surface p-4 sm:col-span-2"><p class="text-xs font-semibold uppercase text-muted">Email</p><p class="mt-1 font-semibold">{{ $member->email ?: '—' }}</p></div>
                <div class="rounded-xl bg-surface p-4"><p class="text-xs font-semibold uppercase text-muted">Institute</p><p class="mt-1 font-semibold">{{ $member->institute ?: '—' }}</p></div>
                <div class="rounded-xl bg-surface p-4"><p class="text-xs font-semibold uppercase text-muted">Sub-institute</p><p class="mt-1 font-semibold">{{ $member->sub_institute ?: '—' }}</p></div>
                <div class="rounded-xl bg-surface p-4"><p class="text-xs font-semibold uppercase text-muted">Section</p><p class="mt-1 font-semibold">{{ $member->section ?: '—' }}</p></div>
                <div class="rounded-xl bg-surface p-4 sm:col-span-2"><p class="text-xs font-semibold uppercase text-muted">Address</p><p class="mt-1 font-semibold">{{ $member->address ?: '—' }}</p></div>
                @if ($member->rejection_reason)
                    <div class="rounded-xl bg-brand-orange-soft p-4 sm:col-span-2"><p class="text-xs font-semibold uppercase text-brand-orange">Rejection reason</p><p class="mt-1 text-brand-orange">{{ $member->rejection_reason }}</p></div>
                @endif
            </div>

            @if (auth()->user()->canManageMembers() && $member->registration_status === 'pending')
                <div class="flex flex-col gap-4 border-t border-slate-100 p-5 sm:flex-row sm:items-end sm:justify-between sm:p-8 sm:pt-5">
                    <form method="POST" action="{{ route('admin.members.approve', $member) }}" data-confirm="Approve {{ $member->displayName() }}? This will activate their membership access.">
                        @csrf
                        <button type="submit" class="btn-primary">Approve registration</button>
                    </form>
                    <form method="POST" action="{{ route('admin.members.reject', $member) }}" class="flex w-full flex-col gap-2 sm:max-w-md" data-confirm="Reject {{ $member->displayName() }}? They will remain blocked from membership access.">
                        @csrf
                        <label for="rejection_reason" class="form-label">Rejection reason (optional)</label>
                        <div class="flex gap-2">
                            <input id="rejection_reason" type="text" name="rejection_reason" class="form-input" placeholder="Reason">
                            <button type="submit" class="btn-accent shrink-0">Reject</button>
                        </div>
                    </form>
                </div>
            @endif

            @if (auth()->user()->canManageMembers() && $member->registration_status === 'rejected')
                <div class="flex flex-col gap-3 border-t border-slate-100 p-5 sm:flex-row sm:items-center sm:justify-between sm:p-8 sm:pt-5">
                    <div>
                        <p class="font-semibold text-ink">Re-accept registration</p>
                        <p class="text-sm text-muted">
                            This member was rejected
                            @if ($member->rejection_reason)
                                ({{ $member->rejection_reason }})
                            @endif
                            . Re-accept to restore active membership access.
                        </p>
                    </div>
                    <form method="POST" action="{{ route('admin.members.re-accept', $member) }}" data-confirm="Re-accept {{ $member->displayName() }}? They will become an active approved member.">
                        @csrf
                        <button type="submit" class="btn-primary">Re-accept</button>
                    </form>
                </div>
            @endif
        </div>

        <div data-tab-panel="events" class="hidden space-y-4 p-5 sm:p-8" role="tabpanel">
            <div>
                <p class="font-semibold text-ink">Enrolled events</p>
                <p class="text-sm text-muted">Active enrollments with schedule. Admins can remove a member from an event with a reason.</p>
            </div>

            @forelse ($member->activeEventEnrollments as $enrollment)
                @php($event = $enrollment->event)
                <div class="rounded-xl border border-slate-200 bg-white p-4">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                        <div class="min-w-0">
                            <p class="font-semibold text-ink">{{ $event?->name ?: 'Event' }}</p>
                            <p class="mt-1 text-sm text-muted">{{ $event?->scheduleLabel() ?: '—' }}</p>
                            <p class="mt-1 text-xs text-muted">
                                Enrolled {{ $enrollment->enrolled_at ? \App\Support\SriLankaDate::datetime($enrollment->enrolled_at) : '—' }}
                                @if ($event)
                                    · {{ $event->timelineLabel() }}
                                @endif
                            </p>
                        </div>
                        @if (auth()->user()->canManageMembers() && $event)
                            <form
                                method="POST"
                                action="{{ route('admin.members.events.kick', [$member, $event]) }}"
                                class="w-full space-y-2 sm:max-w-sm"
                                data-confirm="Remove {{ $member->displayName() }} from {{ $event->name }}?"
                            >
                                @csrf
                                <label for="kick_reason_{{ $enrollment->id }}" class="form-label">Reason for removal</label>
                                <textarea
                                    id="kick_reason_{{ $enrollment->id }}"
                                    name="kick_reason"
                                    rows="2"
                                    required
                                    maxlength="1000"
                                    class="form-input"
                                    placeholder="Enter reason…"
                                >{{ old('kick_reason') }}</textarea>
                                <button type="submit" class="btn-danger">Kick out from event</button>
                            </form>
                        @endif
                    </div>
                </div>
            @empty
                <p class="rounded-xl bg-surface px-4 py-6 text-center text-sm text-muted">Not enrolled in any events.</p>
            @endforelse

            @if ($member->eventEnrollments->isNotEmpty())
                <div class="border-t border-slate-100 pt-4">
                    <p class="mb-3 text-xs font-semibold uppercase tracking-wide text-muted">Removed from events</p>
                    <div class="space-y-2">
                        @foreach ($member->eventEnrollments as $removed)
                            <div class="rounded-lg bg-surface px-3 py-2 text-sm">
                                <p class="font-medium text-ink">{{ $removed->event?->name ?: 'Event' }}</p>
                                <p class="text-xs text-muted">
                                    {{ $removed->event?->scheduleLabel() }}
                                    · Removed {{ $removed->kicked_at ? \App\Support\SriLankaDate::datetime($removed->kicked_at) : '—' }}
                                    @if ($removed->kickedBy)
                                        by {{ $removed->kickedBy->name }}
                                    @endif
                                </p>
                                @if ($removed->kick_reason)
                                    <p class="mt-1 text-xs text-brand-orange">Reason: {{ $removed->kick_reason }}</p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        <div data-tab-panel="activity" class="hidden" role="tabpanel">
            <div class="border-b border-slate-100 px-5 py-4 sm:px-8">
                <p class="font-semibold text-ink">Member activity log</p>
                <p class="text-sm text-muted">Admin-only history of actions by or about this member.</p>
            </div>

            <form
                method="GET"
                action="{{ route('admin.members.show', $member) }}"
                class="grid gap-3 border-b border-slate-100 p-4 sm:grid-cols-2 lg:grid-cols-5"
                data-auto-filter
            >
                <input type="hidden" name="tab" value="activity">
                <div class="sm:col-span-2 lg:col-span-2">
                    <input
                        type="search"
                        name="activity_search"
                        value="{{ request('activity_search') }}"
                        class="form-input"
                        placeholder="Search description, actor, IP…"
                        data-auto-filter-search
                    >
                </div>
                <div>
                    <select name="activity_action" class="form-select" data-auto-filter-change>
                        <option value="">All actions</option>
                        @foreach ($activityActions as $action)
                            <option value="{{ $action }}" @selected(request('activity_action') === $action)>
                                {{ ucfirst(str_replace('_', ' ', $action)) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <input type="date" name="activity_date_from" value="{{ request('activity_date_from') }}" class="form-input" title="From date" data-auto-filter-change>
                </div>
                <div class="flex gap-2">
                    <input type="date" name="activity_date_to" value="{{ request('activity_date_to') }}" class="form-input" title="To date" data-auto-filter-change>
                    @if (request()->hasAny(['activity_search', 'activity_action', 'activity_date_from', 'activity_date_to']))
                        <a href="{{ route('admin.members.show', ['member' => $member, 'tab' => 'activity']) }}" class="btn-outline shrink-0">Clear</a>
                    @endif
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
                                    <p class="font-semibold text-ink">{{ $log->causer_name ?: 'Guest / system' }}</p>
                                    <p class="text-xs text-muted">
                                        {{ $log->guardLabel() }}
                                        @if ($log->causer_role)
                                            · {{ $log->causer_role }}
                                        @endif
                                    </p>
                                </td>
                                <td>
                                    <span class="{{ $log->badgeClass() }}">{{ $log->actionLabel() }}</span>
                                </td>
                                <td class="text-sm text-ink">{{ $log->description }}</td>
                                <td class="text-xs text-muted">{{ $log->ip_address ?: '—' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-10 text-center text-muted">No activity recorded for this member yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($activityLogs->hasPages())
                <div class="border-t border-slate-100 px-4 py-3">{{ $activityLogs->appends(request()->except('activity_page') + ['tab' => 'activity'])->links() }}</div>
            @endif
        </div>

        @if (auth()->user()->canManageMembers())
            <div data-tab-panel="password" class="hidden space-y-4 p-5 sm:p-8" role="tabpanel">
                <div>
                    <p class="font-semibold text-ink">Password management</p>
                    <p class="text-sm text-muted">Reset access or force the member to set a new password on next login.</p>
                </div>
                <div class="flex flex-col gap-3 sm:flex-row sm:flex-wrap">
                    <form method="POST" action="{{ route('admin.members.reset-password', $member) }}" data-confirm="Reset {{ $member->displayName() }} password to default ({{ $member->defaultPassword() }}) and require a new password on next login?">
                        @csrf
                        <button type="submit" class="btn-accent">Reset password to default</button>
                    </form>
                    <form method="POST" action="{{ route('admin.members.require-password-change', $member) }}" data-confirm="Require {{ $member->displayName() }} to set a new password on next login?">
                        @csrf
                        <button type="submit" class="btn-secondary">Require password change on next login</button>
                    </form>
                </div>
                <p class="text-xs text-muted">
                    Default password format: first 4 digits of NIC + @ASDA (example for this member: <span class="font-semibold text-ink">{{ $member->defaultPassword() }}</span>).
                    After reset, they sign in with that password, then must choose a new one (min. 8 characters).
                </p>
            </div>

            <div data-tab-panel="delete" class="hidden p-5 sm:p-8" role="tabpanel">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="font-semibold text-ink">Delete member</p>
                        <p class="text-sm text-muted">Permanently remove this member record.</p>
                    </div>
                    <form method="POST" action="{{ route('admin.members.destroy', $member) }}" data-math-confirm="Delete {{ $member->displayName() }}? Solve the math question to confirm.">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn-danger">Delete</button>
                    </form>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
