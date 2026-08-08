@extends('layouts.dashboard')

@section('title', 'Edit Category')
@section('page-title', 'Edit category')
@section('page-subtitle', 'Settings')

@section('content')
<div class="mx-auto max-w-xl">
    <form method="POST" action="{{ route('admin.member-categories.update', $memberCategory) }}" class="card p-5 sm:p-8">
        @csrf
        @method('PUT')
        @if ($errors->any())
            <div class="alert-error mb-5"><span>{{ $errors->first() }}</span></div>
        @endif
        <div>
            <label for="name" class="form-label">Category name</label>
            <input id="name" type="text" name="name" value="{{ old('name', $memberCategory->name) }}" required class="form-input">
        </div>
        <label class="mt-4 flex items-center gap-2 text-sm text-muted">
            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $memberCategory->is_active)) class="h-4 w-4 rounded border-slate-300 text-brand-green focus:ring-brand-green">
            Active
        </label>
        <div class="mt-6 flex justify-end gap-3">
            <a href="{{ route('admin.member-categories.index') }}" class="btn-outline">Cancel</a>
            <button type="submit" class="btn-secondary">Save</button>
        </div>
    </form>
</div>
@endsection
