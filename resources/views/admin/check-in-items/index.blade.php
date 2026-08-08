@extends('layouts.dashboard')

@section('title', 'Check-in Items')
@section('page-title', 'Check-in items')
@section('page-subtitle', 'Settings · items given at attendance check-in')

@section('page-actions')
@if (auth()->user()->canManageCheckInItems())
<a href="{{ route('admin.check-in-items.create') }}" class="btn-accent">Add item</a>
@endif
@endsection

@section('content')
<div class="card">
    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Order</th>
                    <th>Name</th>
                    <th>Times given</th>
                    <th>Status</th>
                    <th class="text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($items as $item)
                    <tr>
                        <td class="text-muted">{{ $item->sort_order }}</td>
                        <td class="font-semibold text-ink">{{ $item->name }}</td>
                        <td class="text-muted">{{ $item->attendances_count }}</td>
                        <td>
                            <span class="{{ $item->is_active ? 'badge-green' : 'badge-muted' }}">
                                {{ $item->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td>
                            @if (auth()->user()->canManageCheckInItems())
                                <div class="flex justify-end gap-1">
                                    <a href="{{ route('admin.check-in-items.edit', $item) }}" class="btn-ghost px-2.5 py-1.5 text-brand-blue">Edit</a>
                                    <form method="POST" action="{{ route('admin.check-in-items.destroy', $item) }}" data-confirm="Delete {{ $item->name }}?">
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
                    <tr><td colspan="5" class="py-12 text-center text-muted">No check-in items yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($items->hasPages())
        <div class="border-t border-slate-100 px-4 py-3">{{ $items->links() }}</div>
    @endif
</div>
@endsection
