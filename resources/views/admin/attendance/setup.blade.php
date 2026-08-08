@extends('layouts.dashboard')

@section('title', 'Set up attendance — '.$event->name)
@section('page-title', 'Set up attendance desk')
@section('page-subtitle', $event->name)

@section('page-actions')
@if (auth()->user()->hasDeskPin())
<form method="POST" action="{{ route('admin.attendance.lock.store') }}" class="inline">
    @csrf
    <input type="hidden" name="return" value="{{ url()->current() }}">
    <button type="submit" class="btn-secondary shrink-0 whitespace-nowrap">Lock desk</button>
</form>
@endif
<a href="{{ route('admin.attendance.index') }}" class="btn-outline shrink-0 whitespace-nowrap">All events</a>
@endsection

@section('content')
<div class="mx-auto w-full max-w-xl space-y-4 sm:space-y-5">
    <section class="card space-y-5 p-4 sm:p-6">
        <div>
            <p class="text-xs font-semibold uppercase tracking-wide text-muted">Step 1 of 2</p>
            <h2 class="mt-1 font-display text-xl font-bold text-ink">Choose day and venue</h2>
            <p class="mt-2 text-sm text-muted">
                Set your defaults for this reception session, then start attending. These apply to every check-in until you change them.
            </p>
        </div>

        <form method="POST" action="{{ route('admin.attendance.start', $event) }}" class="space-y-4">
            @csrf

            <div>
                <label for="day" class="form-label">Event day <span class="text-brand-orange">*</span></label>
                <select id="day" name="day" class="form-select" required>
                    @foreach ($event->days as $eventDay)
                        <option value="{{ $eventDay->id }}" @selected((int) old('day', $selectedDayId) === $eventDay->id)>
                            {{ $eventDay->dayLabel() }}
                        </option>
                    @endforeach
                </select>
                @error('day')
                    <p class="mt-1 text-xs font-semibold text-red-600">{{ $message }}</p>
                @enderror
            </div>

            @if ($event->venues->isNotEmpty())
                <div>
                    <label for="venue" class="form-label">Check-in venue <span class="text-brand-orange">*</span></label>
                    <select id="venue" name="venue" class="form-select" required>
                        @foreach ($event->venues as $eventVenue)
                            <option value="{{ $eventVenue->id }}" @selected((int) old('venue', $selectedVenueId) === $eventVenue->id)>
                                {{ $eventVenue->locationSummary() }}
                            </option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-xs text-muted">This venue will be set automatically when you scan or search a member.</p>
                    @error('venue')
                        <p class="mt-1 text-xs font-semibold text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            @else
                <div class="rounded-xl border border-dashed border-slate-200 bg-surface/60 px-4 py-3 text-sm text-muted">
                    This event has no venues — venue will not be required when checking in.
                </div>
            @endif

            <div class="flex flex-col gap-2 pt-2 sm:flex-row sm:items-center">
                <button type="submit" class="btn-primary w-full justify-center py-3 sm:flex-1">
                    Start attending
                </button>
                <a href="{{ route('admin.attendance.index') }}" class="btn-outline w-full justify-center sm:w-auto">Cancel</a>
            </div>
        </form>
    </section>

    <p class="text-center text-xs text-muted">
        You can change day or venue later from the attendance desk without leaving the event.
    </p>
</div>
@endsection
