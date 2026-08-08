@extends('layouts.dashboard')

@section('title', $event->name)
@section('page-title', 'Event details')
@section('page-subtitle', $event->name)

@section('page-actions')
@if (auth()->user()->canManageEvents())
    <a href="{{ route('admin.events.edit', $event) }}" class="btn-secondary">Edit</a>
@endif
<a href="{{ route('admin.events.index') }}" class="btn-outline hidden sm:inline-flex">Back to ASDA Events</a>
@endsection

@section('content')
@php
    $theme = $event->hasEnded()
        ? 'slate'
        : ($event->isOngoing() ? 'green' : ($event->isUpcoming() ? 'blue' : 'orange'));
    $hero = match ($theme) {
        'green' => 'from-brand-green via-brand-green-dark to-brand-blue',
        'blue' => 'from-brand-blue via-brand-blue-dark to-brand-green',
        'orange' => 'from-brand-orange via-brand-orange-dark to-brand-blue',
        default => 'from-slate-600 via-slate-700 to-slate-800',
    };
    $activeCount = $event->active_enrollments_count;
@endphp

<div class="mx-auto max-w-5xl space-y-5">
    <section class="overflow-hidden rounded-3xl bg-gradient-to-br {{ $hero }} text-white shadow-lg">
        <div class="p-6 sm:p-8">
            <div class="flex flex-wrap gap-2">
                <span class="rounded-full bg-white/20 px-3 py-1 text-xs font-bold uppercase tracking-wide">{{ $event->timelineLabel() }}</span>
                <span class="rounded-full bg-white/15 px-3 py-1 text-xs font-semibold">{{ $event->statusLabel() }}</span>
                @if ($event->isBothMethods())
                    <span class="rounded-full bg-white/15 px-3 py-1 text-xs font-semibold">Physical</span>
                    <span class="rounded-full bg-white/15 px-3 py-1 text-xs font-semibold">Online</span>
                @elseif ($event->isOnline())
                    <span class="rounded-full bg-white/15 px-3 py-1 text-xs font-semibold">Online</span>
                @else
                    <span class="rounded-full bg-white/15 px-3 py-1 text-xs font-semibold">Physical</span>
                @endif
                <span class="rounded-full bg-white/15 px-3 py-1 text-xs font-semibold">
                    {{ $event->isOpenForEnrollment() ? 'Enrollment open' : 'Enrollment closed' }}
                </span>
            </div>

            <h1 class="mt-4 font-display text-3xl font-bold tracking-tight sm:text-4xl">{{ $event->name }}</h1>
            <p class="mt-3 text-base text-white/90">{{ $event->scheduleLabel() }}</p>

            @if ($event->description)
                <p class="mt-4 max-w-3xl whitespace-pre-line text-sm leading-relaxed text-white/85">{{ $event->description }}</p>
            @endif
        </div>
    </section>

    <section class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-2xl border border-brand-green/20 bg-brand-green-soft p-4">
            <p class="text-[11px] font-semibold uppercase tracking-wide text-brand-green">Method</p>
            <p class="mt-1 font-semibold text-ink">{{ $event->methodLabel() }}</p>
        </div>
        <div class="rounded-2xl border border-brand-blue/20 bg-brand-blue-soft p-4">
            <p class="text-[11px] font-semibold uppercase tracking-wide text-brand-blue">Duration</p>
            <p class="mt-1 font-semibold text-ink">
                {{ $durationDays ? $durationDays.' day'.($durationDays === 1 ? '' : 's') : '—' }}
            </p>
            <p class="mt-1 text-xs text-muted">
                {{ $event->days->sum(fn ($day) => $day->sessions->count()) }} session{{ $event->days->sum(fn ($day) => $day->sessions->count()) === 1 ? '' : 's' }}
                across {{ $event->days->count() }} day{{ $event->days->count() === 1 ? '' : 's' }}
            </p>
        </div>
        <div class="rounded-2xl border border-brand-orange/20 bg-brand-orange-soft p-4">
            <p class="text-[11px] font-semibold uppercase tracking-wide text-brand-orange">Venues</p>
            <p class="mt-1 font-semibold text-ink">{{ $event->venues->count() }}</p>
            <p class="mt-1 text-xs text-muted">
                @if ($event->isOnline() && $event->venues->isEmpty())
                    Online event
                @else
                    Location{{ $event->venues->count() === 1 ? '' : 's' }} listed
                @endif
            </p>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-4">
            <p class="text-[11px] font-semibold uppercase tracking-wide text-muted">Enrolled</p>
            <p class="mt-1 font-display text-2xl font-bold text-ink">{{ $activeCount }}</p>
            <p class="mt-1 text-xs text-muted">
                {{ $kickedEnrollments->count() }} removed / kicked
            </p>
        </div>
    </section>

    <section class="grid gap-4 sm:grid-cols-2">
        <div class="card p-5">
            <p class="text-xs font-semibold uppercase tracking-wide text-muted">Start</p>
            <p class="mt-2 font-semibold text-ink">
                {{ \App\Support\SriLankaDate::date($event->start_date) }}
                · {{ \App\Support\SriLankaDate::format($event->start_date->format('Y-m-d').' '.substr((string) $event->start_time, 0, 5), \App\Support\SriLankaDate::TIME) }}
            </p>
        </div>
        <div class="card p-5">
            <p class="text-xs font-semibold uppercase tracking-wide text-muted">End</p>
            <p class="mt-2 font-semibold text-ink">
                {{ \App\Support\SriLankaDate::date($event->end_date) }}
                · {{ \App\Support\SriLankaDate::format($event->end_date->format('Y-m-d').' '.substr((string) $event->end_time, 0, 5), \App\Support\SriLankaDate::TIME) }}
            </p>
        </div>
    </section>

    <section class="card p-5 sm:p-8">
        <h2 class="font-display text-base font-bold text-ink">Event invitations</h2>
        <p class="mt-1 text-sm text-muted">Digital invitation letter and card templates for active members.</p>
        <div class="mt-4 grid gap-4 sm:grid-cols-2">
            <div class="rounded-xl border border-slate-200 bg-surface/50 p-4">
                <p class="text-xs font-semibold uppercase tracking-wide text-muted">Invitation letter</p>
                @if ($event->hasInvitationLetter())
                    <p class="mt-2 text-sm font-semibold text-brand-green">Uploaded</p>
                    <a href="{{ asset('storage/'.$event->invitation_letter_path) }}" target="_blank" rel="noopener" class="mt-2 inline-flex text-sm font-semibold text-brand-blue hover:underline">
                        View template PDF
                    </a>
                    <p class="mt-1 text-xs text-muted">Auto-fills member name and address.</p>
                @else
                    <p class="mt-2 text-sm text-muted">Not uploaded yet.</p>
                @endif
            </div>
            <div class="rounded-xl border border-slate-200 bg-surface/50 p-4">
                <p class="text-xs font-semibold uppercase tracking-wide text-muted">Invitation card</p>
                @if ($event->hasInvitationCard())
                    <p class="mt-2 text-sm font-semibold text-brand-green">Uploaded</p>
                    <a href="{{ asset('storage/'.$event->invitation_card_path) }}" target="_blank" rel="noopener" class="mt-2 inline-flex text-sm font-semibold text-brand-blue hover:underline">
                        View template PDF
                    </a>
                    <p class="mt-1 text-xs text-muted">Auto-fills member name.</p>
                @else
                    <p class="mt-2 text-sm text-muted">Not uploaded yet.</p>
                @endif
            </div>
        </div>
    </section>

    <section class="card p-5 sm:p-8">
        <div class="flex flex-wrap items-end justify-between gap-3">
            <div>
                <h2 class="font-display text-base font-bold text-ink">Event days &amp; sessions</h2>
                <p class="mt-1 text-sm text-muted">Each day can include multiple sessions.</p>
            </div>
            <span class="badge-green">{{ $event->days->count() }} day{{ $event->days->count() === 1 ? '' : 's' }}</span>
        </div>

        @if ($event->days->isNotEmpty())
            <div class="mt-4 space-y-4">
                @foreach ($event->days as $day)
                    <div class="rounded-2xl border border-brand-green/20 bg-gradient-to-br from-brand-green-soft to-white p-4">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="badge-green">{{ $day->dayLabel() }}</span>
                            <span class="text-xs font-semibold text-muted">{{ $day->sessions->count() }} session{{ $day->sessions->count() === 1 ? '' : 's' }}</span>
                        </div>
                        @if ($day->description)
                            <p class="mt-2 whitespace-pre-line text-sm text-muted">{{ $day->description }}</p>
                        @endif

                        @if ($day->sessions->isNotEmpty())
                            <div class="mt-3 space-y-2">
                                @foreach ($day->sessions as $session)
                                    <div class="rounded-xl border border-white/80 bg-white/90 p-3">
                                        <p class="font-semibold text-ink">{{ $session->name }}</p>
                                        @if ($session->description)
                                            <p class="mt-1 whitespace-pre-line text-sm text-muted">{{ $session->description }}</p>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        @if ($day->questions->isNotEmpty())
                            <div class="mt-3 space-y-2 rounded-xl border border-brand-blue/20 bg-white/90 p-3">
                                <p class="text-xs font-semibold uppercase tracking-wide text-brand-blue">Questionnaire</p>
                                @foreach ($day->questions as $question)
                                    <div>
                                        <p class="text-sm font-semibold text-ink">{{ $question->question }}</p>
                                        <p class="mt-1 text-xs text-muted">
                                            {{ $question->options->pluck('label')->implode(' · ') }}
                                        </p>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        @else
            <p class="mt-4 rounded-xl border border-dashed border-slate-200 bg-surface/60 px-4 py-6 text-center text-sm text-muted">No event days added yet.</p>
        @endif
    </section>

    <section class="card p-5 sm:p-8">
        <div class="flex flex-wrap items-end justify-between gap-3">
            <div>
                <h2 class="font-display text-base font-bold text-ink">Venues</h2>
                <p class="mt-1 text-sm text-muted">Physical locations and maps for this event.</p>
            </div>
            <span class="badge-blue">{{ $event->venues->count() }} venue{{ $event->venues->count() === 1 ? '' : 's' }}</span>
        </div>

        @if ($event->venues->isNotEmpty())
            <div class="mt-4 space-y-4">
                @foreach ($event->venues as $index => $venue)
                    <div class="rounded-2xl border border-slate-200 bg-gradient-to-br from-brand-blue-soft/60 to-white p-4 sm:p-5">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wide text-muted">Venue {{ $index + 1 }}</p>
                                <p class="mt-1 font-display text-lg font-bold text-ink">{{ $venue->name }}</p>
                            </div>
                            @if ($venue->hasMapPin())
                                <span class="badge-green">Map pin set</span>
                            @elseif ($venue->maps_url)
                                <span class="badge-blue">Maps link</span>
                            @else
                                <span class="badge-muted">No map</span>
                            @endif
                        </div>

                        <div class="mt-3 grid gap-3 sm:grid-cols-3">
                            <div class="rounded-xl bg-white/80 p-3 ring-1 ring-slate-100">
                                <p class="text-[11px] font-semibold uppercase tracking-wide text-muted">Floor</p>
                                <p class="mt-1 text-sm font-semibold text-ink">{{ $venue->floor ?: '—' }}</p>
                            </div>
                            <div class="rounded-xl bg-white/80 p-3 ring-1 ring-slate-100">
                                <p class="text-[11px] font-semibold uppercase tracking-wide text-muted">Hall / Room</p>
                                <p class="mt-1 text-sm font-semibold text-ink">{{ $venue->hall_room ?: '—' }}</p>
                            </div>
                            <div class="rounded-xl bg-white/80 p-3 ring-1 ring-slate-100">
                                <p class="text-[11px] font-semibold uppercase tracking-wide text-muted">Coordinates</p>
                                <p class="mt-1 text-sm font-semibold text-ink">
                                    @if ($venue->hasMapPin())
                                        {{ $venue->latitude }}, {{ $venue->longitude }}
                                    @else
                                        —
                                    @endif
                                </p>
                            </div>
                        </div>

                        @if ($venue->description)
                            <p class="mt-3 whitespace-pre-line text-sm text-muted">{{ $venue->description }}</p>
                        @endif

                        @if ($venue->embedMapsUrl())
                            <div class="mt-3 overflow-hidden rounded-xl border border-slate-200">
                                <iframe title="Map {{ $venue->name }}" src="{{ $venue->embedMapsUrl() }}" class="h-52 w-full" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                            </div>
                        @endif

                        @if ($venue->directionsUrl() || $venue->googleMapsLink())
                            <div class="mt-3 flex flex-wrap gap-2">
                                @if ($venue->directionsUrl())
                                    <a href="{{ $venue->directionsUrl() }}" target="_blank" rel="noopener" class="inline-flex items-center justify-center gap-2 rounded-xl bg-brand-orange px-4 py-2.5 text-sm font-bold text-white shadow-lg shadow-brand-orange/20 transition hover:bg-brand-orange-dark">
                                        Get Direction
                                    </a>
                                @endif
                                @if ($venue->googleMapsLink())
                                    <a href="{{ $venue->googleMapsLink() }}" target="_blank" rel="noopener" class="inline-flex items-center justify-center rounded-xl border border-brand-blue/30 bg-white px-4 py-2.5 text-sm font-semibold text-brand-blue transition hover:bg-brand-blue-soft">
                                        Open map
                                    </a>
                                @endif
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        @elseif ($event->isOnline())
            <p class="mt-4 rounded-xl border border-dashed border-slate-200 bg-surface/60 px-4 py-6 text-center text-sm text-muted">This is an online event. Venue details are not required.</p>
        @else
            <p class="mt-4 rounded-xl border border-dashed border-slate-200 bg-surface/60 px-4 py-6 text-center text-sm text-muted">No venues added yet.</p>
        @endif
    </section>

    <section id="enrollments" class="card overflow-hidden">
        <div class="border-b border-slate-100 px-5 py-4 sm:px-6">
            <h2 class="font-display text-base font-bold text-ink">Active enrollments</h2>
            <p class="mt-1 text-sm text-muted">
                {{ $activeCount }} member{{ $activeCount === 1 ? '' : 's' }} enrolled total
                @if ($enrollmentFiltersActive)
                    · showing {{ $activeEnrollments->total() }} match{{ $activeEnrollments->total() === 1 ? '' : 'es' }}
                @endif
            </p>
        </div>

        <form
            method="GET"
            action="{{ route('admin.events.show', $event) }}#enrollments"
            class="grid gap-3 border-b border-slate-100 p-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6"
            data-auto-filter
        >
            <div class="sm:col-span-2 xl:col-span-2">
                <input
                    type="search"
                    name="enrollment_search"
                    value="{{ request('enrollment_search') }}"
                    class="form-input"
                    placeholder="Search name, NIC, ID, mobile, institute…"
                    data-auto-filter-search
                >
            </div>
            <div>
                <select name="enrollment_designation_id" class="form-select" data-auto-filter-change>
                    <option value="">All designations</option>
                    @foreach ($designations as $designation)
                        <option value="{{ $designation->id }}" @selected((string) request('enrollment_designation_id') === (string) $designation->id)>
                            {{ $designation->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <select name="enrollment_category_id" class="form-select" data-auto-filter-change>
                    <option value="">All categories</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" @selected((string) request('enrollment_category_id') === (string) $category->id)>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <select name="enrollment_institute" class="form-select" data-auto-filter-change>
                    <option value="">All institutes</option>
                    @foreach ($institutes as $institute)
                        <option value="{{ $institute }}" @selected(request('enrollment_institute') === $institute)>
                            {{ $institute }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="flex gap-2 sm:col-span-2 xl:col-span-1">
                <input type="date" name="enrollment_date_from" value="{{ request('enrollment_date_from') }}" class="form-input" title="Enrolled from" data-auto-filter-change>
                <input type="date" name="enrollment_date_to" value="{{ request('enrollment_date_to') }}" class="form-input" title="Enrolled to" data-auto-filter-change>
            </div>
            @if ($enrollmentFiltersActive)
                <div class="sm:col-span-2 xl:col-span-6">
                    <a href="{{ route('admin.events.show', $event) }}#enrollments" class="btn-outline">Clear filters</a>
                </div>
            @endif
        </form>

        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Member</th>
                        <th>Designation</th>
                        <th>Category</th>
                        <th>Institute</th>
                        <th>Participation</th>
                        <th>Answers</th>
                        <th>Enrolled at</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($activeEnrollments as $enrollment)
                        @php $member = $enrollment->member; @endphp
                        <tr>
                            <td>
                                @if ($member)
                                    <a href="{{ route('admin.members.show', $member) }}" class="font-semibold text-brand-blue hover:underline">
                                        {{ $member->displayName() }}
                                    </a>
                                    <p class="text-xs text-muted">{{ $member->nic }} · {{ $member->unique_id ?: 'No unique ID' }}</p>
                                    @if ($member->mobile_1)
                                        <p class="text-xs text-muted">{{ $member->mobile_1 }}</p>
                                    @endif
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="text-muted">{{ $member?->designation?->name ?: '—' }}</td>
                            <td class="text-muted">{{ $member?->category?->name ?: '—' }}</td>
                            <td class="text-muted">
                                {{ $member?->institute ?: '—' }}
                                @if ($member?->sub_institute)
                                    <span class="block text-xs">{{ $member->sub_institute }}</span>
                                @endif
                                @if ($member?->section)
                                    <span class="block text-xs">{{ $member->section }}</span>
                                @endif
                            </td>
                            <td class="text-muted">{{ $enrollment->participationModeLabel() }}</td>
                            <td class="text-muted">
                                @if ($enrollment->answers->isEmpty())
                                    —
                                @else
                                    <ul class="max-w-xs space-y-1 text-xs">
                                        @foreach ($enrollment->answers as $answer)
                                            <li>
                                                <span class="font-semibold text-ink">{{ \Illuminate\Support\Str::limit($answer->question?->question ?: 'Q', 40) }}:</span>
                                                {{ $answer->option?->label ?: '—' }}
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif
                            </td>
                            <td class="text-muted">{{ \App\Support\SriLankaDate::datetime($enrollment->enrolled_at) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-10 text-center text-muted">
                                @if ($enrollmentFiltersActive)
                                    No enrollments match these filters.
                                @else
                                    No active enrollments yet.
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($activeEnrollments->hasPages())
            <div class="border-t border-slate-100 px-4 py-3">
                {{ $activeEnrollments->fragment('enrollments')->links() }}
            </div>
        @endif
    </section>

    @if ($kickedEnrollments->isNotEmpty())
        <section class="card overflow-hidden">
            <div class="border-b border-slate-100 px-5 py-4 sm:px-6">
                <h2 class="font-display text-base font-bold text-ink">Removed enrollments</h2>
                <p class="mt-1 text-sm text-muted">{{ $kickedEnrollments->count() }} member{{ $kickedEnrollments->count() === 1 ? '' : 's' }} kicked out of this event</p>
            </div>
            <div class="table-wrap">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Member</th>
                            <th>Reason</th>
                            <th>Removed by</th>
                            <th>Removed at</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($kickedEnrollments as $enrollment)
                            <tr>
                                <td>
                                    @if ($enrollment->member)
                                        <a href="{{ route('admin.members.show', $enrollment->member) }}" class="font-semibold text-brand-blue hover:underline">
                                            {{ $enrollment->member->displayName() }}
                                        </a>
                                        <p class="text-xs text-muted">{{ $enrollment->member->nic }}</p>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="text-muted">{{ $enrollment->kick_reason ?: '—' }}</td>
                                <td class="text-muted">{{ $enrollment->kickedBy?->name ?: '—' }}</td>
                                <td class="text-muted">{{ \App\Support\SriLankaDate::datetime($enrollment->kicked_at) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    @endif

    @if (auth()->user()->canManageEvents())
        <div class="card border-red-200 p-5 sm:p-8">
            <h3 class="font-display text-base font-bold text-red-700">Danger zone</h3>
            <p class="mt-2 text-sm text-muted">
                Deleting this event permanently removes its days, venues, and
                <span class="font-semibold text-ink">{{ $activeCount }}</span>
                enrollment{{ $activeCount === 1 ? '' : 's' }}.
                This cannot be undone.
            </p>

            <form
                method="POST"
                action="{{ route('admin.events.destroy', $event) }}"
                class="mt-5 space-y-4"
                data-math-confirm="Delete event “{{ $event->name }}”? Solve the math question to confirm permanent deletion."
            >
                @csrf
                @method('DELETE')

                @if ($errors->has('confirm_name'))
                    <div class="alert-error"><span>{{ $errors->first('confirm_name') }}</span></div>
                @endif

                <div>
                    <label for="confirm_name" class="form-label">
                        Type the event name to confirm:
                        <span class="font-semibold text-ink">{{ $event->name }}</span>
                    </label>
                    <input
                        id="confirm_name"
                        type="text"
                        name="confirm_name"
                        value="{{ old('confirm_name') }}"
                        required
                        autocomplete="off"
                        class="form-input"
                        placeholder="Type exact event name"
                    >
                </div>

                <button type="submit" class="btn-danger">Delete event permanently</button>
            </form>
        </div>
    @endif
</div>
@endsection
