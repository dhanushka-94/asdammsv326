@extends('layouts.dashboard')

@section('title', 'Edit Event')
@section('page-title', 'Edit event')
@section('page-subtitle', $event->name)

@section('page-actions')
<a href="{{ route('admin.events.show', $event) }}" class="btn-outline">View</a>
@endsection

@section('content')
<div class="mx-auto max-w-4xl">
    <form method="POST" action="{{ route('admin.events.update', $event) }}" enctype="multipart/form-data" class="card p-5 sm:p-8">
        @csrf
        @method('PUT')
        @if ($errors->any())
            <div class="alert-error mb-5">
                <ul class="list-disc pl-4">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        @endif
        @include('admin.events._form', ['event' => $event])
        <div class="mt-6 flex justify-end gap-3 border-t border-slate-100 pt-5">
            <a href="{{ route('admin.events.show', $event) }}" class="btn-outline">Cancel</a>
            <button type="submit" class="btn-secondary">Save changes</button>
        </div>
    </form>
</div>
@endsection
