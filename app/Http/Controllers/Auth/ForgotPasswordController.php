<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Support\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;

class ForgotPasswordController extends Controller
{
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        ActivityLogger::log(
            'password_reset',
            $status === Password::RESET_LINK_SENT
                ? 'Requested system password reset email for '.$request->string('email')->toString()
                : 'Failed system password reset email request for '.$request->string('email')->toString(),
            guard: 'web',
            properties: [
                'email' => $request->string('email')->toString(),
                'status' => $status,
            ],
        );

        return $status === Password::RESET_LINK_SENT
            ? back()->with('status', __($status))
            : back()->withInput($request->only('email'))
                ->withErrors(['email' => __($status)]);
    }
}
