<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Institute;
use App\Models\Member;
use App\Models\Section;
use App\Models\SubInstitute;
use App\Support\LookupCsvImporter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SectionController extends Controller
{
    public function index(Request $request): View
    {
        $instituteId = $request->integer('institute_id') ?: null;
        $subInstituteId = $request->integer('sub_institute_id') ?: null;

        $sections = Section::query()
            ->with(['subInstitute.institute'])
            ->when($subInstituteId, fn ($q) => $q->where('sub_institute_id', $subInstituteId))
            ->when($instituteId && ! $subInstituteId, function ($q) use ($instituteId) {
                $q->whereHas('subInstitute', fn ($sq) => $sq->where('institute_id', $instituteId));
            })
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        $institutes = Institute::query()->orderBy('name')->get();
        $subInstitutes = SubInstitute::query()
            ->when($instituteId, fn ($q) => $q->where('institute_id', $instituteId))
            ->orderBy('name')
            ->get();

        return view('admin.sections.index', compact('sections', 'institutes', 'subInstitutes', 'instituteId', 'subInstituteId'));
    }

    public function create(Request $request): View
    {
        $institutes = Institute::query()->where('is_active', true)->orderBy('name')->get();
        $subInstitutes = SubInstitute::query()
            ->with('institute')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('admin.sections.create', [
            'institutes' => $institutes,
            'subInstitutes' => $subInstitutes,
            'selectedSubInstituteId' => old('sub_institute_id', $request->integer('sub_institute_id') ?: null),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'sub_institute_id' => ['required', 'exists:sub_institutes,id'],
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('sections', 'name')->where(fn ($q) => $q->where('sub_institute_id', $request->input('sub_institute_id'))),
            ],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $data['is_active'] = $request->boolean('is_active', true);

        Section::create($data);

        return redirect()->route('admin.sections.index')->with('success', 'Section created successfully.');
    }

    public function edit(Section $section): View
    {
        $section->load('subInstitute.institute');
        $institutes = Institute::query()->orderBy('name')->get();
        $subInstitutes = SubInstitute::query()->with('institute')->orderBy('name')->get();

        return view('admin.sections.edit', compact('section', 'institutes', 'subInstitutes'));
    }

    public function update(Request $request, Section $section): RedirectResponse
    {
        $data = $request->validate([
            'sub_institute_id' => ['required', 'exists:sub_institutes,id'],
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('sections', 'name')
                    ->where(fn ($q) => $q->where('sub_institute_id', $request->input('sub_institute_id')))
                    ->ignore($section->id),
            ],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $oldName = $section->name;
        $data['is_active'] = $request->boolean('is_active');
        $section->update($data);

        if ($oldName !== $section->name) {
            Member::query()->where('section', $oldName)->update(['section' => $section->name]);
        }

        return redirect()->route('admin.sections.index')->with('success', 'Section updated successfully.');
    }

    public function destroy(Section $section): RedirectResponse
    {
        if (Member::query()->where('section', $section->name)->exists()) {
            return back()->with('error', 'Cannot delete a section that is assigned to members.');
        }

        $section->delete();

        return redirect()->route('admin.sections.index')->with('success', 'Section deleted successfully.');
    }

    public function importForm(): View
    {
        return view('admin.sections.import');
    }

    public function importTemplate(): StreamedResponse
    {
        return response()->streamDownload(function (): void {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['institute', 'sub_institute', 'name', 'is_active']);
            fputcsv($out, ['Department of Agriculture', 'Fruit Research Institute', 'Plant Pathology', '1']);
            fclose($out);
        }, 'asda-sections-import-template.csv', [
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

        $subInstitutes = SubInstitute::query()
            ->get()
            ->keyBy(fn (SubInstitute $s) => $s->institute_id.'|'.Str::lower(trim($s->name)));

        $existing = Section::query()
            ->get()
            ->keyBy(fn (Section $s) => $s->sub_institute_id.'|'.Str::lower(trim($s->name)));

        $report = (new LookupCsvImporter)->import(
            $request->file('csv_file'),
            ['institute', 'sub_institute', 'name'],
            [
                'institute' => 'institute',
                'institute_name' => 'institute',
                'sub_institute' => 'sub_institute',
                'sub_institute_name' => 'sub_institute',
                'name' => 'name',
                'section' => 'name',
                'section_name' => 'name',
                'is_active' => 'is_active',
                'active' => 'is_active',
                'status' => 'is_active',
            ],
            function (array $data) use ($institutes, $subInstitutes, $existing): ?string {
                $instituteName = trim($data['institute'] ?? '');
                $subName = trim($data['sub_institute'] ?? '');
                $name = trim($data['name'] ?? '');

                if ($instituteName === '') {
                    return 'Institute is required.';
                }
                if ($subName === '') {
                    return 'Sub-institute is required.';
                }
                if ($name === '') {
                    return 'Section name is required.';
                }

                $institute = $institutes->get(Str::lower($instituteName));
                if (! $institute) {
                    return 'Unknown institute: '.$instituteName.'.';
                }

                $sub = $subInstitutes->get($institute->id.'|'.Str::lower($subName));
                if (! $sub) {
                    return 'Unknown sub-institute: '.$subName.' under '.$instituteName.'.';
                }

                $key = $sub->id.'|'.Str::lower($name);
                $active = LookupCsvImporter::parseActive($data['is_active'] ?? null, true);

                if ($existing->has($key)) {
                    $existing->get($key)->update(['is_active' => $active]);

                    return null;
                }

                $item = Section::create([
                    'sub_institute_id' => $sub->id,
                    'name' => $name,
                    'is_active' => $active,
                ]);
                $existing->put($key, $item);

                return null;
            }
        );

        $message = "Import finished: {$report['imported']} section(s) saved, {$report['failed']} failed.";

        return redirect()
            ->route('admin.sections.import')
            ->with($report['imported'] > 0 ? 'success' : 'error', $message)
            ->with('import_report', $report);
    }
}
