<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Support\EventInvitationPdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class EventInvitationController extends Controller
{
    public function letter(Event $event): StreamedResponse|RedirectResponse
    {
        return $this->download($event, EventInvitationPdf::TYPE_LETTER);
    }

    public function card(Event $event): StreamedResponse|RedirectResponse
    {
        return $this->download($event, EventInvitationPdf::TYPE_CARD);
    }

    private function download(Event $event, string $type): StreamedResponse|RedirectResponse
    {
        $member = Auth::guard('member')->user();

        if (! $event->isActive()) {
            return redirect()
                ->route('member.events.index')
                ->with('error', 'This event is not available.');
        }

        if (! $member->canLogin()) {
            return redirect()
                ->route('member.events.show', $event)
                ->with('error', 'Only active approved members can download invitations.');
        }

        $hasTemplate = $type === EventInvitationPdf::TYPE_CARD
            ? $event->hasInvitationCard()
            : $event->hasInvitationLetter();

        if (! $hasTemplate) {
            return redirect()
                ->route('member.events.show', $event)
                ->with('error', 'This invitation is not available yet.');
        }

        try {
            return EventInvitationPdf::download($event, $member, $type);
        } catch (Throwable) {
            return redirect()
                ->route('member.events.show', $event)
                ->with('error', 'Could not generate the invitation PDF. Please try again later.');
        }
    }
}
