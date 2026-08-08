@extends('layouts.dashboard')

@section('title', 'Designations')
@section('page-title', 'Designations')
@section('page-subtitle', 'Settings · manage member designations')

@section('page-actions')
@if (auth()->user()->canManageDesignations())
<a href="{{ route('admin.designations.import') }}" class="btn-outline">Import CSV</a>
<a href="{{ route('admin.designations.create') }}" class="btn-accent">Add designation</a>
@endif
@endsection

@section('content')
<div class="card">
    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Members</th>
                    <th>Status</th>
                    <th class="text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($designations as $designation)
                    <tr>
                        <td class="font-semibold text-ink">{{ $designation->name }}</td>
                        <td class="text-muted">{{ $designation->members_count }}</td>
                        <td>
                            <span class="{{ $designation->is_active ? 'badge-green' : 'badge-muted' }}">
                                {{ $designation->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td>
                            @if (auth()->user()->canManageDesignations())
                                <div class="flex justify-end gap-1">
                                    <a href="{{ route('admin.designations.edit', $designation) }}" class="btn-ghost px-2.5 py-1.5 text-brand-blue">Edit</a>
                                    <form method="POST" action="{{ route('admin.designations.destroy', $designation) }}" data-confirm="Delete {{ $designation->name }}?">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-ghost px-2.5 py-1.5 text-red-600">Delete</button>
                                    </form>
                                </div>
                            @else
                                <span class="block text-right text-xs text-muted">View only</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="py-12 text-center text-muted">No designations yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($designations->hasPages())
        <div class="border-t border-slate-100 px-4 py-3">{{ $designations->links() }}</div>
    @endif
</div>
@endsection
