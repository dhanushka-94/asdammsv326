<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureAttendanceDeskIsUnlocked
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::guard('web')->user();

        if (! $user || ! session('attendance_desk_locked')) {
            return $next($request);
        }

        if ($this->isAllowedWhileLocked($request)) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'ok' => false,
                'status' => 'desk_locked',
                'message' => 'Attendance desk is locked. Enter your 4-digit PIN to continue.',
            ], 423);
        }

        return redirect()->route('admin.attendance.lock');
    }

    private function isAllowedWhileLocked(Request $request): bool
    {
        return $request->routeIs([
            'admin.attendance.lock',
            'admin.attendance.unlock',
            'admin.attendance.lock.store',
            'admin.logout',
        ]);
    }
}
