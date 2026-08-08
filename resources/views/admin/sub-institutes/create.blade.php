@extends('layouts.dashboard')

@section('title', 'Add Sub-institute')
@section('page-title', 'Add sub-institute')
@section('page-subtitle', 'Settings')

@section('content')
<div class="mx-auto max-w-xl">
    <form method="POST" action="{{ route('admin.sub-institutes.store') }}" class="card p-5 sm:p-8">
        @csrf
        @if ($errors->any())
            <div class="alert-error mb-5"><span>{{ $errors->first() }}</span></div>
        @endif
        <div>
            <label for="institute_id" class="form-label">Main institute</label>
            <select id="institute_id" name="institute_id" required class="form-select">
                <option value="">Select institute</option>
                @foreach ($institutes as $institute)
                    <option value="{{ $institute->id }}" @selected((string) old('institute_id', $selectedInstituteId) === (string) $institute->id)>{{ $institute->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="mt-4">
            <label for="name" class="form-label">Sub-institute name</label>
            <input id="name" type="text" name="name" value="{{ old('name') }}" required class="form-input">
        </div>
        <label class="mt-4 flex items-center gap-2 text-sm text-muted">
            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', true)) class="h-4 w-4 rounded border-slate-300 text-brand-green focus:ring-brand-green">
            Active
        </label>
        <div class="mt-6 flex justify-end gap-3">
            <a href="{{ route('admin.sub-institutes.index') }}" class="btn-outline">Cancel</a>
            <button type="submit" class="btn-primary">Create</button>
        </div>
    </form>
</div>
@endsection
