<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Designation;
use App\Models\Event;
use App\Models\Member;
use App\Models\MemberCategory;
use App\Support\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class EventInviteController extends Controller
{
    public function edit(Request $request, Event $event): View
    {
        $invitedIds = $event->invitedMembers()->pluck('members.id')->map(fn ($id) => (int) $id)->all();

        $membersQuery = Member::query()
            ->with(['designation:id,name', 'category:id,name'])
            ->where('registration_status', 'approved')
            ->where('status', 'active')
            ->orderBy('full_name');

        if ($search = trim((string) $request->input('search'))) {
            $membersQuery->where(function ($query) use ($search) {
                $query->where('full_name', 'like', '%'.$search.'%')
                    ->orWhere('nic', 'like', '%'.$search.'%')
                    ->orWhere('unique_id', 'like', '%'.$search.'%')
                    ->orWhere('institute', 'like', '%'.$search.'%');
            });
        }

        if ($request->filled('designation_id')) {
            $membersQuery->where('designation_id', $request->integer('designation_id'));
        }

        if ($request->filled('category_id')) {
            $membersQuery->where('member_category_id', $request->integer('category_id'));
        }

        if ($request->boolean('invited_only')) {
            $membersQuery->whereIn('id', $invitedIds ?: [0]);
        }

        $members = $membersQuery->paginate(40)->withQueryString();

        $designations = Designation::query()->orderBy('name')->get(['id', 'name']);
        $categories = MemberCategory::query()->orderBy('name')->get(['id', 'name']);
        $invitedCount = count($invitedIds);

        return view('admin.events.invites', compact(
            'event',
            'members',
            'invitedIds',
            'invitedCount',
            'designations',
            'categories',
        ));
    }

    public function update(Request $request, Event $event): RedirectResponse
    {
        $data = $request->validate([
            'action' => ['required', Rule::in(['invite', 'remove'])],
            'member_ids' => ['required', 'array', 'min:1'],
            'member_ids.*' => [
                'integer',
                Rule::exists('members', 'id')
                    ->where('registration_status', 'approved')
                    ->where('status', 'active'),
            ],
        ]);

        $memberIds = collect($data['member_ids'])
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        if ($data['action'] === 'invite') {
            $existing = $event->invitedMembers()->pluck('members.id')->map(fn ($id) => (int) $id)->all();
            $toAttach = array_values(array_diff($memberIds, $existing));
            $payload = [];
            $now = now();
            $userId = Auth::guard('web')->id();

            foreach ($toAttach as $memberId) {
                $payload[$memberId] = [
                    'invited_by' => $userId,
                    'invited_at' => $now,
                ];
            }

            if ($payload !== []) {
                $event->invitedMembers()->attach($payload);
            }

            ActivityLogger::log(
                'updated',
                'Invited '.count($toAttach).' member(s) to '.$event->name,
                subject: $event,
                properties: [
                    'action' => 'invite',
                    'count' => count($toAttach),
                    'member_ids' => $toAttach,
                ],
            );

            $message = count($toAttach) > 0
                ? count($toAttach).' member(s) invited. They can now see and register for this event.'
                : 'Selected members were already invited.';
        } else {
            $event->invitedMembers()->detach($memberIds);

            ActivityLogger::log(
                'updated',
                'Removed invite for '.count($memberIds).' member(s) from '.$event->name,
                subject: $event,
                properties: [
                    'action' => 'remove',
                    'count' => count($memberIds),
                    'member_ids' => $memberIds,
                ],
            );

            $message = count($memberIds).' member invite(s) removed. They can no longer see this event.';
        }

        return redirect()
            ->route('admin.events.invites.edit', array_merge(['event' => $event], $request->only([
                'search',
                'designation_id',
                'category_id',
                'invited_only',
                'page',
            ])))
            ->with('success', $message);
    }

    public function invite(Request $request, Event $event, Member $member): RedirectResponse
    {
        if ($member->registration_status !== 'approved' || $member->status !== 'active') {
            return back()->with('error', 'Only active approved members can be invited.');
        }

        $alreadyInvited = $event->invitedMembers()->where('members.id', $member->id)->exists();

        if (! $alreadyInvited) {
            $event->invitedMembers()->attach($member->id, [
                'invited_by' => Auth::guard('web')->id(),
                'invited_at' => now(),
            ]);

            ActivityLogger::log(
                'updated',
                'Invited '.$member->displayName().' to '.$event->name,
                subject: $event,
                properties: [
                    'action' => 'invite',
                    'member_id' => $member->id,
                ],
            );
        }

        return redirect()
            ->route('admin.events.invites.edit', array_merge(['event' => $event], $request->only([
                'search',
                'designation_id',
                'category_id',
                'invited_only',
                'page',
            ])))
            ->with('success', $alreadyInvited
                ? $member->displayName().' was already invited.'
                : $member->displayName().' invited. They can now see and register for this event.');
    }

    public function remove(Request $request, Event $event, Member $member): RedirectResponse
    {
        $event->invitedMembers()->detach($member->id);

        ActivityLogger::log(
            'updated',
            'Removed invite for '.$member->displayName().' from '.$event->name,
            subject: $event,
            properties: [
                'action' => 'remove',
                'member_id' => $member->id,
            ],
        );

        return redirect()
            ->route('admin.events.invites.edit', array_merge(['event' => $event], $request->only([
                'search',
                'designation_id',
                'category_id',
                'invited_only',
                'page',
            ])))
            ->with('success', 'Invite removed for '.$member->displayName().'. They can no longer see this event.');
    }
}
