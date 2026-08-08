<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Rules\SriLankanMobile;
use App\Rules\SriLankanPhone;
use App\Support\ActivityLogger;
use App\Support\MemberQrCode;
use App\Support\OrgLookups;
use App\Support\SriLankaFormat;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProfileController extends Controller
{
    public function show(): View
    {
        $member = Auth::guard('member')->user()->load(['designation', 'category']);
        $qrUrl = $member->qrCodeUrl();

        return view('member.profile', compact('member', 'qrUrl'));
    }

    public function downloadQr(): StreamedResponse|RedirectResponse
    {
        $member = Auth::guard('member')->user();

        if (! $member->unique_id) {
            return redirect()->route('member.profile')->with('error', 'Unique ID is not available yet.');
        }

        $path = MemberQrCode::ensure($member->unique_id);
        $filename = MemberQrCode::downloadFilename($member->displayName(), $member->unique_id);

        $member->recordQrDownload();

        ActivityLogger::log(
            'downloaded',
            'Downloaded own membership QR',
            subject: $member,
            guard: 'member',
            causer: $member,
        );

        return Storage::disk('public')->download($path, $filename);
    }

    public function edit(): View
    {
        $member = Auth::guard('member')->user()->load(['designation', 'category']);
        $orgTree = OrgLookups::cascadeTree();

        return view('member.profile-edit', compact('member', 'orgTree'));
    }

    public function update(Request $request): RedirectResponse
    {
        $member = Auth::guard('member')->user();

        $request->merge([
            'mobile_1' => SriLankaFormat::normalizePhone($request->input('mobile_1')),
            'mobile_2' => SriLankaFormat::normalizePhone($request->input('mobile_2')),
            'whatsapp' => SriLankaFormat::normalizePhone($request->input('whatsapp')),
            'office_telephone' => SriLankaFormat::normalizePhone($request->input('office_telephone')),
        ]);

        $data = $request->validate([
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
            'password' => ['nullable', 'confirmed', Password::defaults()],
        ]);

        if ($request->hasFile('profile_image')) {
            if ($member->profile_image) {
                Storage::disk('public')->delete($member->profile_image);
            }
            $data['profile_image'] = $request->file('profile_image')->store('members/profiles', 'public');
        }

        if (empty($data['password'])) {
            unset($data['password']);
        } else {
            $data['must_change_password'] = false;
        }

        $member->update($data);

        return redirect()
            ->route('member.profile')
            ->with('success', 'Profile updated successfully.');
    }
}
