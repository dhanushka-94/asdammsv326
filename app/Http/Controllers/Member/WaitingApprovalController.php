<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Support\ActivityLogger;
use App\Support\MemberQrCode;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class WaitingApprovalController extends Controller
{
    public function show(): View|RedirectResponse
    {
        $member = Auth::guard('member')->user()->load('designation');

        if ($member->canLogin()) {
            return redirect()->route($member->homeRoute());
        }

        $qrUrl = $member->unique_id
            ? route('member.waiting-approval.qr.image')
            : null;

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

    public function showQrImage(): Response
    {
        $member = Auth::guard('member')->user();

        if (! $member->unique_id) {
            abort(404, 'Unique ID is not available yet.');
        }

        try {
            return MemberQrCode::imageResponse($member->unique_id);
        } catch (\Throwable $e) {
            report($e);
            abort(500, 'Unable to generate QR code image.');
        }
    }

    public function downloadQr(): StreamedResponse|RedirectResponse
    {
        $member = Auth::guard('member')->user();

        if (! $member->unique_id) {
            return redirect()->route('member.waiting-approval')
                ->with('error', 'Unique ID is not available yet.');
        }

        $filename = MemberQrCode::downloadFilename($member->displayName(), $member->unique_id);

        $member->recordQrDownload();

        ActivityLogger::log(
            'downloaded',
            'Downloaded membership QR while waiting for approval',
            subject: $member,
            guard: 'member',
            causer: $member,
        );

        try {
            return MemberQrCode::downloadResponse($member->unique_id, $filename);
        } catch (\Throwable $e) {
            report($e);

            return redirect()->route('member.waiting-approval')
                ->with('error', 'Unable to download QR code.');
        }
    }
}
