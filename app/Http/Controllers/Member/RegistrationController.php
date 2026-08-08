<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\Designation;
use App\Models\Member;
use App\Rules\SriLankanMobile;
use App\Rules\SriLankanNic;
use App\Rules\SriLankanPhone;
use App\Support\ActivityLogger;
use App\Support\AppSettings;
use App\Support\MemberProfileImage;
use App\Support\OrgLookups;
use App\Support\SriLankaFormat;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class RegistrationController extends Controller
{
    public function create(): View|RedirectResponse
    {
        if (! AppSettings::memberRegistrationEnabled()) {
            return redirect()
                ->route('member.login')
                ->with('error', 'Member registration is currently closed.');
        }

        $designations = Designation::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $orgTree = OrgLookups::cascadeTree();

        return view('member.register', compact('designations', 'orgTree'));
    }

    public function store(Request $request): RedirectResponse
    {
        if (! AppSettings::memberRegistrationEnabled()) {
            return redirect()
                ->route('member.login')
                ->with('error', 'Member registration is currently closed.');
        }

        $request->merge([
            'nic' => SriLankaFormat::normalizeNic($request->input('nic')),
            'mobile_1' => SriLankaFormat::normalizePhone($request->input('mobile_1')),
            'mobile_2' => SriLankaFormat::normalizePhone($request->input('mobile_2')),
            'whatsapp' => SriLankaFormat::normalizePhone($request->input('whatsapp')),
            'office_telephone' => SriLankaFormat::normalizePhone($request->input('office_telephone')),
        ]);

        $data = $request->validate([
            'title' => ['required', 'in:Dr,Mr,Mrs,Ms,Prof,Eng'],
            'full_name' => ['required', 'string', 'max:255'],
            'nic' => ['required', 'string', 'max:12', new SriLankanNic, 'unique:members,nic'],
            'designation_id' => ['required', 'exists:designations,id'],
            'mobile_1' => ['required', 'string', 'max:15', new SriLankanMobile],
            'mobile_2' => ['nullable', 'string', 'max:15', new SriLankanMobile],
            'whatsapp' => ['nullable', 'string', 'max:15', new SriLankanMobile],
            'office_telephone' => ['nullable', 'string', 'max:15', new SriLankanPhone],
            'email' => ['nullable', 'email', 'max:255'],
            'institute' => ['nullable', 'string', 'max:255'],
            'sub_institute' => ['nullable', 'string', 'max:255'],
            'section' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:1000'],
            'profile_image' => ['nullable', 'image', 'max:2048'],
        ], [
            'nic.unique' => 'This NIC number is already registered.',
        ]);

        $data['unique_id'] = Member::generateUniqueId();
        $data['password'] = Member::defaultPasswordForNic($data['nic']);
        $data['must_change_password'] = true;
        $data['registration_status'] = 'pending';
        $data['status'] = 'inactive';

        if ($request->hasFile('profile_image')) {
            $data['profile_image'] = MemberProfileImage::store(
                $request->file('profile_image'),
                $data['unique_id'],
            );
        }

        $member = Member::create($data);

        Auth::guard('member')->login($member);
        $request->session()->regenerate();

        ActivityLogger::log(
            'registered',
            'New member registered: '.$member->displayName(),
            subject: $member,
            guard: 'member',
            causer: $member,
        );

        return redirect()
            ->route('member.waiting-approval')
            ->with('success', 'Registration submitted. You are now on the Waiting Approval page.');
    }
}
