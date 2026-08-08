@extends('layouts.dashboard')

@section('title', $user->name)
@section('page-title', 'User details')
@section('page-subtitle', 'System access profile')

@section('page-actions')
<a href="{{ route('admin.users.edit', $user) }}" class="btn-secondary">Edit</a>
<a href="{{ route('admin.users.index') }}" class="btn-outline hidden sm:inline-flex">Back</a>
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
                <p class="text-xs font-semibold uppercase tracking-wide text-muted">Created</p>
                <p class="mt-1 font-semibold text-ink">{{ \App\Support\SriLankaDate::datetime($user->created_at) }}</p>
            </div>
        </div>
    </div>

    @if ($user->isReception())
        <div class="card p-5 sm:p-8">
            <h3 class="font-display text-base font-bold text-ink">Assigned events</h3>
            <p class="mt-1 text-sm text-muted">This reception user can run attendance for these events only.</p>
            @if ($user->receptionEvents->isEmpty())
                <p class="mt-4 rounded-xl border border-dashed border-slate-200 bg-surface/60 px-4 py-6 text-center text-sm text-muted">No events assigned yet.</p>
            @else
                <ul class="mt-4 space-y-2">
                    @foreach ($user->receptionEvents as $event)
                        <li class="rounded-xl border border-slate-200 bg-surface/50 px-4 py-3 text-sm">
                            <span class="font-semibold text-ink">{{ $event->name }}</span>
                            <span class="mt-1 block text-xs text-muted">{{ \App\Support\SriLankaDate::date($event->start_date) }}</span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    @endif

    <div class="card space-y-4 p-5">
        <div>
            <p class="font-semibold text-ink">Password management</p>
            <p class="text-sm text-muted">
                Reset to default style (first 4 digits of phone + @ASDA) or require a password change on next login.
                @if ($user->must_change_password)
                    <span class="badge-orange ml-1">Password change required</span>
                @endif
            </p>
        </div>
        <div class="flex flex-col gap-3 sm:flex-row sm:flex-wrap">
            <form method="POST" action="{{ route('admin.users.reset-password', $user) }}" data-confirm="Reset {{ $user->name }} password to {{ $user->defaultPassword() }} and require a new password on next login?">
                @csrf
                <button type="submit" class="btn-accent">Reset password to default</button>
            </form>
            <form method="POST" action="{{ route('admin.users.require-password-change', $user) }}" data-confirm="Require {{ $user->name }} to set a new password on next login?">
                @csrf
                <button type="submit" class="btn-secondary">Require password change on next login</button>
            </form>
        </div>
        <p class="text-xs text-muted">
            Default password for this user: <span class="font-semibold text-ink">{{ $user->defaultPassword() }}</span>
            (from phone{{ $user->phone ? '' : ' / email digits' }}).
        </p>
    </div>

    @if ($user->id !== auth()->id())
        <div class="card flex flex-col gap-3 p-5 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="font-semibold text-ink">Danger zone</p>
                <p class="text-sm text-muted">Permanently remove this system access account.</p>
            </div>
            <form method="POST" action="{{ route('admin.users.destroy', $user) }}" data-confirm="Delete {{ $user->name }}? This cannot be undone.">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn-danger">Delete user</button>
            </form>
        </div>
    @endif
</div>
@endsection
