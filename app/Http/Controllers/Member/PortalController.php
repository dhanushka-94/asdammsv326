<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PortalController extends Controller
{
    public function home(): View
    {
        $member = Auth::guard('member')->user()->load(['designation', 'category']);

        $events = Event::query()
            ->visibleToMember($member)
            ->orderBy('start_date')
            ->orderBy('start_time')
            ->get(['id', 'name', 'invitation_letter_path', 'invitation_card_path', 'status']);

        $hasInvitationLetter = $events->contains(fn (Event $event) => $event->hasInvitationLetter());
        $hasInvitationCard = $events->contains(fn (Event $event) => $event->hasInvitationCard());
        $hasEvents = $events->isNotEmpty();
        $qrUrl = $member->unique_id
            ? route('member.profile.qr.image')
            : null;

        return view('member.portal', compact(
            'member',
            'hasEvents',
            'hasInvitationLetter',
            'hasInvitationCard',
            'qrUrl',
        ));
    }
}
