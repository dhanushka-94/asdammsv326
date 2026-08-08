<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CheckInItem;
use App\Models\Event;
use App\Models\EventAttendance;
use App\Models\EventEnrollment;
use App\Models\Member;
use App\Models\MemberCategory;
use App\Models\Designation;
use App\Support\SriLankaDate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(): View
    {
        $this->ensureCanViewReports();

        $memberStats = [
            'total' => Member::query()->count(),
            'approved' => Member::query()->where('registration_status', 'approved')->count(),
            'pending' => Member::query()->where('registration_status', 'pending')->count(),
            'rejected' => Member::query()->where('registration_status', 'rejected')->count(),
            'active' => Member::query()->where('status', 'active')->count(),
        ];

        $eventStats = [
            'total' => Event::query()->count(),
            'active' => Event::query()->where('status', 'active')->count(),
            'enrollments' => EventEnrollment::query()->active()->count(),
            'check_ins' => EventAttendance::query()->count(),
        ];

        $registrationTrend = Member::query()
            ->selectRaw('DATE(created_at) as day, COUNT(*) as total')
            ->where('created_at', '>=', now()->subDays(29)->startOfDay())
            ->groupBy('day')
            ->orderBy('day')
            ->get();

        $trendLabels = [];
        $trendValues = [];
        $byDay = $registrationTrend->keyBy(fn ($row) => (string) $row->day);
        for ($i = 29; $i >= 0; $i--) {
            $day = now()->subDays($i)->toDateString();
            $trendLabels[] = SriLankaDate::format($day, 'd M');
            $trendValues[] = (int) ($byDay[$day]->total ?? 0);
        }

        $registrationBreakdown = [
            'labels' => ['Approved', 'Pending', 'Rejected'],
            'values' => [
                $memberStats['approved'],
                $memberStats['pending'],
                $memberStats['rejected'],
            ],
        ];

        $checkInsByEvent = EventAttendance::query()
            ->join('events', 'events.id', '=', 'event_attendances.event_id')
            ->selectRaw('events.name as label, COUNT(event_attendances.id) as total')
            ->groupBy('events.id', 'events.name')
            ->orderByDesc('total')
            ->limit(8)
            ->get();

        $topItems = CheckInItem::query()
            ->withCount('attendances')
            ->ordered()
            ->get()
            ->filter(fn (CheckInItem $item) => $item->attendances_count > 0)
            ->take(8)
            ->values();

        $charts = [
            'registration_trend' => [
                'labels' => $trendLabels,
                'values' => $trendValues,
            ],
            'registration_status' => $registrationBreakdown,
            'check_ins_by_event' => [
                'labels' => $checkInsByEvent->pluck('label')->all(),
                'values' => $checkInsByEvent->pluck('total')->map(fn ($v) => (int) $v)->all(),
            ],
            'items_given' => [
                'labels' => $topItems->pluck('name')->all(),
                'values' => $topItems->pluck('attendances_count')->map(fn ($v) => (int) $v)->all(),
            ],
        ];

        return view('admin.reports.index', compact('memberStats', 'eventStats', 'charts'));
    }

    public function members(): View
    {
        $this->ensureCanViewReports();

        $byCategory = Member::query()
            ->leftJoin('member_categories', 'member_categories.id', '=', 'members.member_category_id')
            ->selectRaw("COALESCE(member_categories.name, 'Unassigned') as label, COUNT(members.id) as total")
            ->groupByRaw("COALESCE(member_categories.name, 'Unassigned')")
            ->orderByDesc('total')
            ->get();

        $byDesignation = Member::query()
            ->leftJoin('designations', 'designations.id', '=', 'members.designation_id')
            ->selectRaw("COALESCE(designations.name, 'Unassigned') as label, COUNT(members.id) as total")
            ->groupByRaw("COALESCE(designations.name, 'Unassigned')")
            ->orderByDesc('total')
            ->limit(12)
            ->get();

        $byInstitute = Member::query()
            ->selectRaw("COALESCE(NULLIF(institute, ''), 'Unassigned') as label, COUNT(*) as total")
            ->groupByRaw("COALESCE(NULLIF(institute, ''), 'Unassigned')")
            ->orderByDesc('total')
            ->limit(12)
            ->get();

        $byStatus = Member::query()
            ->selectRaw('status as label, COUNT(*) as total')
            ->groupBy('status')
            ->orderByDesc('total')
            ->get();

        $byRegistration = Member::query()
            ->selectRaw('registration_status as label, COUNT(*) as total')
            ->groupBy('registration_status')
            ->orderByDesc('total')
            ->get();

        $monthly = Member::query()
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as total")
            ->where('created_at', '>=', now()->subMonths(11)->startOfMonth())
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->keyBy('month');

        $monthLabels = [];
        $monthValues = [];
        for ($i = 11; $i >= 0; $i--) {
            $key = now()->subMonths($i)->format('Y-m');
            $monthLabels[] = now()->subMonths($i)->format('M Y');
            $monthValues[] = (int) ($monthly[$key]->total ?? 0);
        }

        $summary = [
            'total' => Member::query()->count(),
            'approved_active' => Member::query()->where('registration_status', 'approved')->where('status', 'active')->count(),
            'categories' => MemberCategory::query()->count(),
            'designations' => Designation::query()->count(),
        ];

        $charts = [
            'by_category' => [
                'labels' => $byCategory->pluck('label')->all(),
                'values' => $byCategory->pluck('total')->map(fn ($v) => (int) $v)->all(),
            ],
            'by_designation' => [
                'labels' => $byDesignation->pluck('label')->all(),
                'values' => $byDesignation->pluck('total')->map(fn ($v) => (int) $v)->all(),
            ],
            'by_institute' => [
                'labels' => $byInstitute->pluck('label')->all(),
                'values' => $byInstitute->pluck('total')->map(fn ($v) => (int) $v)->all(),
            ],
            'monthly' => [
                'labels' => $monthLabels,
                'values' => $monthValues,
            ],
        ];

        return view('admin.reports.members', compact(
            'summary',
            'charts',
            'byCategory',
            'byDesignation',
            'byInstitute',
            'byStatus',
            'byRegistration',
        ));
    }

    public function attendance(Request $request): View
    {
        $this->ensureCanViewReports();

        $events = Event::query()
            ->withCount(['activeEnrollments', 'days'])
            ->orderByDesc('start_date')
            ->get();

        $selectedEventId = $request->integer('event') ?: ($events->first()?->id);
        $event = $selectedEventId
            ? Event::query()->with(['days' => fn ($q) => $q->orderBy('sort_order')->orderBy('day_number'), 'venues'])->find($selectedEventId)
            : null;

        $dayBreakdown = collect();
        $venueBreakdown = collect();
        $modeBreakdown = collect();
        $officerBreakdown = collect();
        $dailyTrend = ['labels' => [], 'values' => []];
        $summary = [
            'enrollments' => 0,
            'check_ins' => 0,
            'days' => 0,
            'venues' => 0,
        ];

        if ($event) {
            $summary['enrollments'] = EventEnrollment::query()->active()->where('event_id', $event->id)->count();
            $summary['check_ins'] = EventAttendance::query()->where('event_id', $event->id)->count();
            $summary['days'] = $event->days->count();
            $summary['venues'] = $event->venues->count();

            $dayBreakdown = EventAttendance::query()
                ->join('event_days', 'event_days.id', '=', 'event_attendances.event_day_id')
                ->where('event_attendances.event_id', $event->id)
                ->selectRaw('event_days.id, event_days.day_number, event_days.description, COUNT(event_attendances.id) as total')
                ->groupBy('event_days.id', 'event_days.day_number', 'event_days.description')
                ->orderBy('event_days.day_number')
                ->get();

            $venueBreakdown = EventAttendance::query()
                ->leftJoin('event_venues', 'event_venues.id', '=', 'event_attendances.event_venue_id')
                ->where('event_attendances.event_id', $event->id)
                ->selectRaw("COALESCE(event_venues.name, 'No venue') as label, COUNT(event_attendances.id) as total")
                ->groupByRaw("COALESCE(event_venues.name, 'No venue')")
                ->orderByDesc('total')
                ->get();

            $modeBreakdown = EventEnrollment::query()
                ->active()
                ->where('event_id', $event->id)
                ->selectRaw("COALESCE(participation_mode, 'unspecified') as label, COUNT(*) as total")
                ->groupByRaw("COALESCE(participation_mode, 'unspecified')")
                ->orderByDesc('total')
                ->get();

            $officerBreakdown = EventAttendance::query()
                ->leftJoin('users', 'users.id', '=', 'event_attendances.checked_in_by')
                ->where('event_attendances.event_id', $event->id)
                ->selectRaw("COALESCE(users.name, 'Unknown') as label, COUNT(event_attendances.id) as total")
                ->groupByRaw("COALESCE(users.name, 'Unknown')")
                ->orderByDesc('total')
                ->limit(10)
                ->get();

            $trendRows = EventAttendance::query()
                ->where('event_id', $event->id)
                ->selectRaw('DATE(checked_in_at) as day, COUNT(*) as total')
                ->groupBy('day')
                ->orderBy('day')
                ->get();

            $dailyTrend = [
                'labels' => $trendRows->map(fn ($row) => SriLankaDate::format($row->day, 'd M'))->all(),
                'values' => $trendRows->pluck('total')->map(fn ($v) => (int) $v)->all(),
            ];
        }

        $charts = [
            'by_day' => [
                'labels' => $dayBreakdown->map(fn ($row) => 'Day '.$row->day_number)->all(),
                'values' => $dayBreakdown->pluck('total')->map(fn ($v) => (int) $v)->all(),
            ],
            'by_venue' => [
                'labels' => $venueBreakdown->pluck('label')->all(),
                'values' => $venueBreakdown->pluck('total')->map(fn ($v) => (int) $v)->all(),
            ],
            'by_mode' => [
                'labels' => $modeBreakdown->map(fn ($row) => ucfirst((string) $row->label))->all(),
                'values' => $modeBreakdown->pluck('total')->map(fn ($v) => (int) $v)->all(),
            ],
            'daily_trend' => $dailyTrend,
            'by_officer' => [
                'labels' => $officerBreakdown->pluck('label')->all(),
                'values' => $officerBreakdown->pluck('total')->map(fn ($v) => (int) $v)->all(),
            ],
        ];

        return view('admin.reports.attendance', compact(
            'events',
            'event',
            'summary',
            'charts',
            'dayBreakdown',
            'venueBreakdown',
            'modeBreakdown',
            'officerBreakdown',
        ));
    }

    public function items(Request $request): View
    {
        $this->ensureCanViewReports();

        $events = Event::query()->orderByDesc('start_date')->get();
        $selectedEventId = $request->filled('event') ? $request->integer('event') : null;

        $itemsQuery = CheckInItem::query()
            ->withCount(['attendances as given_count' => function ($query) use ($selectedEventId) {
                if ($selectedEventId) {
                    $query->where('event_attendances.event_id', $selectedEventId);
                }
            }])
            ->ordered();

        $items = $itemsQuery->get();

        $totalGiven = (int) $items->sum('given_count');
        $activeItems = $items->where('is_active', true)->count();

        $byEvent = DB::table('event_attendance_check_in_item')
            ->join('event_attendances', 'event_attendances.id', '=', 'event_attendance_check_in_item.event_attendance_id')
            ->join('events', 'events.id', '=', 'event_attendances.event_id')
            ->join('check_in_items', 'check_in_items.id', '=', 'event_attendance_check_in_item.check_in_item_id')
            ->when($selectedEventId, fn ($q) => $q->where('events.id', $selectedEventId))
            ->selectRaw('events.name as event_name, check_in_items.name as item_name, COUNT(*) as total')
            ->groupBy('events.id', 'events.name', 'check_in_items.id', 'check_in_items.name')
            ->orderBy('events.name')
            ->orderByDesc('total')
            ->get();

        $charts = [
            'items_given' => [
                'labels' => $items->pluck('name')->all(),
                'values' => $items->pluck('given_count')->map(fn ($v) => (int) $v)->all(),
            ],
        ];

        return view('admin.reports.items', compact(
            'events',
            'selectedEventId',
            'items',
            'totalGiven',
            'activeItems',
            'byEvent',
            'charts',
        ));
    }

    private function ensureCanViewReports(): void
    {
        $user = Auth::guard('web')->user();
        if (! $user || ! $user->canViewReports()) {
            abort(403);
        }
    }
}
