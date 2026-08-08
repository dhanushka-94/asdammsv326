@extends('layouts.dashboard')

@section('title', 'Edit User')
@section('page-title', 'Edit system user')
@section('page-subtitle', 'Update access details for ' . $user->name)

@section('page-actions')
<a href="{{ route('admin.users.show', $user) }}" class="btn-outline">View</a>
@endsection

@section('content')
<div class="mx-auto max-w-3xl">
    <form method="POST" action="{{ route('admin.users.update', $user) }}" enctype="multipart/form-data" class="card p-5 sm:p-8">
        @csrf
        @method('PUT')
        @include('users._form', ['user' => $user])

        <div class="mt-6 flex flex-col-reverse gap-3 border-t border-slate-100 pt-5 sm:flex-row sm:justify-end">
            <a href="{{ route('admin.users.index') }}" class="btn-outline">Cancel</a>
            <button type="submit" class="btn-secondary">Save changes</button>
        </div>
    </form>
</div>
@endsection
