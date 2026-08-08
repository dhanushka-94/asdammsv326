@extends('layouts.dashboard')

@section('title', 'Member Categories')
@section('page-title', 'Member categories')
@section('page-subtitle', 'Settings · categorize members')

@section('page-actions')
@if (auth()->user()->canManageMemberCategories())
<a href="{{ route('admin.member-categories.create') }}" class="btn-accent">Add category</a>
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
                @forelse ($categories as $category)
                    <tr>
                        <td class="font-semibold text-ink">{{ $category->name }}</td>
                        <td class="text-muted">{{ $category->members_count }}</td>
                        <td>
                            <span class="{{ $category->is_active ? 'badge-green' : 'badge-muted' }}">
                                {{ $category->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td>
                            @if (auth()->user()->canManageMemberCategories())
                                <div class="flex justify-end gap-1">
                                    <a href="{{ route('admin.member-categories.edit', $category) }}" class="btn-ghost px-2.5 py-1.5 text-brand-blue">Edit</a>
                                    <form method="POST" action="{{ route('admin.member-categories.destroy', $category) }}" data-confirm="Delete {{ $category->name }}?">
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
                    <tr><td colspan="4" class="py-12 text-center text-muted">No categories yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($categories->hasPages())
        <div class="border-t border-slate-100 px-4 py-3">{{ $categories->links() }}</div>
    @endif
</div>
@endsection
