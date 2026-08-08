<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureMemberPasswordChanged
{
    public function handle(Request $request, Closure $next): Response
    {
        $member = Auth::guard('member')->user();

        if (! $member) {
            return redirect()->route('member.login');
        }

        if ($member->must_change_password) {
            return redirect()->route('member.password.edit');
        }

        return $next($request);
    }
}
