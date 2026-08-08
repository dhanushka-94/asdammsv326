<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSystemUserPasswordChanged
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user('web');

        if (! $user) {
            return redirect()->route('admin.login');
        }

        if ($user->must_change_password) {
            return redirect()->route('admin.set-password.edit');
        }

        return $next($request);
    }
}
