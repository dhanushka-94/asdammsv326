<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventAttendance;
use App\Models\Member;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CheckedInMemberController extends Controller
{
    public function index(Request $request): View
    {
        $user = Auth::guard('web')->user();

        if (! $user->canAccessAttendance()) {
            abort(403);
        }

        $eventsQuery = Event::query()->orderByDesc('start_date');
        if ($user->isReception()) {
            $eventsQuery->whereHas('receptionUsers', fn ($q) => $q->where('users.id', $user->id));
        }
        $events = $eventsQuery->get();
        $allowedEventIds = $events->pluck('id');

        $selectedEventId = $request->filled('event') ? $request->integer('event') : null;
        if ($selectedEventId && ! $allowedEventIds->contains($selectedEventId)) {
            abort(403);
        }

        $query = EventAttendance::query()
            ->with([
                'member.designation',
                'member.category',
                'event',
                'day',
                'venue',
                'checkedInBy',
                'checkInItems',
            ])
            ->when(
                $user->isReception(),
                fn (Builder $q) => $q->whereIn('event_id', $allowedEventIds)
            )
            ->when(
                $selectedEventId,
                fn (Builder $q) => $q->where('event_id', $selectedEventId)
            );

        if ($search = $request->string('search')->trim()->toString()) {
            $like = '%'.$search.'%';
            $digits = preg_replace('/\D+/', '', $search) ?: null;

            $query->where(function (Builder $q) use ($like, $digits, $search) {
                $q->whereHas('member', function (Builder $memberQuery) use ($like, $digits) {
                    $memberQuery->where('full_name', 'like', $like)
                        ->orWhere('unique_id', 'like', $like)
                        ->orWhere('nic', 'like', $like)
                        ->orWhere('email', 'like', $like)
                        ->orWhere('mobile_1', 'like', $like)
                        ->orWhere('mobile_2', 'like', $like)
                        ->orWhereRaw("CONCAT(COALESCE(title, ''), ' ', full_name) like ?", [$like]);

                    if ($digits) {
                        $memberQuery->orWhereRaw("REPLACE(REPLACE(REPLACE(mobile_1, ' ', ''), '-', ''), '+', '') like ?", ['%'.$digits.'%'])
                            ->orWhereRaw("REPLACE(REPLACE(REPLACE(COALESCE(whatsapp, ''), ' ', ''), '-', ''), '+', '') like ?", ['%'.$digits.'%']);
                    }
                })
                    ->orWhereHas('event', fn (Builder $eq) => $eq->where('name', 'like', $like))
                    ->orWhereHas('venue', fn (Builder $vq) => $vq->where('name', 'like', $like))
                    ->orWhereHas('checkedInBy', fn (Builder $uq) => $uq->where('name', 'like', $like))
                    ->orWhereHas('checkInItems', fn (Builder $iq) => $iq->where('name', 'like', $like));

                if (is_numeric($search)) {
                    $q->orWhereHas('day', fn (Builder $dq) => $dq->where('day_number', (int) $search));
                }
            });
        }

        $checkIns = $query
            ->latest('checked_in_at')
            ->paginate(20)
            ->withQueryString();

        $scoped = EventAttendance::query()
            ->when(
                $user->isReception(),
                fn (Builder $q) => $q->whereIn('event_id', $allowedEventIds)
            )
            ->when(
                $selectedEventId,
                fn (Builder $q) => $q->where('event_id', $selectedEventId)
            );

        $totalCheckIns = (clone $scoped)->count();
        $uniqueMembers = (clone $scoped)->select('member_id')->distinct()->count('member_id');

        return view('admin.checked-in.index', compact(
            'checkIns',
            'events',
            'selectedEventId',
            'totalCheckIns',
            'uniqueMembers',
        ));
    }

    public function show(Member $member): View
    {
        $user = Auth::guard('web')->user();

        if (! $user->canAccessAttendance()) {
            abort(403);
        }

        if ($user->isReception()) {
            $allowedEventIds = $user->receptionEvents()->pluck('events.id');

            $visible = EventAttendance::query()
                ->where('member_id', $member->id)
                ->whereIn('event_id', $allowedEventIds)
                ->exists();

            if (! $visible) {
                abort(403, 'You can only view members who checked in at your assigned events.');
            }
        }

        $member->load([
            'designation',
            'category',
            'activeEventEnrollments.event',
            'eventEnrollments' => fn ($q) => $q->whereNotNull('kicked_at')->latest('kicked_at')->with(['event', 'kickedBy']),
        ]);

        $attendances = EventAttendance::query()
            ->with(['event', 'day', 'venue', 'checkedInBy', 'checkInItems', 'enrollment'])
            ->where('member_id', $member->id)
            ->latest('checked_in_at')
            ->get();

        return view('admin.checked-in.show', [
            'member' => $member,
            'qrUrl' => $member->qrCodeUrl(),
            'attendances' => $attendances,
            'enrollments' => $member->activeEventEnrollments,
            'removedEnrollments' => $member->eventEnrollments,
        ]);
    }
}
