@extends('layouts.dashboard')

@section('title', 'Add Check-in Item')
@section('page-title', 'Add check-in item')
@section('page-subtitle', 'Settings')

@section('content')
<div class="mx-auto max-w-xl">
    <form method="POST" action="{{ route('admin.check-in-items.store') }}" class="card p-5 sm:p-8">
        @csrf
        @if ($errors->any())
            <div class="alert-error mb-5"><span>{{ $errors->first() }}</span></div>
        @endif
        <div>
            <label for="name" class="form-label">Item name</label>
            <input id="name" type="text" name="name" value="{{ old('name') }}" required class="form-input" placeholder="e.g. Meal Token, Flask">
        </div>
        <div class="mt-4">
            <label for="sort_order" class="form-label">Display order</label>
            <input id="sort_order" type="number" name="sort_order" value="{{ old('sort_order') }}" min="0" max="9999" class="form-input" placeholder="Auto if blank">
            <p class="mt-1 text-xs text-muted">Lower numbers appear first on the attendance desk.</p>
        </div>
        <label class="mt-4 flex items-center gap-2 text-sm text-muted">
            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', true)) class="h-4 w-4 rounded border-slate-300 text-brand-green focus:ring-brand-green">
            Active (shown on check-in desk)
        </label>
        <div class="mt-6 flex justify-end gap-3">
            <a href="{{ route('admin.check-in-items.index') }}" class="btn-outline">Cancel</a>
            <button type="submit" class="btn-primary">Create</button>
        </div>
    </form>
</div>
@endsection
