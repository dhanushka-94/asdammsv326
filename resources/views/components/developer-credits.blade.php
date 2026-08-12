@props([
    'showCopyright' => false,
    'creditClass' => 'text-xs font-semibold text-brand-blue',
    'copyrightClass' => 'mt-1.5 text-[11px] leading-relaxed text-muted sm:text-xs',
])

<p {{ $attributes->class([$creditClass]) }}>
    {{ \App\Support\AppSettings::developerCredits() }}
</p>

@if ($showCopyright)
    <p class="{{ $copyrightClass }}">
        &copy; {{ now()->year }} {{ \App\Support\AppSettings::footerCopyright() }}
    </p>
@endif
