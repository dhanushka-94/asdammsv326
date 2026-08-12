<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Designation;
use App\Models\Event;
use App\Models\Member;
use App\Models\MemberCategory;
use App\Support\EventInvitationPdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class EventController extends Controller
{
    public function index(): View
    {
        $events = Event::query()
            ->with([
                'venues:id,event_id,name,floor,hall_room,sort_order',
                'days' => fn ($q) => $q->with([
                    'sessions:id,event_day_id,name,sort_order',
                    'questions.options',
                ])->orderBy('sort_order'),
            ])
            ->withCount(['venues', 'days', 'activeEnrollments'])
            ->latest('start_date')
            ->paginate(12);

        return view('admin.events.index', compact('events'));
    }

    public function create(): View
    {
        return view('admin.events.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        $event = DB::transaction(function () use ($request, $data) {
            $event = Event::create($data['event']);
            $this->syncVenues($event, $data['venues']);
            $this->syncDays($event, $data['days']);
            $this->syncInvitationFiles($event, $request);

            return $event;
        });

        return redirect()
            ->route('admin.events.invites.edit', $event)
            ->with('success', 'Event created successfully. Select members to invite so they can see and register for this event.');
    }

    public function show(Request $request, Event $event): View
    {
        $event->load(['venues', 'days.sessions', 'days.questions.options']);
        $event->loadCount(['activeEnrollments', 'venues', 'days', 'invitedMembers']);

        $enrollmentQuery = $event->enrollments()
            ->active()
            ->with(['member.designation', 'member.category', 'answers.question', 'answers.option']);

        $memberFilters = $request->filled('enrollment_search')
            || $request->filled('enrollment_designation_id')
            || $request->filled('enrollment_category_id')
            || $request->filled('enrollment_institute');

        if ($memberFilters) {
            $enrollmentQuery->whereHas('member', function ($query) use ($request) {
                if ($search = trim((string) $request->input('enrollment_search'))) {
                    $query->where(function ($inner) use ($search) {
                        $inner->where('full_name', 'like', '%'.$search.'%')
                            ->orWhere('nic', 'like', '%'.$search.'%')
                            ->orWhere('unique_id', 'like', '%'.$search.'%')
                            ->orWhere('mobile_1', 'like', '%'.$search.'%')
                            ->orWhere('email', 'like', '%'.$search.'%')
                            ->orWhere('institute', 'like', '%'.$search.'%')
                            ->orWhere('sub_institute', 'like', '%'.$search.'%')
                            ->orWhere('section', 'like', '%'.$search.'%');
                    });
                }

                if ($request->filled('enrollment_designation_id')) {
                    $query->where('designation_id', $request->integer('enrollment_designation_id'));
                }

                if ($request->filled('enrollment_category_id')) {
                    $query->where('member_category_id', $request->integer('enrollment_category_id'));
                }

                if ($institute = trim((string) $request->input('enrollment_institute'))) {
                    $query->where('institute', $institute);
                }
            });
        }

        if ($request->filled('enrollment_date_from')) {
            $enrollmentQuery->whereDate('enrolled_at', '>=', $request->input('enrollment_date_from'));
        }

        if ($request->filled('enrollment_date_to')) {
            $enrollmentQuery->whereDate('enrolled_at', '<=', $request->input('enrollment_date_to'));
        }

        $activeEnrollments = $enrollmentQuery
            ->latest('enrolled_at')
            ->paginate(15, ['*'], 'enrollment_page')
            ->withQueryString();

        $kickedEnrollments = $event->enrollments()
            ->whereNotNull('kicked_at')
            ->with(['member.designation', 'kickedBy'])
            ->latest('kicked_at')
            ->get();

        $durationDays = $event->start_date && $event->end_date
            ? $event->start_date->diffInDays($event->end_date) + 1
            : null;

        $designations = Designation::query()->orderBy('name')->get(['id', 'name']);
        $categories = MemberCategory::query()->orderBy('name')->get(['id', 'name']);
        $institutes = Member::query()
            ->whereHas('eventEnrollments', function ($query) use ($event) {
                $query->where('event_id', $event->id)->whereNull('kicked_at');
            })
            ->whereNotNull('institute')
            ->where('institute', '!=', '')
            ->distinct()
            ->orderBy('institute')
            ->pluck('institute');

        $activeCount = $event->active_enrollments_count;
        $enrollmentFiltersActive = $request->hasAny([
            'enrollment_search',
            'enrollment_designation_id',
            'enrollment_category_id',
            'enrollment_institute',
            'enrollment_date_from',
            'enrollment_date_to',
        ]);

        return view('admin.events.show', compact(
            'event',
            'activeEnrollments',
            'kickedEnrollments',
            'durationDays',
            'designations',
            'categories',
            'institutes',
            'activeCount',
            'enrollmentFiltersActive',
        ));
    }

    public function edit(Event $event): View
    {
        $event->load(['venues', 'days.sessions', 'days.questions.options']);

        return view('admin.events.edit', compact('event'));
    }

    public function update(Request $request, Event $event): RedirectResponse
    {
        $data = $this->validated($request);

        DB::transaction(function () use ($request, $event, $data): void {
            $event->update($data['event']);
            $event->venues()->delete();
            $this->syncVenues($event, $data['venues']);
            $event->days()->delete();
            $this->syncDays($event, $data['days']);
            $this->syncInvitationFiles($event, $request);
        });

        return redirect()->route('admin.events.show', $event)->with('success', 'Event updated successfully.');
    }

    public function destroy(Request $request, Event $event): RedirectResponse
    {
        $request->validate([
            'confirm_name' => ['required', 'string', 'max:255'],
        ], [
            'confirm_name.required' => 'Type the exact event name to confirm deletion.',
        ]);

        if (trim($request->input('confirm_name')) !== $event->name) {
            return back()
                ->withErrors(['confirm_name' => 'Confirmation text does not match the event name.'])
                ->withInput();
        }

        $name = $event->name;
        $this->deleteInvitationFiles($event);
        $event->delete();

        return redirect()
            ->route('admin.events.index')
            ->with('success', 'Event “'.$name.'” was permanently deleted.');
    }

    /**
     * @return array{event: array<string, mixed>, venues: list<array<string, mixed>>, days: list<array<string, mixed>>}
     */
    private function validated(Request $request): array
    {
        $physical = $request->boolean('method_physical');
        $online = $request->boolean('method_online');

        if (! $physical && ! $online) {
            throw ValidationException::withMessages([
                'method' => 'Select Physical, Online, or both.',
            ]);
        }

        $method = $physical && $online ? 'both' : ($online ? 'online' : 'physical');

        $request->merge([
            'method' => $method,
            'status' => $request->input('status', 'active'),
            'start_time' => substr((string) $request->input('start_time'), 0, 5),
            'end_time' => substr((string) $request->input('end_time'), 0, 5),
        ]);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'method' => ['required', Rule::in(['physical', 'online', 'both'])],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
            'invitation_letter' => ['nullable', 'file', 'extensions:pdf', 'max:10240'],
            'invitation_card' => ['nullable', 'file', 'extensions:pdf', 'max:10240'],
            'remove_invitation_letter' => ['nullable', 'boolean'],
            'remove_invitation_card' => ['nullable', 'boolean'],
            'venues' => ['nullable', 'array'],
            'venues.*.name' => ['nullable', 'string', 'max:255'],
            'venues.*.floor' => ['nullable', 'string', 'max:100'],
            'venues.*.hall_room' => ['nullable', 'string', 'max:100'],
            'venues.*.description' => ['nullable', 'string', 'max:2000'],
            'venues.*.latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'venues.*.longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'venues.*.maps_url' => ['nullable', 'url', 'max:1000'],
            'days' => ['nullable', 'array', 'max:14'],
            'days.*.day_number' => ['nullable', 'integer', 'min:1', 'max:99'],
            'days.*.description' => ['nullable', 'string', 'max:2000'],
            'days.*.sessions' => ['nullable', 'array', 'max:20'],
            'days.*.sessions.*.name' => ['nullable', 'string', 'max:255'],
            'days.*.sessions.*.description' => ['nullable', 'string', 'max:2000'],
            'days.*.questions' => ['nullable', 'array', 'max:20'],
            'days.*.questions.*.question' => ['nullable', 'string', 'max:500'],
            'days.*.questions.*.options' => ['nullable', 'array', 'max:12'],
            'days.*.questions.*.options.*.label' => ['nullable', 'string', 'max:255'],
        ]);

        if ($data['start_date'] === $data['end_date'] && $data['end_time'] <= $data['start_time']) {
            throw ValidationException::withMessages([
                'end_time' => 'End time must be after start time on the same day.',
            ]);
        }

        $venues = [];
        foreach ($data['venues'] ?? [] as $venue) {
            $name = trim((string) ($venue['name'] ?? ''));
            if ($name === '') {
                continue;
            }

            $venues[] = [
                'sort_order' => count($venues) + 1,
                'name' => $name,
                'floor' => filled($venue['floor'] ?? null) ? trim((string) $venue['floor']) : null,
                'hall_room' => filled($venue['hall_room'] ?? null) ? trim((string) $venue['hall_room']) : null,
                'description' => filled($venue['description'] ?? null) ? trim((string) $venue['description']) : null,
                'latitude' => filled($venue['latitude'] ?? null) ? $venue['latitude'] : null,
                'longitude' => filled($venue['longitude'] ?? null) ? $venue['longitude'] : null,
                'maps_url' => filled($venue['maps_url'] ?? null) ? trim((string) $venue['maps_url']) : null,
            ];
        }

        if (in_array($data['method'], ['physical', 'both'], true) && $venues === []) {
            throw ValidationException::withMessages([
                'venues' => 'Physical events need at least one venue.',
            ]);
        }

        $days = [];
        foreach ($data['days'] ?? [] as $index => $day) {
            $sessions = [];
            foreach ($day['sessions'] ?? [] as $session) {
                $sessionName = trim((string) ($session['name'] ?? ''));
                if ($sessionName === '') {
                    continue;
                }

                $sessions[] = [
                    'sort_order' => count($sessions) + 1,
                    'name' => $sessionName,
                    'description' => filled($session['description'] ?? null) ? trim((string) $session['description']) : null,
                ];
            }

            $questions = [];
            foreach ($day['questions'] ?? [] as $questionRow) {
                $questionText = trim((string) ($questionRow['question'] ?? ''));
                if ($questionText === '') {
                    continue;
                }

                $options = [];
                foreach ($questionRow['options'] ?? [] as $optionRow) {
                    $label = trim((string) ($optionRow['label'] ?? ''));
                    if ($label === '') {
                        continue;
                    }

                    $options[] = [
                        'sort_order' => count($options) + 1,
                        'label' => $label,
                    ];
                }

                if (count($options) < 2) {
                    throw ValidationException::withMessages([
                        'days' => 'Each questionnaire question needs at least 2 selectable answers.',
                    ]);
                }

                $questions[] = [
                    'sort_order' => count($questions) + 1,
                    'question' => $questionText,
                    'options' => $options,
                ];
            }

            if ($sessions === [] && $questions === []) {
                continue;
            }

            if ($sessions === []) {
                throw ValidationException::withMessages([
                    'days' => 'Each event day needs at least one session.',
                ]);
            }

            $dayNumber = (int) ($day['day_number'] ?? ($index + 1));
            if ($dayNumber < 1) {
                $dayNumber = count($days) + 1;
            }

            $days[] = [
                'sort_order' => count($days) + 1,
                'day_number' => $dayNumber,
                'description' => filled($day['description'] ?? null) ? trim((string) $day['description']) : null,
                'sessions' => $sessions,
                'questions' => $questions,
            ];
        }

        return [
            'event' => [
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'method' => $data['method'],
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'],
                'start_time' => $data['start_time'],
                'end_time' => $data['end_time'],
                'status' => $data['status'],
            ],
            'venues' => $venues,
            'days' => $days,
        ];
    }

    /**
     * @param  list<array<string, mixed>>  $venues
     */
    private function syncVenues(Event $event, array $venues): void
    {
        foreach ($venues as $venue) {
            $event->venues()->create($venue);
        }
    }

    /**
     * @param  list<array{sort_order: int, day_number: int, description: ?string, sessions: list<array<string, mixed>>, questions: list<array<string, mixed>>}>  $days
     */
    private function syncDays(Event $event, array $days): void
    {
        foreach ($days as $dayData) {
            $sessions = $dayData['sessions'];
            $questions = $dayData['questions'];
            unset($dayData['sessions'], $dayData['questions']);

            $day = $event->days()->create($dayData);

            foreach ($sessions as $session) {
                $day->sessions()->create($session);
            }

            foreach ($questions as $questionData) {
                $options = $questionData['options'];
                unset($questionData['options']);

                $question = $day->questions()->create($questionData);

                foreach ($options as $option) {
                    $question->options()->create($option);
                }
            }
        }
    }

    private function syncInvitationFiles(Event $event, Request $request): void
    {
        $updates = [];

        if ($request->boolean('remove_invitation_letter') && $event->invitation_letter_path) {
            Storage::disk('public')->delete($event->invitation_letter_path);
            $updates['invitation_letter_path'] = null;
        }

        if ($request->boolean('remove_invitation_card') && $event->invitation_card_path) {
            Storage::disk('public')->delete($event->invitation_card_path);
            $updates['invitation_card_path'] = null;
        }

        if ($request->hasFile('invitation_letter')) {
            /** @var UploadedFile $file */
            $file = $request->file('invitation_letter');
            if ($event->invitation_letter_path) {
                Storage::disk('public')->delete($event->invitation_letter_path);
            }
            $updates['invitation_letter_path'] = $file->storeAs(
                EventInvitationPdf::storageDirectory($event),
                'letter.pdf',
                'public'
            );
            if (! $event->invitation_letter_settings) {
                $updates['invitation_letter_settings'] = EventInvitationPdf::defaultLetterSettings();
            }
        }

        if ($request->hasFile('invitation_card')) {
            /** @var UploadedFile $file */
            $file = $request->file('invitation_card');
            if ($event->invitation_card_path) {
                Storage::disk('public')->delete($event->invitation_card_path);
            }
            $updates['invitation_card_path'] = $file->storeAs(
                EventInvitationPdf::storageDirectory($event),
                'card.pdf',
                'public'
            );
            if (! $event->invitation_card_settings) {
                $updates['invitation_card_settings'] = EventInvitationPdf::defaultCardSettings();
            }
        }

        if ($updates !== []) {
            $event->update($updates);
        }
    }

    private function deleteInvitationFiles(Event $event): void
    {
        if ($event->invitation_letter_path) {
            Storage::disk('public')->delete($event->invitation_letter_path);
        }
        if ($event->invitation_card_path) {
            Storage::disk('public')->delete($event->invitation_card_path);
        }

        Storage::disk('public')->deleteDirectory(EventInvitationPdf::storageDirectory($event));
    }
}
