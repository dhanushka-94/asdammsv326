@php
    $eventModel = $event ?? null;
    $existingVenues = old('venues');

    if ($existingVenues === null) {
        $existingVenues = $eventModel?->venues?->values()->all() ?? [[]];
    }

    if ($existingVenues instanceof \Illuminate\Support\Collection) {
        $existingVenues = $existingVenues->all();
    }

    if ($existingVenues === []) {
        $existingVenues = [[]];
    }

    $existingVenues = array_values($existingVenues);

    $normalizeVenue = function ($venue) {
        if (is_array($venue)) {
            return [
                'name' => $venue['name'] ?? '',
                'floor' => $venue['floor'] ?? '',
                'hall_room' => $venue['hall_room'] ?? '',
                'description' => $venue['description'] ?? '',
                'latitude' => $venue['latitude'] ?? '',
                'longitude' => $venue['longitude'] ?? '',
                'maps_url' => $venue['maps_url'] ?? '',
            ];
        }

        return [
            'name' => $venue->name ?? '',
            'floor' => $venue->floor ?? '',
            'hall_room' => $venue->hall_room ?? '',
            'description' => $venue->description ?? '',
            'latitude' => $venue->latitude ?? '',
            'longitude' => $venue->longitude ?? '',
            'maps_url' => $venue->maps_url ?? '',
        ];
    };

    $venueModels = array_map($normalizeVenue, $existingVenues);

    $existingDays = old('days');

    if ($existingDays === null) {
        $existingDays = $eventModel?->days?->loadMissing('sessions')->values()->all() ?? [[]];
    }

    if ($existingDays instanceof \Illuminate\Support\Collection) {
        $existingDays = $existingDays->all();
    }

    if ($existingDays === []) {
        $existingDays = [[]];
    }

    $existingDays = array_slice(array_values($existingDays), 0, 14);

    $normalizeDay = function ($day, $index) {
        $normalizeQuestion = function ($question) {
            if (is_array($question)) {
                $options = $question['options'] ?? [['label' => ''], ['label' => '']];
                if (count($options) < 2) {
                    $options = array_pad($options, 2, ['label' => '']);
                }

                return [
                    'question' => $question['question'] ?? '',
                    'options' => array_values(array_map(function ($option) {
                        if (is_array($option)) {
                            return ['label' => $option['label'] ?? ''];
                        }

                        return ['label' => $option->label ?? ''];
                    }, $options)),
                ];
            }

            $options = $question->options ?? collect();
            if ($options->count() < 2) {
                $options = collect([(object) ['label' => ''], (object) ['label' => '']]);
            }

            return [
                'question' => $question->question ?? '',
                'options' => $options->map(fn ($option) => [
                    'label' => $option->label ?? '',
                ])->values()->all(),
            ];
        };

        if (is_array($day)) {
            $sessions = $day['sessions'] ?? [['name' => '', 'description' => '']];
            if ($sessions === []) {
                $sessions = [['name' => '', 'description' => '']];
            }

            $questions = $day['questions'] ?? [];

            return [
                'day_number' => $day['day_number'] ?? ($index + 1),
                'description' => $day['description'] ?? '',
                'sessions' => array_values(array_map(function ($session) {
                    if (is_array($session)) {
                        return [
                            'name' => $session['name'] ?? '',
                            'description' => $session['description'] ?? '',
                        ];
                    }

                    return [
                        'name' => $session->name ?? '',
                        'description' => $session->description ?? '',
                    ];
                }, $sessions)),
                'questions' => array_values(array_map($normalizeQuestion, $questions)),
            ];
        }

        $sessions = $day->sessions ?? collect();
        if ($sessions->isEmpty()) {
            $sessions = collect([(object) ['name' => '', 'description' => '']]);
        }

        $questions = $day->questions ?? collect();

        return [
            'day_number' => $day->day_number ?? ($index + 1),
            'description' => $day->description ?? '',
            'sessions' => $sessions->map(fn ($session) => [
                'name' => $session->name ?? '',
                'description' => $session->description ?? '',
            ])->values()->all(),
            'questions' => $questions->map($normalizeQuestion)->values()->all(),
        ];
    };

    $dayModels = [];
    foreach ($existingDays as $index => $day) {
        $dayModels[] = $normalizeDay($day, $index);
    }

    $currentMethod = old('method', $eventModel->method ?? 'physical');
    $methodPhysical = old('method_physical', in_array($currentMethod, ['physical', 'both'], true));
    $methodOnline = old('method_online', in_array($currentMethod, ['online', 'both'], true));

    $timeValue = function ($value) {
        if (! $value) {
            return '';
        }
        $value = (string) $value;

        return strlen($value) >= 5 ? substr($value, 0, 5) : $value;
    };
