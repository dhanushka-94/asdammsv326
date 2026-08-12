<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventEnrollment;
use App\Models\EventEnrollmentAnswer;
use App\Support\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class EventPoolController extends Controller
{
    public function index(): View
    {
        $member = Auth::guard('member')->user();

        $events = Event::query()
            ->visibleToMember($member)
            ->with(['venues', 'days.sessions'])
            ->withCount('activeEnrollments')
            ->orderBy('start_date')
            ->orderBy('start_time')
            ->get();

        $enrolledIds = EventEnrollment::query()
            ->active()
            ->where('member_id', $member->id)
            ->pluck('event_id')
            ->all();

        return view('member.events.index', compact('events', 'enrolledIds', 'member'));
    }

    public function show(Event $event): View|RedirectResponse
    {
        $member = Auth::guard('member')->user();

        if (! $event->isVisibleToMember($member)) {
            return redirect()->route('member.events.index')
                ->with('error', 'This event is not available. Only invited active members can view it.');
        }

        $event->load(['venues', 'days.sessions', 'days.questions.options']);
        $enrollment = EventEnrollment::query()
            ->active()
            ->where('event_id', $event->id)
            ->where('member_id', $member->id)
            ->with(['answers.question', 'answers.option'])
            ->first();

        $enrolled = $enrollment !== null;
        $canRegister = $event->memberCanRegister($member);

        return view('member.events.show', compact('event', 'enrolled', 'enrollment', 'member', 'canRegister'));
    }

    public function enroll(Request $request, Event $event): RedirectResponse
    {
        $member = Auth::guard('member')->user();

        if (! $event->memberCanRegister($member)) {
            return back()->with('error', 'You are not eligible to register. You must be an active invited member and enrollment must be open.');
        }

        $event->load(['days.questions.options']);

        $allowedModes = match ($event->method) {
            'online' => ['online'],
            'both' => ['physical', 'online'],
            default => ['physical'],
        };

        $defaultMode = count($allowedModes) === 1 ? $allowedModes[0] : null;
        $questions = $event->days->flatMap(fn ($day) => $day->questions)->values();
        $enrollFormUrl = route('member.events.show', $event).'#enroll-form';

        $rules = [
            'participation_mode' => [
                Rule::requiredIf(fn () => count($allowedModes) > 1),
                'nullable',
                Rule::in($allowedModes),
            ],
            'answers' => [$questions->isEmpty() ? 'nullable' : 'required', 'array'],
        ];

        $messages = [
            'participation_mode.required' => 'Please choose how you will participate.',
            'participation_mode.in' => 'Please choose a valid participation option.',
            'answers.required' => 'Please answer all questionnaire questions.',
        ];

        foreach ($questions as $question) {
            $optionIds = $question->options->pluck('id')->all();
            $rules['answers.'.$question->id] = ['required', 'integer', Rule::in($optionIds)];
            $messages['answers.'.$question->id.'.required'] = 'Please select an answer for: '.$question->question;
            $messages['answers.'.$question->id.'.in'] = 'Invalid answer selected for: '.$question->question;
        }

        try {
            $data = $request->validate($rules, $messages);
        } catch (ValidationException $exception) {
            throw $exception->redirectTo($enrollFormUrl);
        }

        $participationMode = $data['participation_mode'] ?? $defaultMode;
        if ($participationMode === null || ! in_array($participationMode, $allowedModes, true)) {
            throw ValidationException::withMessages([
                'participation_mode' => 'Please choose how you will participate.',
            ])->redirectTo($enrollFormUrl);
        }

        $answerRows = [];
        foreach ($questions as $question) {
            $answerRows[] = [
                'event_day_question_id' => $question->id,
                'event_day_question_option_id' => (int) $data['answers'][$question->id],
            ];
        }

        DB::transaction(function () use ($event, $member, $participationMode, $answerRows): void {
            $enrollment = EventEnrollment::query()->firstOrNew([
                'event_id' => $event->id,
                'member_id' => $member->id,
            ]);

            $enrollment->fill([
                'enrolled_at' => now(),
                'participation_mode' => $participationMode,
                'kicked_at' => null,
                'kick_reason' => null,
                'kicked_by' => null,
            ])->save();

            $enrollment->answers()->delete();

            foreach ($answerRows as $row) {
                EventEnrollmentAnswer::query()->create([
                    'event_enrollment_id' => $enrollment->id,
                    ...$row,
                ]);
            }
        });

        ActivityLogger::log(
            'created',
            'Enrolled in event: '.$event->name,
            subject: $member,
            guard: 'member',
            causer: $member,
            properties: [
                'event_id' => $event->id,
                'event_name' => $event->name,
                'participation_mode' => $participationMode,
            ],
        );

        return redirect()
            ->route('member.events.show', $event)
            ->with('success', 'You are registered for '.$event->name.'.');
    }

    public function unenroll(Event $event): RedirectResponse
    {
        $member = Auth::guard('member')->user();

        if (! $event->isVisibleToMember($member)) {
            return redirect()->route('member.events.index')
                ->with('error', 'This event is not available.');
        }

        EventEnrollment::query()
            ->active()
            ->where('event_id', $event->id)
            ->where('member_id', $member->id)
            ->delete();

        ActivityLogger::log(
            'deleted',
            'Left event: '.$event->name,
            subject: $member,
            guard: 'member',
            causer: $member,
            properties: [
                'event_id' => $event->id,
                'event_name' => $event->name,
            ],
        );

        return back()->with('success', 'Registration removed for '.$event->name.'.');
    }
}
