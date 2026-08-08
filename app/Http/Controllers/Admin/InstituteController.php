<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Institute;
use App\Models\Member;
use App\Support\LookupCsvImporter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class InstituteController extends Controller
{
    public function index(): View
    {
        $institutes = Institute::query()
            ->withCount('subInstitutes')
            ->orderBy('name')
            ->paginate(20);

        return view('admin.institutes.index', compact('institutes'));
    }

    public function create(): View
    {
        return view('admin.institutes.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:institutes,name'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $data['is_active'] = $request->boolean('is_active', true);

        Institute::create($data);

        return redirect()->route('admin.institutes.index')->with('success', 'Institute created successfully.');
    }

    public function edit(Institute $institute): View
    {
        return view('admin.institutes.edit', compact('institute'));
    }

    public function update(Request $request, Institute $institute): RedirectResponse
    {
        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('institutes', 'name')->ignore($institute->id),
            ],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $oldName = $institute->name;
        $data['is_active'] = $request->boolean('is_active');
        $institute->update($data);

        if ($oldName !== $institute->name) {
            Member::query()->where('institute', $oldName)->update(['institute' => $institute->name]);
        }

        return redirect()->route('admin.institutes.index')->with('success', 'Institute updated successfully.');
    }

    public function destroy(Institute $institute): RedirectResponse
    {
        if ($institute->subInstitutes()->exists()) {
            return back()->with('error', 'Cannot delete an institute that has sub-institutes. Remove them first.');
        }

        if (Member::query()->where('institute', $institute->name)->exists()) {
            return back()->with('error', 'Cannot delete an institute that is assigned to members.');
        }

        $institute->delete();

        return redirect()->route('admin.institutes.index')->with('success', 'Institute deleted successfully.');
    }

    public function importForm(): View
    {
        return view('admin.institutes.import');
    }

    public function importTemplate(): StreamedResponse
    {
        return response()->streamDownload(function (): void {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['name', 'is_active']);
            fputcsv($out, ['Department of Agriculture', '1']);
            fclose($out);
        }, 'asda-institutes-import-template.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function import(Request $request): RedirectResponse
    {
        $request->validate([
            'csv_file' => ['required', 'file', 'mimes:csv,txt', 'max:2048'],
        ]);

        $existing = Institute::query()
            ->get()
            ->keyBy(fn (Institute $i) => Str::lower(trim($i->name)));

        $report = (new LookupCsvImporter)->import(
            $request->file('csv_file'),
            ['name'],
            [
                'name' => 'name',
                'institute' => 'name',
                'institute_name' => 'name',
                'is_active' => 'is_active',
                'active' => 'is_active',
                'status' => 'is_active',
            ],
            function (array $data) use ($existing): ?string {
                $name = trim($data['name'] ?? '');
                if ($name === '') {
                    return 'Name is required.';
                }

                $key = Str::lower($name);
                $active = LookupCsvImporter::parseActive($data['is_active'] ?? null, true);

                if ($existing->has($key)) {
                    $existing->get($key)->update(['is_active' => $active]);

                    return null;
                }

                $item = Institute::create([
                    'name' => $name,
                    'is_active' => $active,
                ]);
                $existing->put($key, $item);

                return null;
            }
        );

        $message = "Import finished: {$report['imported']} institute(s) saved, {$report['failed']} failed.";

        return redirect()
            ->route('admin.institutes.import')
            ->with($report['imported'] > 0 ? 'success' : 'error', $message)
            ->with('import_report', $report);
    }
}
