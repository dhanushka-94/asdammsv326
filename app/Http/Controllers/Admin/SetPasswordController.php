<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class SetPasswordController extends Controller
{
    public function edit(): View|RedirectResponse
    {
        $user = Auth::guard('web')->user();

        if (! $user->must_change_password) {
            return redirect()->route('admin.dashboard');
        }

        return view('admin.set-password', compact('user'));
    }

    public function update(Request $request): RedirectResponse
    {
        $user = Auth::guard('web')->user();

        if (! $user->must_change_password) {
            return redirect()->route('admin.dashboard');
        }

        $data = $request->validate([
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        if ($data['password'] === $user->defaultPassword()) {
            return back()->withErrors([
                'password' => 'Your new password cannot be the default password. Please choose a different password.',
            ]);
        }

        $user->update([
            'password' => $data['password'],
            'must_change_password' => false,
        ]);

        return redirect()
            ->route('admin.dashboard')
            ->with('success', 'Password updated successfully.');
    }
}
