<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CheckInItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CheckInItemController extends Controller
{
    public function index(): View
    {
        $items = CheckInItem::query()
            ->withCount('attendances')
            ->ordered()
            ->paginate(20);

        return view('admin.check-in-items.index', compact('items'));
    }

    public function create(): View
    {
        return view('admin.check-in-items.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:check_in_items,name'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $data['is_active'] = $request->boolean('is_active', true);
        $data['sort_order'] = (int) ($data['sort_order'] ?? ((CheckInItem::query()->max('sort_order') ?? 0) + 1));

        CheckInItem::query()->create($data);

        return redirect()
            ->route('admin.check-in-items.index')
            ->with('success', 'Check-in item created successfully.');
    }

    public function edit(CheckInItem $checkInItem): View
    {
        return view('admin.check-in-items.edit', compact('checkInItem'));
    }

    public function update(Request $request, CheckInItem $checkInItem): RedirectResponse
    {
        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('check_in_items', 'name')->ignore($checkInItem->id),
            ],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $data['is_active'] = $request->boolean('is_active');
        $data['sort_order'] = (int) ($data['sort_order'] ?? $checkInItem->sort_order);

        $checkInItem->update($data);

        return redirect()
            ->route('admin.check-in-items.index')
            ->with('success', 'Check-in item updated successfully.');
    }

    public function destroy(CheckInItem $checkInItem): RedirectResponse
    {
        if ($checkInItem->attendances()->exists()) {
            return back()->with('error', 'Cannot delete an item that was already given at check-in. Mark it inactive instead.');
        }

        $checkInItem->delete();

        return redirect()
            ->route('admin.check-in-items.index')
            ->with('success', 'Check-in item deleted successfully.');
    }
}
