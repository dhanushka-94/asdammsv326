@extends('layouts.dashboard')

@section('title', 'Add Event')
@section('page-title', 'Add event')
@section('page-subtitle', 'Create an event for the member pool')

@section('page-actions')
<a href="{{ route('admin.events.index') }}" class="btn-outline">Back</a>
@endsection

@section('content')
<div class="mx-auto max-w-4xl">
    <form method="POST" action="{{ route('admin.events.store') }}" enctype="multipart/form-data" class="card p-5 sm:p-8">
        @csrf
        @if ($errors->any())
            <div class="alert-error mb-5">
                <ul class="list-disc pl-4">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        @endif
        @include('admin.events._form')
        <div class="mt-6 flex justify-end gap-3 border-t border-slate-100 pt-5">
            <a href="{{ route('admin.events.index') }}" class="btn-outline">Cancel</a>
            <button type="submit" class="btn-primary">Create event</button>
        </div>
    </form>
</div>
@endsection
