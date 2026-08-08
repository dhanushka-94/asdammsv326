@props([
    'member',
    'compact' => false,
])

@php
    $age = $member->age();
    $over61 = $member->isOverSixtyOne();
    $dob = $member->dateOfBirth();
@endphp

@if ($age !== null)
    @if ($compact)
        @if ($over61)
            <span class="badge-orange" title="Date of birth from NIC{{ $dob ? ': '.\App\Support\SriLankaDate::date($dob) : '' }}">
                Age {{ $age }} · Over 61
            </span>
        @else
            <span class="text-xs text-muted" title="Date of birth from NIC{{ $dob ? ': '.\App\Support\SriLankaDate::date($dob) : '' }}">
                Age {{ $age }}
            </span>
        @endif
    @else
        <div class="rounded-xl {{ $over61 ? 'border border-brand-orange/30 bg-brand-orange-soft' : 'bg-surface' }} p-4">
            <p class="text-xs font-semibold uppercase tracking-wide {{ $over61 ? 'text-brand-orange' : 'text-muted' }}">Age (from NIC)</p>
            <p class="mt-1 font-semibold {{ $over61 ? 'text-brand-orange' : 'text-ink' }}">
                {{ $age }} years
                @if ($over61)
                    <span class="badge-orange ml-2">Over 61</span>
                @endif
            </p>
            @if ($dob)
                <p class="mt-1 text-xs {{ $over61 ? 'text-brand-orange/80' : 'text-muted' }}">
                    DOB {{ \App\Support\SriLankaDate::date($dob) }}
                </p>
            @endif
        </div>
    @endif
@endif
