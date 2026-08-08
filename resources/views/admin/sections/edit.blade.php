@extends('layouts.dashboard')

@section('title', 'Edit Section')
@section('page-title', 'Edit section')
@section('page-subtitle', 'Settings')

@section('content')
<div class="mx-auto max-w-xl">
    <form method="POST" action="{{ route('admin.sections.update', $section) }}" class="card p-5 sm:p-8">
        @csrf
        @method('PUT')
        @if ($errors->any())
            <div class="alert-error mb-5"><span>{{ $errors->first() }}</span></div>
        @endif
        <div>
            <label for="sub_institute_id" class="form-label">Sub-institute</label>
            <select id="sub_institute_id" name="sub_institute_id" required class="form-select">
                @foreach ($subInstitutes as $sub)
                    <option value="{{ $sub->id }}" @selected((string) old('sub_institute_id', $section->sub_institute_id) === (string) $sub->id)>
                        {{ $sub->institute?->name }} · {{ $sub->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="mt-4">
            <label for="name" class="form-label">Section name</label>
            <input id="name" type="text" name="name" value="{{ old('name', $section->name) }}" required class="form-input">
        </div>
        <label class="mt-4 flex items-center gap-2 text-sm text-muted">
            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $section->is_active)) class="h-4 w-4 rounded border-slate-300 text-brand-green focus:ring-brand-green">
            Active
        </label>
        <div class="mt-6 flex justify-end gap-3">
            <a href="{{ route('admin.sections.index') }}" class="btn-outline">Cancel</a>
            <button type="submit" class="btn-secondary">Save</button>
        </div>
    </form>
</div>
@endsection