@endphp

<div class="space-y-6" id="event-form-root">
    <div class="grid gap-5 sm:grid-cols-2">
        <div class="sm:col-span-2">
            <label for="name" class="form-label">Event name</label>
            <input id="name" type="text" name="name" value="{{ old('name', $eventModel->name ?? '') }}" required class="form-input" placeholder="Event name">
        </div>

        <div class="sm:col-span-2">
            <label for="description" class="form-label">Description</label>
            <textarea id="description" name="description" rows="4" class="form-input" placeholder="What is this event about?">{{ old('description', $eventModel->description ?? '') }}</textarea>
        </div>

        <div class="sm:col-span-2">
            <p class="form-label">Method</p>
            <div class="mt-2 flex flex-wrap gap-4">
                <label class="flex items-center gap-2 text-sm text-ink">
                    <input type="checkbox" name="method_physical" value="1" @checked($methodPhysical) class="h-4 w-4 rounded border-slate-300 text-brand-green focus:ring-brand-green">
                    Physical
                </label>
                <label class="flex items-center gap-2 text-sm text-ink">
                    <input type="checkbox" name="method_online" value="1" @checked($methodOnline) class="h-4 w-4 rounded border-slate-300 text-brand-green focus:ring-brand-green">
                    Online
                </label>
            </div>
            <p class="mt-1 text-xs text-muted">Select one or both.</p>
            @error('method')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex items-end sm:col-span-2">
            <div class="w-full sm:max-w-xs">
                <label for="status" class="form-label">Status</label>
                <select id="status" name="status" required class="form-select">
                    <option value="active" @selected(old('status', $eventModel->status ?? 'active') === 'active')>Active</option>
                    <option value="inactive" @selected(old('status', $eventModel->status ?? '') === 'inactive')>Inactive</option>
                </select>
                <p class="mt-1 text-xs text-muted">Active events appear in the member pool. You can schedule future dates — members can enroll before the event ends.</p>
            </div>
        </div>

        <div>
            <label for="start_date" class="form-label">Start date</label>
            <input id="start_date" type="date" name="start_date" value="{{ old('start_date', optional($eventModel?->start_date)->format('Y-m-d')) }}" required class="form-input">
        </div>
        <div>
            <label for="end_date" class="form-label">End date</label>
            <input id="end_date" type="date" name="end_date" value="{{ old('end_date', optional($eventModel?->end_date)->format('Y-m-d')) }}" required class="form-input">
        </div>
        <div>
            <label for="start_time" class="form-label">Start time</label>
            <input id="start_time" type="time" name="start_time" value="{{ old('start_time', $timeValue($eventModel->start_time ?? null)) }}" required class="form-input">
        </div>
        <div>
            <label for="end_time" class="form-label">End time</label>
            <input id="end_time" type="time" name="end_time" value="{{ old('end_time', $timeValue($eventModel->end_time ?? null)) }}" required class="form-input">
        </div>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-surface/50 p-4 sm:p-5">
        <div class="mb-4">
            <h3 class="font-display text-base font-bold text-ink">Event invitations</h3>
            <p class="mt-1 text-sm text-muted">
                Upload PDF templates for the invitation letter and invitation card. Active members can download personalized PDFs (letter: name + address, card: name).
            </p>
        </div>

        <div class="grid gap-5 sm:grid-cols-2">
            <div>
                <label for="invitation_letter" class="form-label">Invitation letter (PDF)</label>
                <input id="invitation_letter" type="file" name="invitation_letter" accept="application/pdf,.pdf" class="form-input">
                @error('invitation_letter')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                @if ($eventModel?->hasInvitationLetter())
                    <div class="mt-2 flex flex-wrap items-center gap-3 text-sm">
                        <a href="{{ asset('storage/'.$eventModel->invitation_letter_path) }}" target="_blank" rel="noopener" class="font-semibold text-brand-blue hover:underline">
                            Current letter PDF
                        </a>
                        <label class="inline-flex items-center gap-2 text-muted">
                            <input type="checkbox" name="remove_invitation_letter" value="1" class="h-4 w-4 rounded border-slate-300 text-red-600 focus:ring-red-500">
                            Remove
                        </label>
                    </div>
                @endif
            </div>

            <div>
                <label for="invitation_card" class="form-label">Invitation card (PDF)</label>
                <input id="invitation_card" type="file" name="invitation_card" accept="application/pdf,.pdf" class="form-input">
                @error('invitation_card')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                @if ($eventModel?->hasInvitationCard())
                    <div class="mt-2 flex flex-wrap items-center gap-3 text-sm">
                        <a href="{{ asset('storage/'.$eventModel->invitation_card_path) }}" target="_blank" rel="noopener" class="font-semibold text-brand-blue hover:underline">
                            Current card PDF
                        </a>
                        <label class="inline-flex items-center gap-2 text-muted">
                            <input type="checkbox" name="remove_invitation_card" value="1" class="h-4 w-4 rounded border-slate-300 text-red-600 focus:ring-red-500">
                            Remove
                        </label>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-surface/50 p-4 sm:p-5">
        <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h3 class="font-display text-base font-bold text-ink">Event days <span class="font-normal text-muted">(up to 14)</span></h3>
                <p class="mt-1 text-sm text-muted">
                    Add Day 1, Day 2, … Each day can have multiple sessions. Venues are managed separately below.
                </p>
                @error('days')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <button id="add-day-btn" type="button" class="btn-outline shrink-0">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Add day
            </button>
        </div>

        <div id="days-list" class="space-y-4" data-max-days="14">
            @foreach ($dayModels as $index => $day)
                <div class="day-block rounded-xl border border-slate-200 bg-white p-4" data-day-index="{{ $index }}">
                    <div class="mb-3 flex items-center justify-between gap-3">
                        <p class="text-xs font-semibold uppercase tracking-wide text-muted">Day block <span class="day-block-number">{{ $index + 1 }}</span></p>
                        <button type="button" class="remove-day-btn btn-ghost px-2 py-1 text-red-600 {{ $index === 0 && count($dayModels) === 1 ? 'hidden' : '' }}">Remove day</button>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="form-label">Day number</label>
                            <div class="flex items-center gap-2">
                                <span class="text-sm font-semibold text-muted">Day</span>
                                <input type="number" min="1" max="99" name="days[{{ $index }}][day_number]" value="{{ $day['day_number'] }}" class="form-input day-number-input" placeholder="1">
                            </div>
                        </div>
                        <div class="sm:col-span-2">
                            <label class="form-label">Day notes <span class="font-normal text-muted">(optional)</span></label>
                            <textarea name="days[{{ $index }}][description]" rows="2" class="form-input" placeholder="Optional overview for this day…">{{ $day['description'] }}</textarea>
                        </div>
                    </div>

                    <div class="mt-4 rounded-xl border border-brand-green/20 bg-brand-green-soft/30 p-3 sm:p-4">
                        <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
                            <p class="text-xs font-semibold uppercase tracking-wide text-brand-green">Sessions for this day</p>
                            <button type="button" class="add-session-btn btn-outline px-2.5 py-1.5 text-xs">Add session</button>
                        </div>
                        <div class="sessions-list space-y-3">
                            @foreach ($day['sessions'] as $sessionIndex => $session)
                                <div class="session-block rounded-lg border border-white bg-white p-3" data-session-index="{{ $sessionIndex }}">
                                    <div class="mb-2 flex items-center justify-between gap-2">
                                        <p class="text-xs font-semibold text-muted">Session <span class="session-number">{{ $sessionIndex + 1 }}</span></p>
                                        <button type="button" class="remove-session-btn btn-ghost px-2 py-1 text-xs text-red-600 {{ $sessionIndex === 0 && count($day['sessions']) === 1 ? 'hidden' : '' }}">Remove</button>
                                    </div>
                                    <div class="grid gap-3">
                                        <div>
                                            <label class="form-label">Session name</label>
                                            <input type="text" name="days[{{ $index }}][sessions][{{ $sessionIndex }}][name]" value="{{ $session['name'] }}" class="form-input" placeholder="e.g. Opening ceremony">
                                        </div>
                                        <div>
                                            <label class="form-label">Session notes <span class="font-normal text-muted">(optional)</span></label>
                                            <textarea name="days[{{ $index }}][sessions][{{ $sessionIndex }}][description]" rows="2" class="form-input" placeholder="Agenda for this session…">{{ $session['description'] }}</textarea>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="mt-4 rounded-xl border border-brand-blue/20 bg-brand-blue-soft/30 p-3 sm:p-4">
                        <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wide text-brand-blue">Questionnaire for this day</p>
                                <p class="mt-1 text-[11px] text-muted">Optional. Members answer these when enrolling.</p>
                            </div>
                            <button type="button" class="add-question-btn btn-outline px-2.5 py-1.5 text-xs">Add question</button>
                        </div>
                        <div class="questions-list space-y-3">
                            @foreach ($day['questions'] as $questionIndex => $question)
                                <div class="question-block rounded-lg border border-white bg-white p-3" data-question-index="{{ $questionIndex }}">
                                    <div class="mb-2 flex items-center justify-between gap-2">
                                        <p class="text-xs font-semibold text-muted">Question <span class="question-number">{{ $questionIndex + 1 }}</span></p>
                                        <button type="button" class="remove-question-btn btn-ghost px-2 py-1 text-xs text-red-600">Remove</button>
                                    </div>
                                    <div class="space-y-3">
                                        <div>
                                            <label class="form-label">Question</label>
                                            <input type="text" name="days[{{ $index }}][questions][{{ $questionIndex }}][question]" value="{{ $question['question'] }}" class="form-input" placeholder="e.g. Which lunch package do you prefer?">
                                        </div>
                                        <div>
                                            <div class="mb-2 flex items-center justify-between gap-2">
                                                <p class="text-xs font-semibold text-muted">Selectable answers</p>
                                                <button type="button" class="add-option-btn btn-ghost px-2 py-1 text-xs text-brand-blue">Add answer</button>
                                            </div>
                                            <div class="options-list space-y-2">
                                                @foreach ($question['options'] as $optionIndex => $option)
                                                    <div class="option-block flex items-center gap-2" data-option-index="{{ $optionIndex }}">
                                                        <input type="text" name="days[{{ $index }}][questions][{{ $questionIndex }}][options][{{ $optionIndex }}][label]" value="{{ $option['label'] }}" class="form-input" placeholder="Answer option">
                                                        <button type="button" class="remove-option-btn btn-ghost px-2 py-1 text-xs text-red-600 {{ count($question['options']) <= 2 ? 'hidden' : '' }}">×</button>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-surface/50 p-4 sm:p-5">
        <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h3 class="font-display text-base font-bold text-ink">Venues</h3>
                <p class="mt-1 text-sm text-muted">
                    Event venues. Add as many as needed. Physical events need at least one venue.
                </p>
                @error('venues')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <button id="add-venue-btn" type="button" class="btn-outline shrink-0">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Add venue
            </button>
        </div>

        <div id="venues-list" class="space-y-4">
            @foreach ($venueModels as $index => $venue)
                <div class="venue-block rounded-xl border border-slate-200 bg-white p-4" data-venue-index="{{ $index }}">
                    <div class="mb-3 flex items-center justify-between gap-3">
                        <p class="text-xs font-semibold uppercase tracking-wide text-muted">Venue <span class="venue-number">{{ $index + 1 }}</span></p>
                        <button type="button" class="remove-venue-btn btn-ghost px-2 py-1 text-red-600 {{ $index === 0 && count($venueModels) === 1 ? 'hidden' : '' }}">Remove</button>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <label class="form-label">Venue name</label>
                            <input type="text" name="venues[{{ $index }}][name]" value="{{ $venue['name'] }}" class="form-input" placeholder="e.g. BMICH">
                        </div>
                        <div>
                            <label class="form-label">Floor</label>
                            <input type="text" name="venues[{{ $index }}][floor]" value="{{ $venue['floor'] }}" class="form-input" placeholder="e.g. 2">
                        </div>
                        <div>
                            <label class="form-label">Hall / Room number</label>
                            <input type="text" name="venues[{{ $index }}][hall_room]" value="{{ $venue['hall_room'] }}" class="form-input" placeholder="e.g. Hall A / Room 12">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="form-label">Venue description</label>
                            <textarea name="venues[{{ $index }}][description]" rows="2" class="form-input" placeholder="Directions, entrance notes, seating info…">{{ $venue['description'] }}</textarea>
                        </div>
                        <div>
                            <label class="form-label">Latitude</label>
                            <input type="text" name="venues[{{ $index }}][latitude]" value="{{ $venue['latitude'] }}" class="form-input" placeholder="6.927079" inputmode="decimal">
                        </div>
                        <div>
                            <label class="form-label">Longitude</label>
                            <input type="text" name="venues[{{ $index }}][longitude]" value="{{ $venue['longitude'] }}" class="form-input" placeholder="79.861244" inputmode="decimal">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="form-label">Google Maps link <span class="font-normal text-muted">(optional)</span></label>
                            <input type="url" name="venues[{{ $index }}][maps_url]" value="{{ $venue['maps_url'] }}" class="form-input" placeholder="https://maps.google.com/...">
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>

