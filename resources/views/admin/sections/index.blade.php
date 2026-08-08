@extends('layouts.dashboard')

@section('title', 'Sections')
@section('page-title', 'Sections')
@section('page-subtitle', 'Settings · under a sub-institute')

@section('page-actions')
@if (auth()->user()->canManageInstitutes())
<a href="{{ route('admin.sections.import') }}" class="btn-outline">Import CSV</a>
<a href="{{ route('admin.sections.create', $subInstituteId ? ['sub_institute_id' => $subInstituteId] : []) }}" class="btn-accent">Add section</a>
@endif
@endsection

@section('content')
<div class="mb-4 card p-4">
    <form method="GET" action="{{ route('admin.sections.index') }}" class="grid gap-3 sm:grid-cols-2" data-auto-submit>
        <div>
            <label for="institute_id" class="form-label">Institute</label>
            <select id="institute_id" name="institute_id" class="form-select">
                <option value="">All institutes</option>
                @foreach ($institutes as $institute)
                    <option value="{{ $institute->id }}" @selected((string) $instituteId === (string) $institute->id)>{{ $institute->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="sub_institute_id" class="form-label">Sub-institute</label>
            <select id="sub_institute_id" name="sub_institute_id" class="form-select">
                <option value="">All sub-institutes</option>
                @foreach ($subInstitutes as $sub)
                    <option value="{{ $sub->id }}" @selected((string) $subInstituteId === (string) $sub->id)>{{ $sub->name }}</option>
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
                    <th>Section</th>
                    <th>Sub-institute</th>
                    <th>Institute</th>
                    <th>Status</th>
                    <th class="text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($sections as $section)
                    <tr>
                        <td class="font-semibold text-ink">{{ $section->name }}</td>
                        <td class="text-muted">{{ $section->subInstitute?->name }}</td>
                        <td class="text-muted">{{ $section->subInstitute?->institute?->name }}</td>
                        <td>
                            <span class="{{ $section->is_active ? 'badge-green' : 'badge-muted' }}">
                                {{ $section->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td>
                            @if (auth()->user()->canManageInstitutes())
                                <div class="flex justify-end gap-1">
                                    <a href="{{ route('admin.sections.edit', $section) }}" class="btn-ghost px-2.5 py-1.5 text-brand-blue">Edit</a>
                                    <form method="POST" action="{{ route('admin.sections.destroy', $section) }}" data-confirm="Delete {{ $section->name }}?">
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
                    <tr><td colspan="5" class="py-12 text-center text-muted">No sections yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($sections->hasPages())
        <div class="border-t border-slate-100 px-4 py-3">{{ $sections->links() }}</div>
    @endif
</div>
@endsection
