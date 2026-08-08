@extends('layouts.dashboard')

@section('title', 'Member reports')
@section('page-title', 'Member reports')
@section('page-subtitle', 'Category, designation, institute, and growth trends')

@section('content')
    @include('admin.reports._nav')

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="stat-card">
            <p class="text-sm font-medium text-muted">Total members</p>
            <p class="mt-2 font-display text-3xl font-bold text-brand-blue">{{ number_format($summary['total']) }}</p>
        </div>
        <div class="stat-card">
            <p class="text-sm font-medium text-muted">Approved & active</p>
            <p class="mt-2 font-display text-3xl font-bold text-brand-green">{{ number_format($summary['approved_active']) }}</p>
        </div>
        <div class="stat-card">
            <p class="text-sm font-medium text-muted">Categories</p>
            <p class="mt-2 font-display text-3xl font-bold text-brand-blue">{{ number_format($summary['categories']) }}</p>
        </div>
        <div class="stat-card">
            <p class="text-sm font-medium text-muted">Designations</p>
            <p class="mt-2 font-display text-3xl font-bold text-brand-orange">{{ number_format($summary['designations']) }}</p>
        </div>
    </div>

    <div class="mt-6 grid gap-6 xl:grid-cols-2">
        @include('admin.reports._chart-card', [
            'title' => 'Members by category',
            'type' => 'doughnut',
            'labels' => $charts['by_category']['labels'],
            'values' => $charts['by_category']['values'],
        ])
        @include('admin.reports._chart-card', [
            'title' => 'New members by month',
            'subtitle' => 'Last 12 months',
            'type' => 'line',
            'labels' => $charts['monthly']['labels'],
            'values' => $charts['monthly']['values'],
        ])
    </div>

    <div class="mt-6 grid gap-6 xl:grid-cols-2">
        @include('admin.reports._chart-card', [
            'title' => 'Top designations',
            'type' => 'bar',
            'labels' => $charts['by_designation']['labels'],
            'values' => $charts['by_designation']['values'],
        ])
        @include('admin.reports._chart-card', [
            'title' => 'Top institutes',
            'type' => 'bar',
            'labels' => $charts['by_institute']['labels'],
            'values' => $charts['by_institute']['values'],
        ])
    </div>

    <div class="mt-6 grid gap-6 xl:grid-cols-3">
        <div class="card">
            <div class="border-b border-slate-100 px-5 py-4">
                <h2 class="font-display text-base font-bold text-ink">By category</h2>
            </div>
            <div class="table-wrap">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Category</th>
                            <th class="text-right">Members</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($byCategory as $row)
                            <tr>
                                <td class="font-medium text-ink">{{ $row->label }}</td>
                                <td class="text-right tabular-nums">{{ number_format($row->total) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="2" class="py-8 text-center text-muted">No data.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card">
            <div class="border-b border-slate-100 px-5 py-4">
                <h2 class="font-display text-base font-bold text-ink">By registration</h2>
            </div>
            <div class="table-wrap">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Status</th>
                            <th class="text-right">Members</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($byRegistration as $row)
                            <tr>
                                <td><span class="badge-blue">{{ ucfirst($row->label) }}</span></td>
                                <td class="text-right tabular-nums">{{ number_format($row->total) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="2" class="py-8 text-center text-muted">No data.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card">
            <div class="border-b border-slate-100 px-5 py-4">
                <h2 class="font-display text-base font-bold text-ink">By account status</h2>
            </div>
            <div class="table-wrap">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Status</th>
                            <th class="text-right">Members</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($byStatus as $row)
                            <tr>
                                <td>
                                    <span class="{{ $row->label === 'active' ? 'badge-green' : 'badge-muted' }}">{{ ucfirst($row->label) }}</span>
                                </td>
                                <td class="text-right tabular-nums">{{ number_format($row->total) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="2" class="py-8 text-center text-muted">No data.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="mt-6 grid gap-6 xl:grid-cols-2">
        <div class="card">
            <div class="border-b border-slate-100 px-5 py-4">
                <h2 class="font-display text-base font-bold text-ink">Designation breakdown</h2>
            </div>
            <div class="table-wrap">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Designation</th>
                            <th class="text-right">Members</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($byDesignation as $row)
                            <tr>
                                <td class="font-medium text-ink">{{ $row->label }}</td>
                                <td class="text-right tabular-nums">{{ number_format($row->total) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="2" class="py-8 text-center text-muted">No data.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card">
            <div class="border-b border-slate-100 px-5 py-4">
                <h2 class="font-display text-base font-bold text-ink">Institute breakdown</h2>
            </div>
            <div class="table-wrap">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Institute</th>
                            <th class="text-right">Members</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($byInstitute as $row)
                            <tr>
                                <td class="font-medium text-ink">{{ $row->label }}</td>
                                <td class="text-right tabular-nums">{{ number_format($row->total) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="2" class="py-8 text-center text-muted">No data.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
