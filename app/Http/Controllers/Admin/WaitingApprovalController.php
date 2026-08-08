<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Support\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WaitingApprovalController extends Controller
{
    public function index(Request $request): View
    {
        $query = Member::query()
            ->with('designation')
            ->where('registration_status', 'pending')
            ->latest();

        if ($search = $request->string('search')->trim()->toString()) {
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                    ->orWhere('nic', 'like', "%{$search}%")
                    ->orWhere('unique_id', 'like', "%{$search}%")
                    ->orWhere('mobile_1', 'like', "%{$search}%");
            });
        }

        $members = $query->paginate(25)->withQueryString();
        $pendingCount = Member::where('registration_status', 'pending')->count();

        return view('admin.waiting-approvals.index', compact('members', 'pendingCount'));
    }

    public function bulk(Request $request, MemberController $members): RedirectResponse
    {
        $data = $request->validate([
            'action' => ['required', 'in:approve,reject'],
            'member_ids' => ['required', 'array', 'min:1'],
            'member_ids.*' => ['integer', 'exists:members,id'],
            'rejection_reason' => ['nullable', 'string', 'max:1000'],
        ]);

        $pending = Member::query()
            ->where('registration_status', 'pending')
            ->whereIn('id', $data['member_ids'])
            ->get();

        $count = 0;

        foreach ($pending as $member) {
            $done = match ($data['action']) {
                'approve' => $members->applyApprove($member),
                'reject' => $members->applyReject(
                    $member,
                    $data['rejection_reason'] ?? 'Bulk rejected from waiting approvals.'
                ),
            };

            if ($done) {
                $count++;
            }
        }

        $label = $data['action'] === 'approve' ? 'approved' : 'rejected';

        ActivityLogger::log(
            $data['action'] === 'approve' ? 'approved' : 'rejected',
            'Bulk '.$label.' '.$count.' pending member(s)',
            properties: [
                'action' => $data['action'],
                'count' => $count,
                'member_ids' => $data['member_ids'],
            ],
        );

        return back()->with('success', "Bulk action complete: {$count} member(s) {$label}.");
    }
}
