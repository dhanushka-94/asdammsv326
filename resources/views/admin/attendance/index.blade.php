@extends('layouts.dashboard')

@section('title', 'Event Attendance')
@section('page-title', 'Event Attendance')
@section('page-subtitle', 'Reception desk — select an event to check in members')

@section('content')
<div class="mx-auto max-w-5xl space-y-5">
    @if ($events->isEmpty())
        <div class="card px-6 py-16 text-center">
            <p class="font-display text-lg font-bold text-ink">No events available</p>
            <p class="mt-2 text-sm text-muted">
                @if (auth()->user()->isReception())
                    Ask a Super Admin to assign events to your reception account.
                @else
                    Create an event first, then open the attendance desk.
                @endif
            </p>
        </div>
    @else
        <div class="grid gap-4 sm:grid-cols-2">
            @foreach ($events as $event)
                <article class="card flex flex-col p-5">
                    <div class="flex flex-wrap gap-2">
                        <span class="badge-{{ $event->status === 'active' ? 'green' : 'muted' }}">{{ $event->statusLabel() }}</span>
                        <span class="badge-blue">{{ $event->timelineLabel() }}</span>
                    </div>
                    <h2 class="mt-3 font-display text-xl font-bold text-ink">{{ $event->name }}</h2>
                    <p class="mt-2 text-sm text-muted">{{ $event->scheduleLabel() }}</p>
                    <p class="mt-3 text-xs font-semibold text-muted">
                        {{ $event->days_count }} day{{ $event->days_count === 1 ? '' : 's' }}
                        · {{ $event->active_enrollments_count }} registered
                    </p>
                    <div class="mt-auto pt-5">
                        @if ($event->days_count < 1)
                            <p class="rounded-xl border border-brand-orange/20 bg-brand-orange-soft px-3 py-2 text-xs font-semibold text-brand-orange">
                                Add event days before using the desk.
                            </p>
                        @else
                            <a href="{{ route('admin.attendance.desk', $event) }}" class="btn-primary w-full justify-center">
                                Open attendance desk
                            </a>
                        @endif
                    </div>
                </article>
            @endforeach
        </div>
    @endif
</div>
@endsection
