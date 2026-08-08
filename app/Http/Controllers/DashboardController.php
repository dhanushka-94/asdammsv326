<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Event;
use App\Models\EventAttendance;
use App\Models\EventEnrollment;
use App\Models\Member;
use App\Models\User;
use App\Support\SriLankaDate;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $today = SriLankaDate::now()->toDateString();

        $stats = [
            'total_members' => Member::query()->count(),
            'pending_members' => Member::query()->where('registration_status', 'pending')->count(),
            'approved_members' => Member::query()->where('registration_status', 'approved')->count(),
            'rejected_members' => Member::query()->where('registration_status', 'rejected')->count(),
            'active_members' => Member::query()->where('status', 'active')->count(),
            'members_today' => Member::query()->whereDate('created_at', $today)->count(),
            'total_users' => User::query()->count(),
            'active_users' => User::query()->where('status', 'active')->count(),
            'active_events' => Event::query()->where('status', 'active')->count(),
            'total_enrollments' => EventEnrollment::query()->active()->count(),
            'check_ins_today' => EventAttendance::query()->whereDate('checked_in_at', $today)->count(),
            'check_ins_total' => EventAttendance::query()->count(),
        ];

        $registrationTrend = Member::query()
            ->selectRaw('DATE(created_at) as day, COUNT(*) as total')
            ->where('created_at', '>=', now()->subDays(13)->startOfDay())
            ->groupBy('day')
            ->orderBy('day')
            ->get()
            ->keyBy(fn ($row) => (string) $row->day);

        $trendLabels = [];
        $trendValues = [];
        for ($i = 13; $i >= 0; $i--) {
            $day = now()->subDays($i)->toDateString();
            $trendLabels[] = SriLankaDate::format($day, 'd M');
            $trendValues[] = (int) ($registrationTrend[$day]->total ?? 0);
        }

        $charts = [
            'registration_trend' => [
                'labels' => $trendLabels,
                'values' => $trendValues,
            ],
            'registration_status' => [
                'labels' => ['Approved', 'Pending', 'Rejected'],
                'values' => [
                    $stats['approved_members'],
                    $stats['pending_members'],
                    $stats['rejected_members'],
                ],
            ],
        ];

        $spotlightEvents = Event::query()
            ->withCount(['activeEnrollments', 'attendances', 'days'])
            ->where('status', 'active')
            ->orderByDesc('start_date')
            ->limit(12)
            ->get()
            ->filter(fn (Event $event) => $event->isOngoing() || $event->isUpcoming() || ! $event->hasEnded())
            ->take(4)
            ->values();

        $recentMembers = Member::query()
            ->with('designation')
            ->latest()
            ->take(6)
            ->get();

        $recentCheckIns = EventAttendance::query()
            ->with(['member', 'event', 'venue', 'checkedInBy'])
            ->latest('checked_in_at')
            ->take(6)
            ->get();

        $recentActivity = ActivityLog::query()
            ->latest('created_at')
            ->take(8)
            ->get();

        $user = Auth::guard('web')->user();

        return view('dashboard', compact(
            'stats',
            'charts',
            'spotlightEvents',
            'recentMembers',
            'recentCheckIns',
            'recentActivity',
            'user',
        ));
    }
}
