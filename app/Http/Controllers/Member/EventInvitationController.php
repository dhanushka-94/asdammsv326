<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventEnrollment;
use App\Support\ActivityLogger;
use App\Support\EventInvitationPdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class EventInvitationController extends Controller
{
    public function letterIndex(): View
    {
        return $this->index(EventInvitationPdf::TYPE_LETTER);
    }

    public function cardIndex(): View
    {
        return $this->index(EventInvitationPdf::TYPE_CARD);
    }

    public function letter(Event $event): StreamedResponse|RedirectResponse
    {
        return $this->download($event, EventInvitationPdf::TYPE_LETTER);
    }

    public function card(Event $event): StreamedResponse|RedirectResponse
    {
        return $this->download($event, EventInvitationPdf::TYPE_CARD);
    }

    private function index(string $type): View
    {
        $member = Auth::guard('member')->user();
        $isCard = $type === EventInvitationPdf::TYPE_CARD;

        $events = Event::query()
            ->visibleToMember($member)
            ->when(
                $isCard,
                fn ($query) => $query->whereNotNull('invitation_card_path')->where('invitation_card_path', '!=', ''),
                fn ($query) => $query->whereNotNull('invitation_letter_path')->where('invitation_letter_path', '!=', ''),
            )
            ->orderBy('start_date')
            ->orderBy('start_time')
            ->get();

        $enrolledIds = EventEnrollment::query()
            ->active()
            ->where('member_id', $member->id)
            ->pluck('event_id')
            ->all();

        $events = $events
            ->sortByDesc(fn (Event $event) => in_array($event->id, $enrolledIds, true))
            ->values();

        return view('member.invitations.index', [
            'member' => $member,
            'events' => $events,
            'enrolledIds' => $enrolledIds,
            'type' => $type,
            'isCard' => $isCard,
            'pageTitle' => $isCard ? 'Invitation Card' : 'Invitation Letter',
            'pageSubtitle' => $isCard
                ? 'Download your personalized invitation card as PDF.'
                : 'Download your personalized invitation letter as PDF.',
        ]);
    }

    private function download(Event $event, string $type): StreamedResponse|RedirectResponse
    {
        $member = Auth::guard('member')->user();
        $isCard = $type === EventInvitationPdf::TYPE_CARD;
        $indexRoute = $isCard ? 'member.invitations.card' : 'member.invitations.letter';

        if (! $event->isVisibleToMember($member)) {
            return redirect()
                ->route($indexRoute)
                ->with('error', 'This invitation is not available. Only invited active members can download it.');
        }

        $hasTemplate = $isCard
            ? $event->hasInvitationCard()
            : $event->hasInvitationLetter();

        if (! $hasTemplate) {
            return redirect()
                ->route($indexRoute)
                ->with('error', 'This invitation is not available yet.');
        }

        try {
            $response = EventInvitationPdf::download($event, $member, $type);

            ActivityLogger::log(
                'downloaded',
                'Downloaded event invitation '.($isCard ? 'card' : 'letter')
                    .' for '.$event->name,
                subject: $event,
                guard: 'member',
                causer: $member,
                properties: [
                    'event_id' => $event->id,
                    'invitation_type' => $type,
                ],
            );

            return $response;
        } catch (Throwable) {
            return redirect()
                ->route($indexRoute)
                ->with('error', 'Could not generate the invitation PDF. Please try again later.');
        }
    }
}
