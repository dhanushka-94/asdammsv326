@extends('layouts.dashboard')

@section('title', $member->displayName())
@section('page-title', 'Member profile')
@section('page-subtitle', $member->unique_id ?: 'Checked-in member details')

@section('page-actions')
<a href="{{ route('admin.checked-in.index') }}" class="btn-outline">Back to list</a>
@if (! auth()->user()->isReception())
    <a href="{{ route('admin.members.show', $member) }}" class="btn-secondary">Admin member page</a>
@endif
@endsection

@section('content')
@php
    $defaultTab = request('tab', 'details');
    if (! in_array($defaultTab, ['details', 'events', 'checkins'], true)) {
        $defaultTab = 'details';
    }
@endphp

<div class="mx-auto max-w-5xl">
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
                    <div class="mt-2 flex flex-wrap gap-2">
                        @if ($member->category)
                            <span class="rounded-full bg-white/15 px-3 py-1 text-xs font-semibold">{{ $member->category->name }}</span>
                        @endif
                        <span class="rounded-full bg-white/15 px-3 py-1 text-xs font-semibold">{{ $attendances->count() }} check-in{{ $attendances->count() === 1 ? '' : 's' }}</span>
                        <span class="rounded-full bg-white/15 px-3 py-1 text-xs font-semibold">{{ $enrollments->count() }} enrollment{{ $enrollments->count() === 1 ? '' : 's' }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex flex-wrap gap-1 border-b border-slate-100 bg-surface/60 px-2 pt-2 sm:px-3" role="tablist" aria-label="Member sections">
            <button type="button" class="member-tab-btn" data-tab-btn="details" role="tab" aria-selected="true">Details</button>
            <button type="button" class="member-tab-btn" data-tab-btn="events" role="tab" aria-selected="false">
                Enrolled events
                <span class="ml-1.5 rounded-full bg-brand-green-soft px-1.5 py-0.5 text-[10px] font-bold text-brand-green">{{ $enrollments->count() }}</span>
            </button>
            <button type="button" class="member-tab-btn" data-tab-btn="checkins" role="tab" aria-selected="false">
                Check-ins
                <span class="ml-1.5 rounded-full bg-brand-blue-soft px-1.5 py-0.5 text-[10px] font-bold text-brand-blue">{{ $attendances->count() }}</span>
            </button>
        </div>

        <div data-tab-panel="details" role="tabpanel">
            <div class="grid gap-4 p-5 sm:grid-cols-2 sm:p-8">
                @if ($qrUrl && $member->unique_id)
                    <div class="rounded-xl border border-slate-200 bg-white p-4 sm:col-span-2">
                        <div class="flex flex-col items-center gap-4 sm:flex-row sm:justify-between">
                            <div>
                                <p class="text-xs font-semibold uppercase text-muted">Membership QR</p>
                                <p class="mt-1 font-display text-xl font-bold text-brand-blue">{{ $member->unique_id }}</p>
                                <p class="mt-1 text-sm text-muted">Scan or confirm Unique ID at the desk</p>
                            </div>
                            <img src="{{ $qrUrl }}" alt="QR {{ $member->unique_id }}" class="h-40 w-40 rounded-xl border border-slate-100 p-2">
                        </div>
                    </div>
                @endif

                <div class="rounded-xl bg-surface p-4">
                    <p class="text-xs font-semibold uppercase text-muted">Unique ID</p>
                    <p class="mt-1 font-semibold text-brand-blue">{{ $member->unique_id ?: '—' }}</p>
                </div>
                <div class="rounded-xl bg-surface p-4">
                    <p class="text-xs font-semibold uppercase text-muted">Statuses</p>
                    <p class="mt-2 space-x-1">
                        <span class="{{ $member->registration_status === 'approved' ? 'badge-green' : ($member->registration_status === 'rejected' ? 'badge-orange' : 'badge-blue') }}">{{ ucfirst($member->registration_status) }}</span>
                        <span class="{{ $member->status === 'active' ? 'badge-green' : 'badge-muted' }}">{{ ucfirst($member->status) }}</span>
                    </p>
                </div>
                <div class="rounded-xl bg-surface p-4">
                    <p class="text-xs font-semibold uppercase text-muted">Designation</p>
                    <p class="mt-1 font-semibold">{{ $member->designation?->name ?: '—' }}</p>
                </div>
                <div class="rounded-xl bg-surface p-4">
                    <p class="text-xs font-semibold uppercase text-muted">Category</p>
                    <p class="mt-1 font-semibold">{{ $member->category?->name ?: '—' }}</p>
                </div>
                <x-member-age :member="$member" />
                <div class="rounded-xl bg-surface p-4">
                    <p class="text-xs font-semibold uppercase text-muted">NIC</p>
                    <p class="mt-1 font-semibold">{{ $member->nic }}</p>
                </div>
                <div class="rounded-xl bg-surface p-4">
                    <p class="text-xs font-semibold uppercase text-muted">Mobile 1</p>
                    <p class="mt-1 font-semibold">{{ $member->mobile_1 }}</p>
                </div>
                <div class="rounded-xl bg-surface p-4">
                    <p class="text-xs font-semibold uppercase text-muted">Mobile 2</p>
                    <p class="mt-1 font-semibold">{{ $member->mobile_2 ?: '—' }}</p>
                </div>
                <div class="rounded-xl bg-surface p-4">
                    <p class="text-xs font-semibold uppercase text-muted">WhatsApp</p>
                    <p class="mt-1 font-semibold">{{ $member->whatsapp ?: '—' }}</p>
                </div>
                <div class="rounded-xl bg-surface p-4">
                    <p class="text-xs font-semibold uppercase text-muted">Office telephone</p>
                    <p class="mt-1 font-semibold">{{ $member->office_telephone ?: '—' }}</p>
                </div>
                <div class="rounded-xl bg-surface p-4 sm:col-span-2">
                    <p class="text-xs font-semibold uppercase text-muted">Email</p>
                    <p class="mt-1 font-semibold">{{ $member->email ?: '—' }}</p>
                </div>
                <div class="rounded-xl bg-surface p-4">
                    <p class="text-xs font-semibold uppercase text-muted">Institute</p>
                    <p class="mt-1 font-semibold">{{ $member->institute ?: '—' }}</p>
                </div>
                <div class="rounded-xl bg-surface p-4">
                    <p class="text-xs font-semibold uppercase text-muted">Sub-institute</p>
                    <p class="mt-1 font-semibold">{{ $member->sub_institute ?: '—' }}</p>
                </div>
                <div class="rounded-xl bg-surface p-4">
                    <p class="text-xs font-semibold uppercase text-muted">Section</p>
                    <p class="mt-1 font-semibold">{{ $member->section ?: '—' }}</p>
                </div>
                <div class="rounded-xl bg-surface p-4 sm:col-span-2">
                    <p class="text-xs font-semibold uppercase text-muted">Address</p>
                    <p class="mt-1 font-semibold">{{ $member->address ?: '—' }}</p>
                </div>
            </div>
        </div>

        <div data-tab-panel="events" class="hidden space-y-4 p-5 sm:p-8" role="tabpanel">
            <div>
                <p class="font-semibold text-ink">Active enrollments</p>
                <p class="text-sm text-muted">Events this member is currently enrolled in</p>
            </div>

            @forelse ($enrollments as $enrollment)
                @php($event = $enrollment->event)
                <div class="rounded-xl border border-slate-200 bg-white p-4">
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                        <div>
                            <p class="font-semibold text-ink">{{ $event?->name ?: 'Event' }}</p>
                            <p class="mt-1 text-sm text-muted">{{ $event?->scheduleLabel() ?: '—' }}</p>
                            <p class="mt-1 text-xs text-muted">
                                Enrolled {{ $enrollment->enrolled_at ? \App\Support\SriLankaDate::datetime($enrollment->enrolled_at) : '—' }}
                                @if ($enrollment->participation_mode)
                                    · Mode: {{ ucfirst($enrollment->participation_mode) }}
                                @endif
                                @if ($event)
                                    · {{ $event->timelineLabel() }}
                                @endif
                            </p>
                        </div>
                        <span class="badge-green">Active</span>
                    </div>
                </div>
            @empty
                <div class="rounded-xl border border-dashed border-slate-200 px-4 py-10 text-center text-muted">No active enrollments.</div>
            @endforelse

            @if ($removedEnrollments->isNotEmpty())
                <div class="pt-2">
                    <p class="font-semibold text-ink">Removed from events</p>
                    <p class="text-sm text-muted">Previous enrollments that were kicked</p>
                </div>
                @foreach ($removedEnrollments as $enrollment)
                    @php($event = $enrollment->event)
                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                        <p class="font-semibold text-ink">{{ $event?->name ?: 'Event' }}</p>
                        <p class="mt-1 text-xs text-muted">
                            Removed {{ $enrollment->kicked_at ? \App\Support\SriLankaDate::datetime($enrollment->kicked_at) : '—' }}
                            @if ($enrollment->kick_reason)
                                · {{ $enrollment->kick_reason }}
                            @endif
                        </p>
                    </div>
                @endforeach
            @endif
        </div>

        <div data-tab-panel="checkins" class="hidden space-y-4 p-5 sm:p-8" role="tabpanel">
            <div>
                <p class="font-semibold text-ink">Check-in history</p>
                <p class="text-sm text-muted">All attendance desk check-ins for this member</p>
            </div>

            @forelse ($attendances as $attendance)
                <div class="rounded-xl border border-slate-200 bg-white p-4">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                        <div class="min-w-0">
                            <p class="font-semibold text-ink">{{ $attendance->event?->name ?: 'Event' }}</p>
                            <p class="mt-1 text-sm text-muted">
                                Day {{ $attendance->day?->day_number ?? '—' }}
                                @if ($attendance->venue)
                                    · {{ $attendance->venue->name }}
                                @endif
                            </p>
                            <p class="mt-1 text-xs text-muted">
                                By {{ $attendance->checkedInBy?->name ?: 'Unknown officer' }}
                                @if ($attendance->enrollment?->participation_mode)
                                    · Mode: {{ ucfirst($attendance->enrollment->participation_mode) }}
                                @endif
                            </p>
                            @if ($attendance->checkInItems->isNotEmpty())
                                <div class="mt-3 flex flex-wrap gap-1.5">
                                    @foreach ($attendance->checkInItems as $item)
                                        <span class="badge-blue">{{ $item->name }}</span>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                        <div class="shrink-0 text-left sm:text-right">
                            <span class="badge-green">Checked in</span>
                            <p class="mt-2 text-sm font-medium text-ink">{{ \App\Support\SriLankaDate::format($attendance->checked_in_at, 'd M Y') }}</p>
                            <p class="text-xs text-muted">{{ \App\Support\SriLankaDate::format($attendance->checked_in_at, 'h:i A') }}</p>
                        </div>
                    </div>
                </div>
            @empty
                <div class="rounded-xl border border-dashed border-slate-200 px-4 py-10 text-center text-muted">No check-ins recorded.</div>
            @endforelse
        </div>
    </div>
</div>
@endsection
