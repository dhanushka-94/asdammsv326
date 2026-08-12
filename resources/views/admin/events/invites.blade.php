@extends('layouts.dashboard')

@section('title', 'Invite members')
@section('page-title', 'Invite members')
@section('page-subtitle', $event->name)

@section('page-actions')
<a href="{{ route('admin.events.show', $event) }}" class="btn-outline">Back to event</a>
@endsection

@section('content')
<div class="mx-auto max-w-6xl space-y-5">
    <section class="card p-5 sm:p-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h2 class="font-display text-lg font-bold text-ink">Invite / remove invite</h2>
                <p class="mt-1 text-sm text-muted">
                    Click <span class="font-medium text-ink">Invite</span> or <span class="font-medium text-ink">Remove invite</span> on a row,
                    or use checkboxes for bulk actions. Only invited active approved members can see and register for this event.
                </p>
            </div>
            <div class="rounded-xl border border-brand-orange/20 bg-brand-orange-soft px-4 py-3 text-center">
                <p class="text-[11px] font-semibold uppercase tracking-wide text-brand-orange">Currently invited</p>
                <p class="mt-1 font-display text-2xl font-bold text-ink">{{ $invitedCount }}</p>
            </div>
        </div>
    </section>

    <form method="GET" action="{{ route('admin.events.invites.edit', $event) }}" class="card grid gap-3 p-4 sm:grid-cols-2 lg:grid-cols-5">
        <div class="lg:col-span-2">
            <label for="search" class="form-label">Search</label>
            <input id="search" type="search" name="search" value="{{ request('search') }}" class="form-input" placeholder="Name, NIC, Unique ID, institute">
        </div>
        <div>
            <label for="designation_id" class="form-label">Designation</label>
            <select id="designation_id" name="designation_id" class="form-select">
                <option value="">All</option>
                @foreach ($designations as $designation)
                    <option value="{{ $designation->id }}" @selected((string) request('designation_id') === (string) $designation->id)>{{ $designation->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="category_id" class="form-label">Category</label>
            <select id="category_id" name="category_id" class="form-select">
                <option value="">All</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" @selected((string) request('category_id') === (string) $category->id)>{{ $category->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex items-end gap-2">
            <label class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm font-medium text-ink">
                <input type="checkbox" name="invited_only" value="1" class="rounded border-slate-300 text-brand-green focus:ring-brand-green" @checked(request()->boolean('invited_only'))>
                Invited only
            </label>
            <button type="submit" class="btn-secondary">Filter</button>
        </div>
    </form>

    <div class="card overflow-hidden">
        <form method="POST" action="{{ route('admin.events.invites.update', $event) }}" id="event-invites-form">
            @csrf
            @method('PUT')
            @foreach (request()->only(['search', 'designation_id', 'category_id', 'invited_only', 'page']) as $key => $value)
                @if (filled($value))
                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                @endif
            @endforeach

            <div class="flex flex-col gap-3 border-b border-slate-100 px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
                <p class="text-sm text-muted">Bulk: select members, then invite or remove invite.</p>
                <div class="flex flex-wrap gap-2">
                    <button type="submit" name="action" value="invite" class="btn-accent">Invite selected</button>
                    <button type="submit" name="action" value="remove" class="btn-outline" data-confirm="Remove invite from selected members? They will lose event access.">Remove invite selected</button>
                </div>
            </div>
        </form>

        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th class="w-10">
                            <input type="checkbox" class="invite-select-all h-4 w-4 rounded border-slate-300 text-brand-green focus:ring-brand-green" title="Select all on this page" form="event-invites-form">
                        </th>
                        <th>Member</th>
                        <th>Unique ID</th>
                        <th>Designation</th>
                        <th>Institute</th>
                        <th>Status</th>
                        <th class="text-right">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($members as $member)
                        @php $isInvited = in_array($member->id, $invitedIds, true); @endphp
                        <tr>
                            <td>
                                <input
                                    type="checkbox"
                                    name="member_ids[]"
                                    value="{{ $member->id }}"
                                    form="event-invites-form"
                                    class="invite-member-checkbox h-4 w-4 rounded border-slate-300 text-brand-green focus:ring-brand-green"
                                >
                            </td>
                            <td>
                                <p class="font-semibold text-ink">{{ $member->displayName() }}</p>
                                <p class="text-xs text-muted">{{ $member->nic }}</p>
                            </td>
                            <td class="font-medium text-brand-blue">{{ $member->unique_id ?: '—' }}</td>
                            <td>{{ $member->designation?->name ?: '—' }}</td>
                            <td>{{ $member->institute ?: '—' }}</td>
                            <td>
                                @if ($isInvited)
                                    <span class="badge-orange">Invited</span>
                                @else
                                    <span class="badge-muted">Not invited</span>
                                @endif
                            </td>
                            <td class="text-right">
                                @if ($isInvited)
                                    <form method="POST" action="{{ route('admin.events.invites.remove', [$event, $member]) }}" data-confirm="Remove invite for {{ $member->displayName() }}?">
                                        @csrf
                                        @method('DELETE')
                                        @foreach (request()->only(['search', 'designation_id', 'category_id', 'invited_only', 'page']) as $key => $value)
                                            @if (filled($value))
                                                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                                            @endif
                                        @endforeach
                                        <button type="submit" class="btn-outline px-2.5 py-1.5 text-xs">Remove invite</button>
                                    </form>
                                @else
                                    <form method="POST" action="{{ route('admin.events.invites.invite', [$event, $member]) }}">
                                        @csrf
                                        @foreach (request()->only(['search', 'designation_id', 'category_id', 'invited_only', 'page']) as $key => $value)
                                            @if (filled($value))
                                                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                                            @endif
                                        @endforeach
                                        <button type="submit" class="btn-accent px-2.5 py-1.5 text-xs">Invite</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-10 text-center text-sm text-muted">No active approved members match this filter.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($members->hasPages())
            <div class="border-t border-slate-100 px-4 py-3">
                {{ $members->links() }}
            </div>
        @endif
    </div>
</div>

<script>
(() => {
    const form = document.getElementById('event-invites-form');
    if (!form) return;
    const selectAll = document.querySelector('.invite-select-all');
    const boxes = () => Array.from(document.querySelectorAll('.invite-member-checkbox'));
    selectAll?.addEventListener('change', () => {
        boxes().forEach((box) => { box.checked = selectAll.checked; });
    });
})();
</script>
@endsection
