@props([
    'title',
    'subtitle' => null,
    'type' => 'bar',
    'labels' => [],
    'values' => [],
    'height' => 'h-72',
])

@php
    $hasData = collect($values)->contains(fn ($v) => (int) $v > 0);
@endphp

<div class="card p-5">
    <div class="mb-4">
        <h2 class="font-display text-base font-bold text-ink">{{ $title }}</h2>
        @if ($subtitle)
            <p class="mt-0.5 text-sm text-muted">{{ $subtitle }}</p>
        @endif
    </div>

    @if ($hasData)
        <div class="{{ $height }}">
            <canvas
                data-report-chart
                data-chart-type="{{ $type }}"
                data-chart-labels='@json($labels)'
                data-chart-values='@json($values)'
            ></canvas>
        </div>
    @else
        <div class="{{ $height }} flex items-center justify-center rounded-xl border border-dashed border-slate-200 bg-slate-50">
            <p class="text-sm text-muted">No data to chart yet.</p>
        </div>
    @endif
</div>
