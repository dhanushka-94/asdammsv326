@extends('layouts.dashboard')

@section('title', 'My Profile')
@section('page-title', 'My Profile')
@section('page-subtitle', 'Your system user account')

@section('page-actions')
<a href="{{ route('admin.profile.edit') }}" class="btn-secondary">Edit profile</a>
@endsection

@section('content')
<div class="mx-auto max-w-3xl space-y-5">
    <div class="card overflow-hidden">
        <div class="bg-gradient-to-r from-brand-blue via-brand-green to-brand-orange px-5 py-8 sm:px-8">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center">
                <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-white/15 text-2xl font-bold text-white backdrop-blur">
                    {{ strtoupper(substr($user->name, 0, 1)) }}
                </div>
                <div class="text-white">
                    <h2 class="font-display text-2xl font-bold">{{ $user->name }}</h2>
                    <p class="text-white/80">{{ $user->email }}</p>
                </div>
            </div>
        </div>

        <div class="grid gap-4 p-5 sm:grid-cols-2 sm:p-8">
            <div class="rounded-xl bg-surface p-4">
                <p class="text-xs font-semibold uppercase tracking-wide text-muted">Phone</p>
                <p class="mt-1 font-semibold text-ink">{{ $user->phone ?: 'Not provided' }}</p>
            </div>
            <div class="rounded-xl bg-surface p-4">
                <p class="text-xs font-semibold uppercase tracking-wide text-muted">Role</p>
                <p class="mt-2">
                    @if ($user->role === \App\Support\UserRole::SUPER_ADMIN)
                        <span class="badge-orange">{{ $user->roleLabel() }}</span>
                    @elseif ($user->role === \App\Support\UserRole::ADMIN)
                        <span class="badge-blue">{{ $user->roleLabel() }}</span>
                    @else
                        <span class="badge-green">{{ $user->roleLabel() }}</span>
                    @endif
                </p>
            </div>
            <div class="rounded-xl bg-surface p-4">
                <p class="text-xs font-semibold uppercase tracking-wide text-muted">Status</p>
                <p class="mt-2">
                    @if ($user->status === 'active')
                        <span class="badge-green">Active</span>
                    @else
                        <span class="badge-muted">Inactive</span>
                    @endif
                </p>
            </div>
            <div class="rounded-xl bg-surface p-4">
                <p class="text-xs font-semibold uppercase tracking-wide text-muted">Member since</p>
                <p class="mt-1 font-semibold text-ink">{{ \App\Support\SriLankaDate::datetime($user->created_at) }}</p>
            </div>
            @if ($user->canAccessAttendance())
                <div class="rounded-xl bg-surface p-4 sm:col-span-2">
                    <p class="text-xs font-semibold uppercase tracking-wide text-muted">Attendance desk PIN</p>
                    <p class="mt-1 font-semibold text-ink">
                        @if ($user->hasDeskPin())
                            Set — use Lock desk on the attendance screen for a quick lock
                        @else
                            Not set
                        @endif
                    </p>
                    <a href="{{ route('admin.profile.edit') }}" class="mt-2 inline-flex text-sm font-semibold text-brand-blue underline">
                        {{ $user->hasDeskPin() ? 'Change desk PIN' : 'Set desk PIN' }}
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
