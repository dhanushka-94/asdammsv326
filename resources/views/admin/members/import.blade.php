@extends('layouts.dashboard')

@section('title', 'Import Members')
@section('page-title', 'Import members')
@section('page-subtitle', 'Upload a CSV file. Duplicate ID numbers (NIC) are skipped.')

@section('page-actions')
<a href="{{ route('admin.members.import.template') }}" class="btn-outline">Download template</a>
<a href="{{ route('admin.members.index') }}" class="btn-outline">Back</a>
@endsection

@section('content')
<div class="mx-auto max-w-3xl space-y-6">
    <div class="card p-5 sm:p-8">
        @if ($errors->any())
            <div class="alert-error mb-5">
                <ul class="list-disc pl-4">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.members.import.store') }}" enctype="multipart/form-data" class="space-y-5">
            @csrf
            <div>
                <label for="csv_file" class="form-label">CSV file</label>
                <input id="csv_file" type="file" name="csv_file" accept=".csv,text/csv" required class="form-input">
                <p class="mt-1 text-xs text-muted">Max 5 MB. Required columns: title, full_name, nic, designation, mobile_1.</p>
            </div>

            <div class="rounded-xl border border-slate-200 bg-surface/60 p-4 text-sm text-muted">
                <p class="font-semibold text-ink">Duplicate checking</p>
                <p class="mt-1">Rows with an ID number (NIC) that already exists in the system, or that repeats earlier in the same file, are skipped.</p>
            </div>

            <div class="rounded-xl border border-slate-200 bg-surface/60 p-4 text-sm text-muted">
                <p class="font-semibold text-ink">Designation names (use exact name)</p>
                <p class="mt-2 flex flex-wrap gap-2">
                    @forelse ($designations as $designation)
                        <span class="rounded-md bg-white px-2 py-1 text-xs text-ink ring-1 ring-slate-200">{{ $designation->name }}</span>
                    @empty
                        <span>No active designations found. Add designations first.</span>
                    @endforelse
                </p>
            </div>

            <div class="flex justify-end gap-3 border-t border-slate-100 pt-5">
                <a href="{{ route('admin.members.index') }}" class="btn-outline">Cancel</a>
                <button type="submit" class="btn-primary">Import CSV</button>
            </div>
        </form>
    </div>

    @if (session('import_report'))
        @php
            $report = session('import_report');
        @endphp
        <div class="card overflow-hidden">
            <div class="border-b border-slate-100 px-4 py-3 sm:px-6">
                <h2 class="text-sm font-semibold text-ink">Import report</h2>
                <p class="mt-1 text-sm text-muted">
                    {{ $report['imported'] }} imported ·
                    {{ $report['skipped_duplicates'] }} duplicate ID(s) ·
                    {{ $report['failed'] }} failed
                </p>
            </div>

            @if (! empty($report['errors']))
                <div class="table-wrap">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Row</th>
                                <th>ID number</th>
                                <th>Message</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($report['errors'] as $error)
                                <tr>
                                    <td>{{ $error['row'] }}</td>
                                    <td>{{ $error['nic'] ?? '—' }}</td>
                                    <td>{{ $error['message'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="px-4 py-6 text-sm text-muted sm:px-6">All rows imported successfully.</p>
            @endif
        </div>
    @endif
</div>
@endsection