<template id="day-block-template">
    <div class="day-block rounded-xl border border-slate-200 bg-white p-4" data-day-index="__INDEX__">
        <div class="mb-3 flex items-center justify-between gap-3">
            <p class="text-xs font-semibold uppercase tracking-wide text-muted">Day block <span class="day-block-number">__NUMBER__</span></p>
            <button type="button" class="remove-day-btn btn-ghost px-2 py-1 text-red-600">Remove day</button>
        </div>
        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label class="form-label">Day number</label>
                <div class="flex items-center gap-2">
                    <span class="text-sm font-semibold text-muted">Day</span>
                    <input type="number" min="1" max="99" name="days[__INDEX__][day_number]" value="__NUMBER__" class="form-input day-number-input" placeholder="1">
                </div>
            </div>
            <div class="sm:col-span-2">
                <label class="form-label">Day notes <span class="font-normal text-muted">(optional)</span></label>
                <textarea name="days[__INDEX__][description]" rows="2" class="form-input" placeholder="Optional overview for this day…"></textarea>
            </div>
        </div>
        <div class="mt-4 rounded-xl border border-brand-green/20 bg-brand-green-soft/30 p-3 sm:p-4">
            <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
                <p class="text-xs font-semibold uppercase tracking-wide text-brand-green">Sessions for this day</p>
                <button type="button" class="add-session-btn btn-outline px-2.5 py-1.5 text-xs">Add session</button>
            </div>
            <div class="sessions-list space-y-3">
                <div class="session-block rounded-lg border border-white bg-white p-3" data-session-index="0">
                    <div class="mb-2 flex items-center justify-between gap-2">
                        <p class="text-xs font-semibold text-muted">Session <span class="session-number">1</span></p>
                        <button type="button" class="remove-session-btn btn-ghost px-2 py-1 text-xs text-red-600 hidden">Remove</button>
                    </div>
                    <div class="grid gap-3">
                        <div>
                            <label class="form-label">Session name</label>
                            <input type="text" name="days[__INDEX__][sessions][0][name]" class="form-input" placeholder="e.g. Opening ceremony">
                        </div>
                        <div>
                            <label class="form-label">Session notes <span class="font-normal text-muted">(optional)</span></label>
                            <textarea name="days[__INDEX__][sessions][0][description]" rows="2" class="form-input" placeholder="Agenda for this session…"></textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="mt-4 rounded-xl border border-brand-blue/20 bg-brand-blue-soft/30 p-3 sm:p-4">
            <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-brand-blue">Questionnaire for this day</p>
                    <p class="mt-1 text-[11px] text-muted">Optional. Members answer these when enrolling.</p>
                </div>
                <button type="button" class="add-question-btn btn-outline px-2.5 py-1.5 text-xs">Add question</button>
            </div>
            <div class="questions-list space-y-3"></div>
        </div>
    </div>
