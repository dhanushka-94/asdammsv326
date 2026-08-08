<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use App\Support\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function show(Request $request): View
    {
        $user = Auth::guard('web')->user();

        $logsQuery = ActivityLog::query()
            ->where('causer_type', User::class)
            ->where('causer_id', $user->id)
            ->latest('created_at');

        if ($search = $request->string('search')->trim()->toString()) {
            $logsQuery->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                    ->orWhere('subject_label', 'like', "%{$search}%")
                    ->orWhere('ip_address', 'like', "%{$search}%")
                    ->orWhere('action', 'like', "%{$search}%")
                    ->orWhere('route_name', 'like', "%{$search}%");
            });
        }

        if ($action = $request->string('action')->toString()) {
            $logsQuery->where('action', $action);
        }

        if ($dateFrom = $request->string('date_from')->toString()) {
            $logsQuery->whereDate('created_at', '>=', $dateFrom);
        }

        if ($dateTo = $request->string('date_to')->toString()) {
            $logsQuery->whereDate('created_at', '<=', $dateTo);
        }

        $activityLogs = $logsQuery->paginate(15)->withQueryString();

        $activityActions = ActivityLog::query()
            ->where('causer_type', User::class)
            ->where('causer_id', $user->id)
            ->select('action')
            ->distinct()
            ->orderBy('action')
            ->pluck('action');

        return view('admin.profile.show', compact('user', 'activityLogs', 'activityActions'));
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

            ActivityLogger::log(
                'updated',
                'Removed attendance desk PIN',
                subject: $user,
                guard: 'web',
                causer: $user,
            );

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

        $wasSet = $user->hasDeskPin();
        $user->setDeskPin($data['desk_pin']);

        ActivityLogger::log(
            'updated',
            $wasSet ? 'Updated attendance desk PIN' : 'Set attendance desk PIN',
            subject: $user,
            guard: 'web',
            causer: $user,
        );

        return redirect()
            ->route('admin.profile.edit')
            ->with('success', 'Attendance desk PIN saved. Use it to lock and unlock the desk quickly.');
    }
}
