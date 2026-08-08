<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MemberCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MemberCategoryController extends Controller
{
    public function index(): View
    {
        $categories = MemberCategory::query()
            ->withCount('members')
            ->orderBy('name')
            ->paginate(15);

        return view('admin.member-categories.index', compact('categories'));
    }

    public function create(): View
    {
        return view('admin.member-categories.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:member_categories,name'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $data['is_active'] = $request->boolean('is_active', true);

        MemberCategory::create($data);

        return redirect()->route('admin.member-categories.index')->with('success', 'Category created successfully.');
    }

    public function edit(MemberCategory $memberCategory): View
    {
        return view('admin.member-categories.edit', compact('memberCategory'));
    }

    public function update(Request $request, MemberCategory $memberCategory): RedirectResponse
    {
        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('member_categories', 'name')->ignore($memberCategory->id),
            ],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $data['is_active'] = $request->boolean('is_active');

        $memberCategory->update($data);

        return redirect()->route('admin.member-categories.index')->with('success', 'Category updated successfully.');
    }

    public function destroy(MemberCategory $memberCategory): RedirectResponse
    {
        if ($memberCategory->members()->exists()) {
            return back()->with('error', 'Cannot delete a category that is assigned to members.');
        }

        $memberCategory->delete();

        return redirect()->route('admin.member-categories.index')->with('success', 'Category deleted successfully.');
    }
}
