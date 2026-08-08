<?php

namespace App\Http\Middleware;

use App\Support\AppSettings;
use App\Support\MemberSessionInvalidator;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsurePublicAccessIsOpen
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($this->shouldBypass($request) || ! AppSettings::maintenanceMode()) {
            return $next($request);
        }

        if (Auth::guard('member')->check()) {
            Auth::guard('member')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => AppSettings::maintenanceMessage(),
            ], 503);
        }

        return response()
            ->view('maintenance', [
                'message' => AppSettings::maintenanceMessage(),
            ], 503);
    }

    private function shouldBypass(Request $request): bool
    {
        return $request->is('admin')
            || $request->is('admin/*')
            || $request->is('up');
    }
}
