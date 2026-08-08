<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventAttendance;
use App\Models\EventDay;
use App\Models\EventEnrollment;
use App\Models\EventVenue;
use App\Models\Member;
use App\Support\ActivityLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    public function index(): View|RedirectResponse
    {
        $user = Auth::guard('web')->user();

        if (! $user->canAccessAttendance()) {
            abort(403);
        }

        $eventsQuery = Event::query()
            ->with(['days' => fn ($q) => $q->orderBy('sort_order')])
            ->withCount(['activeEnrollments', 'days'])
            ->orderByDesc('start_date');

        if ($user->isReception()) {
            $eventsQuery->whereHas('receptionUsers', fn ($q) => $q->where('users.id', $user->id));
        }

        $events = $eventsQuery->get();

        return view('admin.attendance.index', compact('events'));
    }

    public function desk(Request $request, Event $event): View|RedirectResponse
    {
        $user = Auth::guard('web')->user();
        $this->ensureCanAccessEvent($user, $event);

        $event->load([
            'days' => fn ($q) => $q->orderBy('sort_order')->orderBy('day_number'),
            'venues' => fn ($q) => $q->orderBy('sort_order'),
        ]);

        if ($event->days->isEmpty()) {
            return redirect()
                ->route('admin.attendance.index')
                ->with('error', 'Add at least one event day before using the attendance desk for “'.$event->name.'”.');
        }

        $selectedDayId = (int) $request->input('day', $event->days->first()->id);
        $day = $event->days->firstWhere('id', $selectedDayId) ?? $event->days->first();

        $selectedVenueId = $request->filled('venue')
            ? (int) $request->input('venue')
            : ($event->venues->first()?->id);

        $venue = $selectedVenueId
            ? $event->venues->firstWhere('id', $selectedVenueId)
            : null;

        $checkedInQuery = EventAttendance::query()
            ->where('event_id', $event->id)
            ->where('event_day_id', $day->id)
            ->with(['member.designation', 'checkedInBy', 'venue']);

        $checkedInTotal = (clone $checkedInQuery)->count();

        $search = trim((string) $request->input('search', ''));
        if ($search !== '') {
            $like = '%'.$search.'%';
            $digits = preg_replace('/\D+/', '', $search) ?? '';

            $checkedInQuery->where(function ($query) use ($like, $digits) {
                $query->whereHas('member', function ($memberQuery) use ($like, $digits) {
                    $memberQuery->where('full_name', 'like', $like)
                        ->orWhere('unique_id', 'like', $like)
                        ->orWhere('nic', 'like', $like)
                        ->orWhereRaw("CONCAT(COALESCE(title, ''), ' ', full_name) like ?", [$like]);

                    if ($digits !== '') {
                        $memberQuery->orWhere('mobile_1', 'like', '%'.$digits.'%')
                            ->orWhere('mobile_2', 'like', '%'.$digits.'%')
                            ->orWhere('whatsapp', 'like', '%'.$digits.'%');
                    }
                })
                    ->orWhereHas('checkedInBy', function ($officerQuery) use ($like) {
                        $officerQuery->where('name', 'like', $like)
                            ->orWhere('email', 'like', $like);
                    })
                    ->orWhereHas('venue', function ($venueQuery) use ($like) {
                        $venueQuery->where('name', 'like', $like)
                            ->orWhere('floor', 'like', $like)
                            ->orWhere('hall_room', 'like', $like)
                            ->orWhere('description', 'like', $like);
                    });
            });
        }

        $checkedIn = $checkedInQuery
            ->latest('checked_in_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.attendance.desk', [
            'event' => $event,
            'day' => $day,
            'venue' => $venue,
            'checkedIn' => $checkedIn,
            'checkedInTotal' => $checkedInTotal,
            'checkedInSearch' => $search,
        ]);
    }

    public function lookup(Request $request, Event $event): JsonResponse
    {
        $user = Auth::guard('web')->user();
        $this->ensureCanAccessEvent($user, $event);

        $data = $request->validate([
            'event_day_id' => ['required', 'integer'],
            'member_id' => ['nullable', 'integer', 'exists:members,id'],
            'code' => ['nullable', 'string', 'max:64'],
            'q' => ['nullable', 'string', 'max:120'],
        ]);

        $day = $this->resolveDay($event, (int) $data['event_day_id']);

        if (! empty($data['member_id'])) {
            $member = Member::query()
                ->with(['designation', 'category'])
                ->find($data['member_id']);

            if (! $member) {
                return response()->json([
                    'ok' => false,
                    'status' => 'not_found',
                    'message' => 'No member found.',
                ]);
            }

            return response()->json($this->buildMemberLookupPayload($event, $day, $member));
        }

        $term = trim((string) ($data['q'] ?? $data['code'] ?? ''));
        if ($term === '') {
            return response()->json([
                'ok' => false,
                'status' => 'invalid',
                'message' => 'Enter a unique ID, NIC, name, or mobile number.',
            ], 422);
        }

        $members = $this->searchMembers($term);

        if ($members->isEmpty()) {
            return response()->json([
                'ok' => false,
                'status' => 'not_found',
                'message' => 'No member found for that search.',
            ]);
        }

        if ($members->count() > 1) {
            return response()->json([
                'ok' => false,
                'status' => 'multiple',
                'message' => 'Multiple members found. Select one to continue.',
                'matches' => $members->map(fn (Member $member) => $this->memberPayload($member))->values()->all(),
            ]);
        }

        return response()->json(
            $this->buildMemberLookupPayload($event, $day, $members->first())
        );
    }

    public function checkIn(Request $request, Event $event): JsonResponse
    {
        $user = Auth::guard('web')->user();
        $this->ensureCanAccessEvent($user, $event);

        $event->loadMissing('venues');
        $requiresVenue = $event->venues->isNotEmpty();

        $data = $request->validate([
            'member_id' => ['required', 'integer', 'exists:members,id'],
            'event_day_id' => ['required', 'integer'],
            'event_venue_id' => [
                Rule::requiredIf($requiresVenue),
                'nullable',
                'integer',
            ],
        ]);

        $day = $this->resolveDay($event, (int) $data['event_day_id']);
        $venue = null;

        if ($requiresVenue) {
            $venue = $this->resolveVenue($event, (int) $data['event_venue_id']);
        }

        $member = Member::query()->findOrFail($data['member_id']);

        $enrollment = EventEnrollment::query()
            ->active()
            ->where('event_id', $event->id)
            ->where('member_id', $member->id)
            ->first();

        if (! $enrollment) {
            return response()->json([
                'ok' => false,
                'status' => 'not_enrolled',
                'message' => 'Member is not registered for this event.',
            ], 422);
        }

        $existing = EventAttendance::query()
            ->where('event_day_id', $day->id)
            ->where('member_id', $member->id)
            ->first();

        if ($existing) {
            return response()->json([
                'ok' => false,
                'status' => 'already_checked_in',
                'message' => 'Already checked in for '.$day->dayLabel().'.',
            ], 422);
        }

        $attendance = EventAttendance::query()->create([
            'event_id' => $event->id,
            'event_day_id' => $day->id,
            'event_venue_id' => $venue?->id,
            'member_id' => $member->id,
            'event_enrollment_id' => $enrollment->id,
            'checked_in_at' => now(),
            'checked_in_by' => $user->id,
        ]);

        ActivityLogger::log(
            'created',
            'Checked in '.$member->displayName().' for '.$event->name.' ('.$day->dayLabel().')'
                .($venue ? ' at '.$venue->locationSummary() : ''),
            subject: $member,
            guard: 'web',
            causer: $user,
            properties: [
                'event_id' => $event->id,
                'event_day_id' => $day->id,
                'event_venue_id' => $venue?->id,
                'attendance_id' => $attendance->id,
            ],
        );

        return response()->json([
            'ok' => true,
            'status' => 'checked_in',
            'message' => $member->displayName().' checked in for '.$day->dayLabel()
                .($venue ? ' · '.$venue->locationSummary() : '').'.',
            'attendance' => [
                'id' => $attendance->id,
                'checked_in_at' => $attendance->checked_in_at?->toIso8601String(),
                'member_name' => $member->displayName(),
                'unique_id' => $member->unique_id,
                'venue' => $venue?->locationSummary(),
                'officer' => $user->name,
            ],
        ]);
    }

    private function ensureCanAccessEvent($user, Event $event): void
    {
        if (! $user->canAccessAttendance() || ! $user->canAccessEventAttendance($event)) {
            abort(403, 'You do not have attendance access for this event.');
        }
    }

    private function resolveDay(Event $event, int $dayId): EventDay
    {
        $day = EventDay::query()
            ->where('event_id', $event->id)
            ->where('id', $dayId)
            ->first();

        if (! $day) {
            abort(422, 'Invalid event day.');
        }

        return $day;
    }

    private function resolveVenue(Event $event, int $venueId): EventVenue
    {
        $venue = EventVenue::query()
            ->where('event_id', $event->id)
            ->where('id', $venueId)
            ->first();

        if (! $venue) {
            abort(422, 'Invalid venue.');
        }

        return $venue;
    }

    /**
     * @return \Illuminate\Support\Collection<int, Member>
     */
    private function searchMembers(string $term)
    {
        $normalized = strtoupper(preg_replace('/[\s\-]/', '', $term) ?? $term);
        $like = '%'.$term.'%';
        $digits = preg_replace('/\D+/', '', $term) ?? '';

        return Member::query()
            ->with(['designation', 'category'])
            ->where(function ($query) use ($term, $normalized, $like, $digits) {
                $query->where('unique_id', $normalized)
                    ->orWhere('nic', $normalized)
                    ->orWhere('nic', 'like', $like)
                    ->orWhere('unique_id', 'like', '%'.$normalized.'%')
                    ->orWhere('full_name', 'like', $like)
                    ->orWhereRaw("CONCAT(COALESCE(title, ''), ' ', full_name) like ?", [$like]);

                if ($digits !== '') {
                    $query->orWhere('mobile_1', 'like', '%'.$digits.'%')
                        ->orWhere('mobile_2', 'like', '%'.$digits.'%')
                        ->orWhere('whatsapp', 'like', '%'.$digits.'%');
                }
            })
            ->orderBy('full_name')
            ->limit(12)
            ->get();
    }

    /**
     * @return array<string, mixed>
     */
    private function buildMemberLookupPayload(Event $event, EventDay $day, Member $member): array
    {
        $enrollment = EventEnrollment::query()
            ->active()
            ->where('event_id', $event->id)
            ->where('member_id', $member->id)
            ->first();

        if (! $enrollment) {
            return [
                'ok' => false,
                'status' => 'not_enrolled',
                'message' => 'Member is not registered for this event.',
                'member' => $this->memberPayload($member),
                'can_check_in' => false,
            ];
        }

        $existing = EventAttendance::query()
            ->where('event_day_id', $day->id)
            ->where('member_id', $member->id)
            ->with(['checkedInBy:id,name', 'venue'])
            ->first();

        if ($existing) {
            return [
                'ok' => false,
                'status' => 'already_checked_in',
                'message' => 'Already checked in for '.$day->dayLabel().'.',
                'member' => $this->memberPayload($member),
                'enrollment' => [
                    'participation_mode' => $enrollment->participationModeLabel(),
                    'enrolled_at' => $enrollment->enrolled_at?->toIso8601String(),
                ],
                'attendance' => [
                    'checked_in_at' => $existing->checked_in_at?->toIso8601String(),
                    'checked_in_by' => $existing->checkedInBy?->name,
                    'venue' => $existing->venue?->locationSummary(),
                ],
                'can_check_in' => false,
            ];
        }

        return [
            'ok' => true,
            'status' => 'ready',
            'message' => 'Member found. Select venue (if required) and confirm check-in for '.$day->dayLabel().'.',
            'member' => $this->memberPayload($member),
            'enrollment' => [
                'id' => $enrollment->id,
                'participation_mode' => $enrollment->participationModeLabel(),
                'enrolled_at' => $enrollment->enrolled_at?->toIso8601String(),
            ],
            'can_check_in' => true,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function memberPayload(Member $member): array
    {
        return [
            'id' => $member->id,
            'name' => $member->displayName(),
            'unique_id' => $member->unique_id,
            'nic' => $member->nic,
            'mobile' => $member->mobile_1,
            'institute' => $member->institute,
            'designation' => $member->designation?->name,
            'category' => $member->category?->name,
            'photo_url' => $member->profileImageUrl(),
            'registration_status' => $member->registration_status,
            'status' => $member->status,
        ];
    }
}