</template>

<template id="session-block-template">
    <div class="session-block rounded-lg border border-white bg-white p-3" data-session-index="__SESSION_INDEX__">
        <div class="mb-2 flex items-center justify-between gap-2">
            <p class="text-xs font-semibold text-muted">Session <span class="session-number">__SESSION_NUMBER__</span></p>
            <button type="button" class="remove-session-btn btn-ghost px-2 py-1 text-xs text-red-600">Remove</button>
        </div>
        <div class="grid gap-3">
            <div>
                <label class="form-label">Session name</label>
                <input type="text" name="days[__DAY_INDEX__][sessions][__SESSION_INDEX__][name]" class="form-input" placeholder="e.g. Opening ceremony">
            </div>
            <div>
                <label class="form-label">Session notes <span class="font-normal text-muted">(optional)</span></label>
                <textarea name="days[__DAY_INDEX__][sessions][__SESSION_INDEX__][description]" rows="2" class="form-input" placeholder="Agenda for this session…"></textarea>
            </div>
        </div>
    </div>
</template>

<template id="question-block-template">
    <div class="question-block rounded-lg border border-white bg-white p-3" data-question-index="__QUESTION_INDEX__">
        <div class="mb-2 flex items-center justify-between gap-2">
            <p class="text-xs font-semibold text-muted">Question <span class="question-number">__QUESTION_NUMBER__</span></p>
            <button type="button" class="remove-question-btn btn-ghost px-2 py-1 text-xs text-red-600">Remove</button>
        </div>
        <div class="space-y-3">
            <div>
                <label class="form-label">Question</label>
                <input type="text" name="days[__DAY_INDEX__][questions][__QUESTION_INDEX__][question]" class="form-input" placeholder="e.g. Which lunch package do you prefer?">
            </div>
            <div>
                <div class="mb-2 flex items-center justify-between gap-2">
                    <p class="text-xs font-semibold text-muted">Selectable answers</p>
                    <button type="button" class="add-option-btn btn-ghost px-2 py-1 text-xs text-brand-blue">Add answer</button>
                </div>
                <div class="options-list space-y-2">
                    <div class="option-block flex items-center gap-2" data-option-index="0">
                        <input type="text" name="days[__DAY_INDEX__][questions][__QUESTION_INDEX__][options][0][label]" class="form-input" placeholder="Answer option">
                        <button type="button" class="remove-option-btn btn-ghost px-2 py-1 text-xs text-red-600 hidden">×</button>
                    </div>
                    <div class="option-block flex items-center gap-2" data-option-index="1">
                        <input type="text" name="days[__DAY_INDEX__][questions][__QUESTION_INDEX__][options][1][label]" class="form-input" placeholder="Answer option">
                        <button type="button" class="remove-option-btn btn-ghost px-2 py-1 text-xs text-red-600 hidden">×</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<template id="option-block-template">
    <div class="option-block flex items-center gap-2" data-option-index="__OPTION_INDEX__">
        <input type="text" name="days[__DAY_INDEX__][questions][__QUESTION_INDEX__][options][__OPTION_INDEX__][label]" class="form-input" placeholder="Answer option">
        <button type="button" class="remove-option-btn btn-ghost px-2 py-1 text-xs text-red-600">×</button>
    </div>
