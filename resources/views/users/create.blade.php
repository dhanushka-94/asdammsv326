@extends('layouts.dashboard')

@section('title', 'Add User')
@section('page-title', 'Add system user')
@section('page-subtitle', 'Create a new account with system access')

@section('page-actions')
<a href="{{ route('admin.users.index') }}" class="btn-outline">Back</a>
@endsection

@section('content')
<div class="mx-auto max-w-3xl">
    <form method="POST" action="{{ route('admin.users.store') }}" class="card p-5 sm:p-8">
        @csrf
        @include('users._form')

        <div class="mt-6 flex flex-col-reverse gap-3 border-t border-slate-100 pt-5 sm:flex-row sm:justify-end">
            <a href="{{ route('admin.users.index') }}" class="btn-outline">Cancel</a>
            <button type="submit" class="btn-primary">Create user</button>
        </div>
    </form>
</div>
@endsection
