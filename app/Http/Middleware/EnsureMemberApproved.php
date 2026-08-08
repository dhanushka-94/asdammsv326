<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureMemberApproved
{
    public function handle(Request $request, Closure $next): Response
    {
        $member = Auth::guard('member')->user();

        if (! $member) {
            return redirect()->route('member.login');
        }

        if (! $member->canLogin()) {
            return redirect()->route('member.waiting-approval');
        }

        return $next($request);
    }
}
