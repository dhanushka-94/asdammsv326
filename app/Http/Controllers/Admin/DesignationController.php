<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Designation;
use App\Support\LookupCsvImporter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DesignationController extends Controller
{
    public function index(): View
    {
        $designations = Designation::query()
            ->withCount('members')
            ->orderBy('name')
            ->paginate(15);

        return view('admin.designations.index', compact('designations'));
    }

    public function create(): View
    {
        return view('admin.designations.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:designations,name'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $data['is_active'] = $request->boolean('is_active', true);

        Designation::create($data);

        return redirect()->route('admin.designations.index')->with('success', 'Designation created successfully.');
    }

    public function edit(Designation $designation): View
    {
        return view('admin.designations.edit', compact('designation'));
    }

    public function update(Request $request, Designation $designation): RedirectResponse
    {
        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('designations', 'name')->ignore($designation->id),
            ],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $data['is_active'] = $request->boolean('is_active');

        $designation->update($data);

        return redirect()->route('admin.designations.index')->with('success', 'Designation updated successfully.');
    }

    public function destroy(Designation $designation): RedirectResponse
    {
        if ($designation->members()->exists()) {
            return back()->with('error', 'Cannot delete a designation that is assigned to members.');
        }

        $designation->delete();

        return redirect()->route('admin.designations.index')->with('success', 'Designation deleted successfully.');
    }

    public function importForm(): View
    {
        return view('admin.designations.import');
    }

    public function importTemplate(): StreamedResponse
    {
        return response()->streamDownload(function (): void {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['name', 'is_active']);
            fputcsv($out, ['Agricultural Officer', '1']);
            fclose($out);
        }, 'asda-designations-import-template.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function import(Request $request): RedirectResponse
    {
        $request->validate([
            'csv_file' => ['required', 'file', 'mimes:csv,txt', 'max:2048'],
        ]);

        $existing = Designation::query()
            ->get()
            ->keyBy(fn (Designation $d) => Str::lower(trim($d->name)));

        $report = (new LookupCsvImporter)->import(
            $request->file('csv_file'),
            ['name'],
            [
                'name' => 'name',
                'designation' => 'name',
                'designation_name' => 'name',
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

                $item = Designation::create([
                    'name' => $name,
                    'is_active' => $active,
                ]);
                $existing->put($key, $item);

                return null;
            }
        );

        return $this->importRedirect('admin.designations.import', $report, 'designation');
    }

    /**
     * @param  array{imported: int, skipped: int, failed: int, errors: list<array{row: int, message: string}>}  $report
     */
    private function importRedirect(string $route, array $report, string $label): RedirectResponse
    {
        $message = "Import finished: {$report['imported']} {$label}(s) saved, {$report['failed']} failed.";

        return redirect()
            ->route($route)
            ->with($report['imported'] > 0 ? 'success' : 'error', $message)
            ->with('import_report', $report);
    }
}
