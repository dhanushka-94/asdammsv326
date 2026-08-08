<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Support\ActivityLogger;
use App\Support\MemberQrCode;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class WaitingApprovalController extends Controller
{
    public function show(): View|RedirectResponse
    {
        $member = Auth::guard('member')->user()->load('designation');

        if ($member->canLogin()) {
            return redirect()->route($member->homeRoute());
        }

        $qrUrl = $member->qrCodeUrl();

        return view('member.waiting-approval', compact('member', 'qrUrl'));
    }

    public function status(): JsonResponse
    {
        $member = Auth::guard('member')->user()->fresh();

        return response()->json([
            'registration_status' => $member->registration_status,
            'status' => $member->status,
            'can_login' => $member->canLogin(),
            'unique_id' => $member->unique_id,
            'rejection_reason' => $member->rejection_reason,
            'redirect' => $member->canLogin() ? route($member->homeRoute()) : null,
        ]);
    }

    public function downloadQr(): StreamedResponse|RedirectResponse
    {
        $member = Auth::guard('member')->user();

        if (! $member->unique_id) {
            return redirect()->route('member.waiting-approval')
                ->with('error', 'Unique ID is not available yet.');
        }

        $path = MemberQrCode::ensure($member->unique_id);
        $filename = MemberQrCode::downloadFilename($member->displayName(), $member->unique_id);

        $member->recordQrDownload();

        ActivityLogger::log(
            'downloaded',
            'Downloaded membership QR while waiting for approval',
            subject: $member,
            guard: 'member',
            causer: $member,
        );

        return Storage::disk('public')->download($path, $filename);
    }
}