</template>

<template id="venue-block-template">
    <div class="venue-block rounded-xl border border-slate-200 bg-white p-4" data-venue-index="__INDEX__">
        <div class="mb-3 flex items-center justify-between gap-3">
            <p class="text-xs font-semibold uppercase tracking-wide text-muted">Venue <span class="venue-number">__NUMBER__</span></p>
            <button type="button" class="remove-venue-btn btn-ghost px-2 py-1 text-red-600">Remove</button>
        </div>
        <div class="grid gap-4 sm:grid-cols-2">
            <div class="sm:col-span-2">
                <label class="form-label">Venue name</label>
                <input type="text" name="venues[__INDEX__][name]" class="form-input" placeholder="e.g. BMICH">
            </div>
            <div>
                <label class="form-label">Floor</label>
                <input type="text" name="venues[__INDEX__][floor]" class="form-input" placeholder="e.g. 2">
            </div>
            <div>
                <label class="form-label">Hall / Room number</label>
                <input type="text" name="venues[__INDEX__][hall_room]" class="form-input" placeholder="e.g. Hall A / Room 12">
            </div>
            <div class="sm:col-span-2">
                <label class="form-label">Venue description</label>
                <textarea name="venues[__INDEX__][description]" rows="2" class="form-input" placeholder="Directions, entrance notes, seating info…"></textarea>
            </div>
            <div>
                <label class="form-label">Latitude</label>
                <input type="text" name="venues[__INDEX__][latitude]" class="form-input" placeholder="6.927079" inputmode="decimal">
            </div>
            <div>
                <label class="form-label">Longitude</label>
                <input type="text" name="venues[__INDEX__][longitude]" class="form-input" placeholder="79.861244" inputmode="decimal">
            </div>
            <div class="sm:col-span-2">
                <label class="form-label">Google Maps link <span class="font-normal text-muted">(optional)</span></label>
                <input type="url" name="venues[__INDEX__][maps_url]" class="form-input" placeholder="https://maps.google.com/...">
            </div>
        </div>
    </div>
</template>
