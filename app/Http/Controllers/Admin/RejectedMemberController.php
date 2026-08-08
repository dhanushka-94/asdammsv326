<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Support\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RejectedMemberController extends Controller
{
    public function index(Request $request): View
    {
        $query = Member::query()
            ->with(['designation', 'approver'])
            ->where('registration_status', 'rejected')
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
        $rejectedCount = Member::where('registration_status', 'rejected')->count();

        return view('admin.rejected-members.index', compact('members', 'rejectedCount'));
    }

    public function bulk(Request $request, MemberController $members): RedirectResponse
    {
        $data = $request->validate([
            'action' => ['required', 'in:reaccept'],
            'member_ids' => ['required', 'array', 'min:1'],
            'member_ids.*' => ['integer', 'exists:members,id'],
        ]);

        $rejected = Member::query()
            ->where('registration_status', 'rejected')
            ->whereIn('id', $data['member_ids'])
            ->get();

        $count = 0;

        foreach ($rejected as $member) {
            if ($members->applyApprove($member)) {
                $count++;
            }
        }

        ActivityLogger::log(
            'approved',
            'Bulk re-accepted '.$count.' rejected member(s)',
            properties: [
                'action' => 'reaccept',
                'count' => $count,
                'member_ids' => $data['member_ids'],
            ],
        );

        return back()->with('success', "Bulk action complete: {$count} member(s) re-accepted.");
    }
}
