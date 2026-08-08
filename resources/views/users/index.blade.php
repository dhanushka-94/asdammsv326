@extends('layouts.dashboard')

@section('title', 'System Users')
@section('page-title', 'System Users')
@section('page-subtitle', 'Manage accounts that can access the system')

@section('page-actions')
@if (auth()->user()->canManageUsers())
<a href="{{ route('admin.users.create') }}" class="btn-accent">
    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
    <span class="hidden sm:inline">Add user</span>
</a>
@endif
@endsection

@section('content')
<div class="card">
    <form method="GET" action="{{ route('admin.users.index') }}" class="grid gap-3 border-b border-slate-100 p-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="sm:col-span-2">
            <label for="search" class="sr-only">Search</label>
            <input id="search" type="search" name="search" value="{{ request('search') }}" class="form-input" placeholder="Search name, email, or phone">
        </div>
        <div>
            <select name="role" class="form-select">
                <option value="">All roles</option>
                @foreach (\App\Support\UserRole::labels() as $value => $label)
                    <option value="{{ $value }}" @selected(request('role') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex gap-2">
            <select name="status" class="form-select">
                <option value="">All status</option>
                <option value="active" @selected(request('status') === 'active')>Active</option>
                <option value="inactive" @selected(request('status') === 'inactive')>Inactive</option>
            </select>
            <button type="submit" class="btn-primary shrink-0">Filter</button>
        </div>
    </form>

    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>User</th>
                    <th>Phone</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th class="text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($users as $user)
                    <tr>
                        <td>
                            <div class="flex items-center gap-3">
                                <div class="flex h-10 w-10 shrink-0 items-center justify-center overflow-hidden rounded-full bg-brand-green-soft text-sm font-bold text-brand-green">
                                    @if ($user->profileImageUrl())
                                        <img src="{{ $user->profileImageUrl() }}" alt="" class="h-full w-full object-cover">
                                    @else
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    @endif
                                </div>
                                <div>
                                    <p class="font-semibold text-ink">{{ $user->name }}</p>
                                    <p class="text-xs text-muted">{{ $user->email }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="text-muted">{{ $user->phone ?: '—' }}</td>
                        <td>
                            @if ($user->role === \App\Support\UserRole::SUPER_ADMIN)
                                <span class="badge-orange">{{ $user->roleLabel() }}</span>
                            @elseif ($user->role === \App\Support\UserRole::ADMIN)
                                <span class="badge-blue">{{ $user->roleLabel() }}</span>
                            @else
                                <span class="badge-green">{{ $user->roleLabel() }}</span>
                            @endif
                        </td>
                        <td>
                            @if ($user->status === 'active')
                                <span class="badge-green">Active</span>
                            @else
                                <span class="badge-muted">Inactive</span>
                            @endif
                        </td>
                        <td>
                            <div class="flex flex-wrap items-center justify-end gap-1">
                                <a href="{{ route('admin.users.show', $user) }}" class="btn-ghost px-2.5 py-1.5">View</a>
                                <a href="{{ route('admin.users.edit', $user) }}" class="btn-ghost px-2.5 py-1.5 text-brand-blue">Edit</a>
                                @if ($user->id !== auth()->id())
                                    <form method="POST" action="{{ route('admin.users.destroy', $user) }}" data-confirm="Delete {{ $user->name }}? This cannot be undone.">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-ghost px-2.5 py-1.5 text-red-600 hover:bg-red-50">Delete</button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="py-14 text-center">
                            <p class="font-semibold text-ink">No users found</p>
                            <p class="mt-1 text-sm text-muted">Try adjusting filters or create a new system user.</p>
                            <a href="{{ route('admin.users.create') }}" class="btn-accent mt-4">Add user</a>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($users->hasPages())
        <div class="border-t border-slate-100 px-4 py-3">
            {{ $users->links() }}
        </div>
    @endif
</div>
@endsection
