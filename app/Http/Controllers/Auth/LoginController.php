<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Support\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function show(): View
    {
        return view('auth.admin-login');
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::guard('web')->attempt($credentials, $request->boolean('remember'))) {
            ActivityLogger::log(
                'login_failed',
                'Failed system login attempt for '.$credentials['email'],
                properties: ['email' => $credentials['email']],
                guard: 'web',
            );

            return back()
                ->withInput($request->only('email', 'remember'))
                ->withErrors(['email' => 'These credentials do not match our records.']);
        }

        $user = Auth::guard('web')->user();

        if (! $user->isActive()) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            ActivityLogger::log(
                'login_failed',
                'Inactive system user login blocked for '.$user->email,
                subject: $user,
                properties: ['email' => $user->email],
                guard: 'web',
                causer: $user,
            );

            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'Your account is inactive. Contact an administrator.']);
        }

        $request->session()->regenerate();

        ActivityLogger::log(
            'login',
            'System user signed in',
            subject: $user,
            guard: 'web',
            causer: $user,
        );

        return redirect()->intended(route($user->homeRoute()));
    }

    public function destroy(Request $request): RedirectResponse
    {
        $user = Auth::guard('web')->user();

        if ($user) {
            ActivityLogger::log(
                'logout',
                'System user signed out',
                subject: $user,
                guard: 'web',
                causer: $user,
            );
        }

        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}
