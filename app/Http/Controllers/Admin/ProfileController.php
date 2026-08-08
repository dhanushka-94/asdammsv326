<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function show(): View
    {
        $user = Auth::guard('web')->user();

        return view('admin.profile.show', compact('user'));
    }

    public function edit(): View
    {
        $user = Auth::guard('web')->user();

        return view('admin.profile.edit', compact('user'));
    }

    public function update(Request $request): RedirectResponse
    {
        $user = Auth::guard('web')->user();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'phone' => ['nullable', 'string', 'max:30'],
            'password' => ['nullable', 'confirmed', Password::defaults()],
        ]);

        if (empty($data['password'])) {
            unset($data['password']);
        } else {
            $data['must_change_password'] = false;
        }

        $user->update($data);

        return redirect()
            ->route('admin.profile.show')
            ->with('success', 'Your profile was updated successfully.');
    }

    public function updateDeskPin(Request $request): RedirectResponse
    {
        $user = Auth::guard('web')->user();

        if (! $user->canAccessAttendance()) {
            abort(403, 'Desk PIN is only for attendance desk users.');
        }

        $action = $request->input('action', 'save');

        if ($action === 'clear') {
            if ($user->hasDeskPin()) {
                $request->validate([
                    'current_desk_pin' => ['required', 'digits:4'],
                ]);

                if (! $user->verifyDeskPin($request->input('current_desk_pin'))) {
                    return back()->withErrors(['current_desk_pin' => 'Current desk PIN is incorrect.'])->withInput();
                }
            }

            $user->clearDeskPin();
            session()->forget(['attendance_desk_locked', 'attendance_desk_lock_return']);

            return redirect()
                ->route('admin.profile.edit')
                ->with('success', 'Attendance desk PIN was removed.');
        }

        $rules = [
            'desk_pin' => ['required', 'digits:4'],
            'desk_pin_confirmation' => ['required', 'same:desk_pin'],
        ];

        if ($user->hasDeskPin()) {
            $rules['current_desk_pin'] = ['required', 'digits:4'];
        }

        $data = $request->validate($rules);

        if ($user->hasDeskPin() && ! $user->verifyDeskPin($data['current_desk_pin'])) {
            return back()->withErrors(['current_desk_pin' => 'Current desk PIN is incorrect.'])->withInput();
        }

        $user->setDeskPin($data['desk_pin']);

        return redirect()
            ->route('admin.profile.edit')
            ->with('success', 'Attendance desk PIN saved. Use it to lock and unlock the desk quickly.');
    }
}
