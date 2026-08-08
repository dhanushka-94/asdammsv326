@extends('layouts.dashboard')

@section('title', 'Edit Profile')
@section('page-title', 'Edit Profile')
@section('page-subtitle', 'Update your account details')

@section('page-actions')
<a href="{{ route('admin.profile.show') }}" class="btn-outline">Back</a>
@endsection

@section('content')
<div class="mx-auto max-w-2xl">
    <form method="POST" action="{{ route('admin.profile.update') }}" class="card p-5 sm:p-8">
        @csrf
        @method('PUT')

        @if ($errors->any())
            <div class="alert-error mb-5">
                <div>
                    <p class="font-semibold">Please fix the following:</p>
                    <ul class="mt-1 list-disc pl-4">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        <div class="grid gap-5 sm:grid-cols-2">
            <div class="sm:col-span-2">
                <label for="name" class="form-label">Full name</label>
                <input id="name" type="text" name="name" value="{{ old('name', $user->name) }}" required class="form-input">
            </div>

            <div>
                <label for="email" class="form-label">Email</label>
                <input id="email" type="email" name="email" value="{{ old('email', $user->email) }}" required class="form-input">
            </div>

            <div>
                <label for="phone" class="form-label">Phone</label>
                <input id="phone" type="text" name="phone" value="{{ old('phone', $user->phone) }}" class="form-input" placeholder="0771234567">
            </div>

            <div class="sm:col-span-2 rounded-xl bg-surface p-4">
                <p class="text-xs font-semibold uppercase tracking-wide text-muted">Role</p>
                <p class="mt-1 font-semibold text-ink">{{ $user->roleLabel() }}</p>
                <p class="mt-1 text-xs text-muted">Role can only be changed by a Super Admin from System Users.</p>
            </div>

            <div>
                <label for="password" class="form-label">New password <span class="font-normal text-muted">(optional)</span></label>
                <input id="password" type="password" name="password" class="form-input" autocomplete="new-password" minlength="8">
                <p class="mt-1 text-xs text-muted">At least 8 characters. Leave blank to keep current.</p>
            </div>

            <div>
                <label for="password_confirmation" class="form-label">Confirm password</label>
                <input id="password_confirmation" type="password" name="password_confirmation" class="form-input" autocomplete="new-password" minlength="8">
            </div>
        </div>

        <div class="mt-6 flex flex-wrap gap-3">
            <button type="submit" class="btn-primary">Save changes</button>
            <a href="{{ route('admin.profile.show') }}" class="btn-outline">Cancel</a>
        </div>
    </form>
</div>
@endsection
