<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Institute;
use App\Models\Member;
use App\Models\SubInstitute;
use App\Support\LookupCsvImporter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SubInstituteController extends Controller
{
    public function index(Request $request): View
    {
        $instituteId = $request->integer('institute_id') ?: null;

        $subInstitutes = SubInstitute::query()
            ->with('institute')
            ->withCount('sections')
            ->when($instituteId, fn ($q) => $q->where('institute_id', $instituteId))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        $institutes = Institute::query()->orderBy('name')->get();

        return view('admin.sub-institutes.index', compact('subInstitutes', 'institutes', 'instituteId'));
    }

    public function create(Request $request): View
    {
        $institutes = Institute::query()->where('is_active', true)->orderBy('name')->get();

        return view('admin.sub-institutes.create', [
            'institutes' => $institutes,
            'selectedInstituteId' => old('institute_id', $request->integer('institute_id') ?: null),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'institute_id' => ['required', 'exists:institutes,id'],
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('sub_institutes', 'name')->where(fn ($q) => $q->where('institute_id', $request->input('institute_id'))),
            ],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $data['is_active'] = $request->boolean('is_active', true);

        SubInstitute::create($data);

        return redirect()->route('admin.sub-institutes.index')->with('success', 'Sub-institute created successfully.');
    }

    public function edit(SubInstitute $subInstitute): View
    {
        $institutes = Institute::query()->orderBy('name')->get();

        return view('admin.sub-institutes.edit', [
            'subInstitute' => $subInstitute,
            'institutes' => $institutes,
        ]);
    }

    public function update(Request $request, SubInstitute $subInstitute): RedirectResponse
    {
        $data = $request->validate([
            'institute_id' => ['required', 'exists:institutes,id'],
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('sub_institutes', 'name')
                    ->where(fn ($q) => $q->where('institute_id', $request->input('institute_id')))
                    ->ignore($subInstitute->id),
            ],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $oldName = $subInstitute->name;
        $data['is_active'] = $request->boolean('is_active');
        $subInstitute->update($data);

        if ($oldName !== $subInstitute->name) {
            Member::query()->where('sub_institute', $oldName)->update(['sub_institute' => $subInstitute->name]);
        }

        return redirect()->route('admin.sub-institutes.index')->with('success', 'Sub-institute updated successfully.');
    }

    public function destroy(SubInstitute $subInstitute): RedirectResponse
    {
        if ($subInstitute->sections()->exists()) {
            return back()->with('error', 'Cannot delete a sub-institute that has sections. Remove them first.');
        }

        if (Member::query()->where('sub_institute', $subInstitute->name)->exists()) {
            return back()->with('error', 'Cannot delete a sub-institute that is assigned to members.');
        }

        $subInstitute->delete();

        return redirect()->route('admin.sub-institutes.index')->with('success', 'Sub-institute deleted successfully.');
    }

    public function importForm(): View
    {
        $institutes = Institute::query()->where('is_active', true)->orderBy('name')->get();

        return view('admin.sub-institutes.import', compact('institutes'));
    }

    public function importTemplate(): StreamedResponse
    {
        return response()->streamDownload(function (): void {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['institute', 'name', 'is_active']);
            fputcsv($out, ['Department of Agriculture', 'Fruit Research Institute', '1']);
            fclose($out);
        }, 'asda-sub-institutes-import-template.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function import(Request $request): RedirectResponse
    {
        $request->validate([
            'csv_file' => ['required', 'file', 'mimes:csv,txt', 'max:2048'],
        ]);

        $institutes = Institute::query()
            ->get()
            ->keyBy(fn (Institute $i) => Str::lower(trim($i->name)));

        $existing = SubInstitute::query()
            ->get()
            ->keyBy(fn (SubInstitute $s) => $s->institute_id.'|'.Str::lower(trim($s->name)));

        $report = (new LookupCsvImporter)->import(
            $request->file('csv_file'),
            ['institute', 'name'],
            [
                'institute' => 'institute',
                'institute_name' => 'institute',
                'name' => 'name',
                'sub_institute' => 'name',
                'sub_institute_name' => 'name',
                'is_active' => 'is_active',
                'active' => 'is_active',
                'status' => 'is_active',
            ],
            function (array $data) use ($institutes, $existing): ?string {
                $instituteName = trim($data['institute'] ?? '');
                $name = trim($data['name'] ?? '');

                if ($instituteName === '') {
                    return 'Institute is required.';
                }
                if ($name === '') {
                    return 'Sub-institute name is required.';
                }

                $institute = $institutes->get(Str::lower($instituteName));
                if (! $institute) {
                    return 'Unknown institute: '.$instituteName.'. Add the institute first.';
                }

                $key = $institute->id.'|'.Str::lower($name);
                $active = LookupCsvImporter::parseActive($data['is_active'] ?? null, true);

                if ($existing->has($key)) {
                    $existing->get($key)->update(['is_active' => $active]);

                    return null;
                }

                $item = SubInstitute::create([
                    'institute_id' => $institute->id,
                    'name' => $name,
                    'is_active' => $active,
                ]);
                $existing->put($key, $item);

                return null;
            }
        );

        $message = "Import finished: {$report['imported']} sub-institute(s) saved, {$report['failed']} failed.";

        return redirect()
            ->route('admin.sub-institutes.import')
            ->with($report['imported'] > 0 ? 'success' : 'error', $message)
            ->with('import_report', $report);
    }
}
