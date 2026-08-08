@extends('layouts.dashboard')

@section('title', 'Add Member')
@section('page-title', 'Add member')
@section('page-subtitle', 'Create a member record from the admin panel')

@section('page-actions')
<a href="{{ route('admin.members.index') }}" class="btn-outline">Back</a>
@endsection

@section('content')
<div class="mx-auto max-w-4xl">
    <form method="POST" action="{{ route('admin.members.store') }}" enctype="multipart/form-data" class="card p-5 sm:p-8">
        @csrf
        @if ($errors->any())
            <div class="alert-error mb-5">
                <ul class="list-disc pl-4">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        @endif
        @include('members._form-fields', ['designations' => $designations, 'categories' => $categories, 'orgTree' => $orgTree ?? [], 'showAdminStatus' => true])
        <div class="mt-6 flex justify-end gap-3 border-t border-slate-100 pt-5">
            <a href="{{ route('admin.members.index') }}" class="btn-outline">Cancel</a>
            <button type="submit" class="btn-primary">Create member</button>
        </div>
    </form>
</div>
@endsection
