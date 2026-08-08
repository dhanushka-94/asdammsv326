@php
    $tabs = [
        ['route' => 'admin.reports.index', 'label' => 'Overview', 'match' => 'admin.reports.index'],
        ['route' => 'admin.reports.members', 'label' => 'Members', 'match' => 'admin.reports.members'],
        ['route' => 'admin.reports.attendance', 'label' => 'Attendance', 'match' => 'admin.reports.attendance'],
        ['route' => 'admin.reports.items', 'label' => 'Check-in items', 'match' => 'admin.reports.items'],
    ];
@endphp

<nav class="mb-6 flex flex-wrap gap-2 border-b border-slate-200 pb-3" aria-label="Reports">
    @foreach ($tabs as $tab)
        <a
            href="{{ route($tab['route']) }}"
            class="rounded-lg px-3.5 py-2 text-sm font-semibold transition {{ request()->routeIs($tab['match']) ? 'bg-brand-blue text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}"
        >
            {{ $tab['label'] }}
        </a>
    @endforeach
</nav>
