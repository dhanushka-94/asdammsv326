<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Rules\SriLankanNic;
use App\Support\ActivityLogger;
use App\Support\SriLankaFormat;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function show(): View
    {
        return view('member.login');
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'nic' => ['required', 'string', new SriLankanNic],
            'password' => ['required', 'string'],
        ]);

        $nic = SriLankaFormat::normalizeNic($credentials['nic']);
        $password = $credentials['password'];
        if (SriLankaFormat::isValidNic($password)) {
            $password = SriLankaFormat::normalizeNic($password);
        }

        if (! Auth::guard('member')->attempt([
            'nic' => $nic,
            'password' => $password,
        ], $request->boolean('remember'))) {
            ActivityLogger::log(
                'login_failed',
                'Failed member login attempt for NIC '.$nic,
                properties: ['nic' => $nic],
                guard: 'member',
            );

            return back()
                ->withInput($request->only('nic', 'remember'))
                ->withErrors(['nic' => 'Invalid NIC or password.']);
        }

        $request->session()->regenerate();

        $member = Auth::guard('member')->user();
        $member->recordLogin();

        ActivityLogger::log(
            'login',
            'Member signed in',
            subject: $member,
            guard: 'member',
            causer: $member,
        );

        return redirect()->intended(route($member->homeRoute()));
    }

    public function destroy(Request $request): RedirectResponse
    {
        $member = Auth::guard('member')->user();

        if ($member) {
            ActivityLogger::log(
                'logout',
                'Member signed out',
                subject: $member,
                guard: 'member',
                causer: $member,
            );
        }

        Auth::guard('member')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('member.login');
    }
}
