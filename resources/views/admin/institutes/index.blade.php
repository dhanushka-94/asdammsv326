@extends('layouts.dashboard')

@section('title', 'Institutes')
@section('page-title', 'Institutes')
@section('page-subtitle', 'Settings · main institutes')

@section('page-actions')
@if (auth()->user()->canManageInstitutes())
<a href="{{ route('admin.institutes.import') }}" class="btn-outline">Import CSV</a>
<a href="{{ route('admin.institutes.create') }}" class="btn-accent">Add institute</a>
@endif
@endsection

@section('content')
<div class="card">
    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Sub-institutes</th>
                    <th>Status</th>
                    <th class="text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($institutes as $institute)
                    <tr>
                        <td class="font-semibold text-ink">{{ $institute->name }}</td>
                        <td>
                            <a href="{{ route('admin.sub-institutes.index', ['institute_id' => $institute->id]) }}" class="text-sm font-medium text-brand-blue hover:underline">
                                {{ $institute->sub_institutes_count }}
                            </a>
                        </td>
                        <td>
                            <span class="{{ $institute->is_active ? 'badge-green' : 'badge-muted' }}">
                                {{ $institute->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td>
                            @if (auth()->user()->canManageInstitutes())
                                <div class="flex justify-end gap-1">
                                    <a href="{{ route('admin.institutes.edit', $institute) }}" class="btn-ghost px-2.5 py-1.5 text-brand-blue">Edit</a>
                                    <form method="POST" action="{{ route('admin.institutes.destroy', $institute) }}" data-confirm="Delete {{ $institute->name }}?">
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
                    <tr><td colspan="4" class="py-12 text-center text-muted">No institutes yet. Add one or import a CSV.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($institutes->hasPages())
        <div class="border-t border-slate-100 px-4 py-3">{{ $institutes->links() }}</div>
    @endif
</div>
@endsection
