<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Support\SriLankaFormat;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class SetPasswordController extends Controller
{
    public function edit(): View|RedirectResponse
    {
        $member = Auth::guard('member')->user();

        if (! $member->canLogin()) {
            return redirect()->route('member.waiting-approval');
        }

        if (! $member->must_change_password) {
            return redirect()->route('member.profile');
        }

        return view('member.set-password', compact('member'));
    }

    public function update(Request $request): RedirectResponse
    {
        $member = Auth::guard('member')->user();

        if (! $member->canLogin()) {
            return redirect()->route('member.waiting-approval');
        }

        if (! $member->must_change_password) {
            return redirect()->route('member.profile');
        }

        $data = $request->validate([
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $normalizedPassword = SriLankaFormat::isValidNic($data['password'])
            ? SriLankaFormat::normalizeNic($data['password'])
            : $data['password'];

        if (strcasecmp($normalizedPassword, $member->nic) === 0) {
            return back()->withErrors([
                'password' => 'Your new password cannot be the same as your NIC. Please choose a different password.',
            ]);
        }

        if ($data['password'] === $member->defaultPassword()) {
            return back()->withErrors([
                'password' => 'Your new password cannot be the default password. Please choose a different password.',
            ]);
        }

        $member->update([
            'password' => $data['password'],
            'must_change_password' => false,
        ]);

        return redirect()
            ->route('member.profile')
            ->with('success', 'Password updated successfully. Welcome to ASDA MMS.');
    }
}
