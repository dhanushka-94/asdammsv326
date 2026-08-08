@extends('layouts.dashboard')

@section('title', 'Sub-institutes')
@section('page-title', 'Sub-institutes')
@section('page-subtitle', 'Settings · under a main institute')

@section('page-actions')
@if (auth()->user()->canManageInstitutes())
<a href="{{ route('admin.sub-institutes.import') }}" class="btn-outline">Import CSV</a>
<a href="{{ route('admin.sub-institutes.create', $instituteId ? ['institute_id' => $instituteId] : []) }}" class="btn-accent">Add sub-institute</a>
@endif
@endsection

@section('content')
<div class="mb-4 card p-4">
    <form method="GET" action="{{ route('admin.sub-institutes.index') }}" class="flex flex-wrap items-end gap-3" data-auto-submit>
        <div class="min-w-[220px] flex-1">
            <label for="institute_id" class="form-label">Filter by institute</label>
            <select id="institute_id" name="institute_id" class="form-select">
                <option value="">All institutes</option>
                @foreach ($institutes as $institute)
                    <option value="{{ $institute->id }}" @selected((string) $instituteId === (string) $institute->id)>{{ $institute->name }}</option>
                @endforeach
            </select>
        </div>
    </form>
</div>

<div class="card">
    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Sub-institute</th>
                    <th>Institute</th>
                    <th>Sections</th>
                    <th>Status</th>
                    <th class="text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($subInstitutes as $subInstitute)
                    <tr>
                        <td class="font-semibold text-ink">{{ $subInstitute->name }}</td>
                        <td class="text-muted">{{ $subInstitute->institute?->name }}</td>
                        <td>
                            <a href="{{ route('admin.sections.index', ['sub_institute_id' => $subInstitute->id, 'institute_id' => $subInstitute->institute_id]) }}" class="text-sm font-medium text-brand-blue hover:underline">
                                {{ $subInstitute->sections_count }}
                            </a>
                        </td>
                        <td>
                            <span class="{{ $subInstitute->is_active ? 'badge-green' : 'badge-muted' }}">
                                {{ $subInstitute->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td>
                            @if (auth()->user()->canManageInstitutes())
                                <div class="flex justify-end gap-1">
                                    <a href="{{ route('admin.sub-institutes.edit', $subInstitute) }}" class="btn-ghost px-2.5 py-1.5 text-brand-blue">Edit</a>
                                    <form method="POST" action="{{ route('admin.sub-institutes.destroy', $subInstitute) }}" data-confirm="Delete {{ $subInstitute->name }}?">
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
                    <tr><td colspan="5" class="py-12 text-center text-muted">No sub-institutes yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($subInstitutes->hasPages())
        <div class="border-t border-slate-100 px-4 py-3">{{ $subInstitutes->links() }}</div>
    @endif
</div>
@endsection
