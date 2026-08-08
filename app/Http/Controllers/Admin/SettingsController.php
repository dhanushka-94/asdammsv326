<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\AppSettings;
use App\Support\MemberSessionInvalidator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function edit(): View
    {
        return view('admin.settings.edit', [
            'maintenanceMode' => AppSettings::maintenanceMode(),
            'maintenanceMessage' => AppSettings::maintenanceMessage(),
            'memberRegistrationEnabled' => AppSettings::memberRegistrationEnabled(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'maintenance_mode' => ['nullable', 'boolean'],
            'maintenance_message' => ['required', 'string', 'max:1000'],
            'member_registration_enabled' => ['nullable', 'boolean'],
        ]);

        $wasOn = AppSettings::maintenanceMode();
        $nowOn = $request->boolean('maintenance_mode');
        $registrationOn = $request->boolean('member_registration_enabled');

        AppSettings::setMaintenanceMode($nowOn);
        AppSettings::setMaintenanceMessage(trim($data['maintenance_message']));
        AppSettings::setMemberRegistrationEnabled($registrationOn);

        if ($nowOn && ! $wasOn) {
            MemberSessionInvalidator::flushAll();
        }

        $parts = [
            $registrationOn
                ? 'Member registration is ON.'
                : 'Member registration is OFF.',
            $nowOn
                ? 'Maintenance mode is ON. Public site is blocked and logged-in members were signed out. Admin panel remains available.'
                : 'Maintenance mode is OFF. Public member site is working again.',
        ];

        return back()->with('success', implode(' ', $parts));
    }
}
