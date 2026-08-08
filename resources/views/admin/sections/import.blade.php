@extends('layouts.dashboard')

@section('title', 'Import Sections')
@section('page-title', 'Import sections')
@section('page-subtitle', 'Settings · CSV import')

@section('page-actions')
<a href="{{ route('admin.sections.import.template') }}" class="btn-outline">Download template</a>
<a href="{{ route('admin.sections.index') }}" class="btn-outline">Back</a>
@endsection

@section('content')
<div class="mx-auto max-w-3xl space-y-6">
    <div class="card p-5 sm:p-8">
        @if ($errors->any())
            <div class="alert-error mb-5"><span>{{ $errors->first() }}</span></div>
        @endif
        <form method="POST" action="{{ route('admin.sections.import.store') }}" enctype="multipart/form-data" class="space-y-5">
            @csrf
            <div>
                <label for="csv_file" class="form-label">CSV file</label>
                <input id="csv_file" type="file" name="csv_file" accept=".csv,text/csv" required class="form-input">
                <p class="mt-1 text-xs text-muted">Required: <code class="text-ink">institute</code>, <code class="text-ink">sub_institute</code>, <code class="text-ink">name</code>. Optional: <code class="text-ink">is_active</code>. Parents must already exist.</p>
            </div>
            <div class="flex justify-end gap-3 border-t border-slate-100 pt-5">
                <a href="{{ route('admin.sections.index') }}" class="btn-outline">Cancel</a>
                <button type="submit" class="btn-primary">Import CSV</button>
            </div>
        </form>
    </div>
    @include('admin.lookups._import-report')
</div>
@endsection
