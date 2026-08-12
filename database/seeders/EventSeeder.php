<?php

namespace Database\Seeders;

use App\Models\CheckInItem;
use App\Models\Event;
use App\Models\EventAttendance;
use App\Models\EventEnrollment;
use App\Models\EventEnrollmentAnswer;
use App\Models\Member;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EventSeeder extends Seeder
{
    public function run(): void
    {
        $adminId = User::query()->where('email', 'admin@asdamms.com')->value('id');
        $receptionIds = User::query()
            ->whereIn('email', ['reception@asdamms.com', 'reception2@asdamms.com'])
            ->pluck('id')
            ->all();

        $main = Event::query()->create([
            'name' => 'ASDA Annual Symposium 2026',
            'description' => 'The Annual Symposium of the Department of Agriculture (ASDA) 2026 — keynotes, technical sessions, posters, and networking across physical and online channels.',
            'method' => 'both',
            'start_date' => '2026-09-15',
            'end_date' => '2026-09-17',
            'start_time' => '08:30',
            'end_time' => '17:00',
            'status' => 'active',
        ]);

        $main->venues()->createMany([
            [
                'sort_order' => 1,
                'name' => 'BMICH',
                'floor' => 'Ground',
                'hall_room' => 'Main Hall',
                'description' => 'Main conference venue for opening ceremony and keynotes.',
                'latitude' => 6.9030000,
                'longitude' => 79.8720000,
                'maps_url' => 'https://www.google.com/maps?q=6.903,79.872',
            ],
            [
                'sort_order' => 2,
                'name' => 'Department of Agriculture Auditorium',
                'floor' => '1',
                'hall_room' => 'Auditorium A',
                'description' => 'Parallel technical sessions and panels.',
                'latitude' => 7.2730000,
                'longitude' => 80.5980000,
                'maps_url' => null,
            ],
        ]);

        $day1 = $main->days()->create([
            'sort_order' => 1,
            'day_number' => 1,
            'description' => 'Inauguration day.',
        ]);
        $day1->sessions()->createMany([
            ['sort_order' => 1, 'name' => 'Opening ceremony', 'description' => 'Welcome address and inauguration.'],
            ['sort_order' => 2, 'name' => 'Keynote presentations', 'description' => 'Invited keynote speakers.'],
        ]);
        $q1 = $day1->questions()->create([
            'sort_order' => 1,
            'question' => 'Which lunch package do you prefer on Day 1?',
        ]);
        $q1->options()->createMany([
            ['sort_order' => 1, 'label' => 'Vegetarian'],
            ['sort_order' => 2, 'label' => 'Non-vegetarian'],
            ['sort_order' => 3, 'label' => 'No lunch required'],
        ]);
        $q2 = $day1->questions()->create([
            'sort_order' => 2,
            'question' => 'Do you need a printed programme booklet?',
        ]);
        $q2->options()->createMany([
            ['sort_order' => 1, 'label' => 'Yes'],
            ['sort_order' => 2, 'label' => 'No — digital only'],
        ]);

        $day2 = $main->days()->create([
            'sort_order' => 2,
            'day_number' => 2,
            'description' => 'Technical programme day.',
        ]);
        $day2->sessions()->createMany([
            ['sort_order' => 1, 'name' => 'Technical papers', 'description' => 'Oral paper presentations.'],
            ['sort_order' => 2, 'name' => 'Poster session', 'description' => 'Poster exhibition and discussion.'],
            ['sort_order' => 3, 'name' => 'Panel discussions', 'description' => 'Thematic panels.'],
        ]);
        $q3 = $day2->questions()->create([
            'sort_order' => 1,
            'question' => 'Which parallel track will you attend on Day 2 morning?',
        ]);
        $q3->options()->createMany([
            ['sort_order' => 1, 'label' => 'Crop science'],
            ['sort_order' => 2, 'label' => 'Extension & training'],
            ['sort_order' => 3, 'label' => 'Policy & planning'],
            ['sort_order' => 4, 'label' => 'Online general stream'],
        ]);

        $day3 = $main->days()->create([
            'sort_order' => 3,
            'day_number' => 3,
            'description' => 'Showcase and closing.',
        ]);
        $day3->sessions()->createMany([
            ['sort_order' => 1, 'name' => 'Field showcase', 'description' => 'Innovation and field exhibits.'],
            ['sort_order' => 2, 'name' => 'Awards & closing ceremony', 'description' => 'Awards presentation and closing remarks.'],
        ]);
        $q4 = $day3->questions()->create([
            'sort_order' => 1,
            'question' => 'Will you join the field showcase visit?',
        ]);
        $q4->options()->createMany([
            ['sort_order' => 1, 'label' => 'Yes'],
            ['sort_order' => 2, 'label' => 'No'],
        ]);

        $workshop = Event::query()->create([
            'name' => 'ASDA Annual Symposium 2026 — Pre-Conference Workshop',
            'description' => 'Pre-conference capacity-building workshop on research communication, poster design, and field innovation.',
            'method' => 'physical',
            'start_date' => '2026-09-14',
            'end_date' => '2026-09-14',
            'start_time' => '09:00',
            'end_time' => '16:30',
            'status' => 'active',
        ]);

        $workshop->venues()->create([
            'sort_order' => 1,
            'name' => 'HORDI Conference Room',
            'floor' => '2',
            'hall_room' => 'Room 201',
            'description' => 'Hands-on workshop space with projection and breakout seating.',
            'latitude' => 7.2715000,
            'longitude' => 80.5995000,
            'maps_url' => 'https://www.google.com/maps?q=7.2715,80.5995',
        ]);

        $workshopDay = $workshop->days()->create([
            'sort_order' => 1,
            'day_number' => 1,
            'description' => 'Full-day capacity-building programme.',
        ]);
        $workshopDay->sessions()->createMany([
            ['sort_order' => 1, 'name' => 'Research communication', 'description' => 'Morning skill session.'],
            ['sort_order' => 2, 'name' => 'Poster design workshop', 'description' => 'Afternoon hands-on session.'],
        ]);
        $wq1 = $workshopDay->questions()->create([
            'sort_order' => 1,
            'question' => 'Which skill focus do you prefer?',
        ]);
        $wq1->options()->createMany([
            ['sort_order' => 1, 'label' => 'Oral presentation skills'],
            ['sort_order' => 2, 'label' => 'Poster design'],
            ['sort_order' => 3, 'label' => 'Both equally'],
        ]);

        $webinar = Event::query()->create([
            'name' => 'ASDA Digital Agriculture Webinar 2026',
            'description' => 'Online webinar on digital agriculture tools, advisory platforms, and farmer-facing apps.',
            'method' => 'online',
            'start_date' => '2026-08-20',
            'end_date' => '2026-08-20',
            'start_time' => '14:00',
            'end_time' => '16:00',
            'status' => 'active',
        ]);

        $webinarDay = $webinar->days()->create([
            'sort_order' => 1,
            'day_number' => 1,
            'description' => 'Single online session.',
        ]);
        $webinarDay->sessions()->create([
            'sort_order' => 1,
            'name' => 'Digital tools showcase',
            'description' => 'Live demos and Q&A.',
        ]);
        $vq1 = $webinarDay->questions()->create([
            'sort_order' => 1,
            'question' => 'What is your primary interest for this webinar?',
        ]);
        $vq1->options()->createMany([
            ['sort_order' => 1, 'label' => 'Advisory platforms'],
            ['sort_order' => 2, 'label' => 'Remote sensing / GIS'],
            ['sort_order' => 3, 'label' => 'Farmer mobile apps'],
            ['sort_order' => 4, 'label' => 'General overview'],
        ]);

        if ($receptionIds !== []) {
            $main->receptionUsers()->sync($receptionIds);
            $workshop->receptionUsers()->sync([$receptionIds[0]]);
        }

        $main->load(['days.questions.options', 'venues']);
        $workshop->load(['days.questions.options', 'venues']);
        $webinar->load('days.questions.options');

        $memberIds = Member::query()
            ->where('registration_status', 'approved')
            ->where('status', 'active')
            ->orderBy('id')
            ->pluck('id');

        // Invite most active approved members; leave a couple uninvited for testing.
        $inviteIds = $memberIds->take(max(0, $memberIds->count() - 2))->all();
        $invitePayload = [];
        $now = now();
        foreach ($inviteIds as $memberId) {
            $invitePayload[$memberId] = [
                'invited_by' => $adminId,
                'invited_at' => $now->copy()->subDays(3),
            ];
        }
        if ($invitePayload !== []) {
            $main->invitedMembers()->attach($invitePayload);
            $workshop->invitedMembers()->attach(array_slice($invitePayload, 0, 8, true));
            $webinar->invitedMembers()->attach(array_slice($invitePayload, 0, 10, true));
        }

        DB::transaction(function () use ($main, $workshop, $webinar, $inviteIds, $adminId): void {
            foreach ($inviteIds as $index => $memberId) {
                $mode = $index % 3 === 0 ? 'online' : 'physical';
                $isKickedSample = $index === count($inviteIds) - 1 && count($inviteIds) > 3;

                $enrollment = EventEnrollment::query()->create([
                    'event_id' => $main->id,
                    'member_id' => $memberId,
                    'enrolled_at' => now()->subDays(($index % 10) + 1),
                    'participation_mode' => $mode,
                    'kicked_at' => $isKickedSample ? now()->subDays(2) : null,
                    'kick_reason' => $isKickedSample ? 'Sample removal for testing.' : null,
                    'kicked_by' => $isKickedSample ? $adminId : null,
                ]);

                if (! $isKickedSample) {
                    $this->seedAnswers($enrollment, $main, $index);
                }

                if ($index < 8) {
                    $workshopEnrollment = EventEnrollment::query()->create([
                        'event_id' => $workshop->id,
                        'member_id' => $memberId,
                        'enrolled_at' => now()->subDays(($index % 7) + 1),
                        'participation_mode' => 'physical',
                    ]);
                    $this->seedAnswers($workshopEnrollment, $workshop, $index);
                }

                if ($index < 10) {
                    $webinarEnrollment = EventEnrollment::query()->create([
                        'event_id' => $webinar->id,
                        'member_id' => $memberId,
                        'enrolled_at' => now()->subDays(($index % 5) + 1),
                        'participation_mode' => 'online',
                    ]);
                    $this->seedAnswers($webinarEnrollment, $webinar, $index);
                }
            }
        });

        $this->seedCheckIns($main, $receptionIds, $adminId, 12);
        $this->seedCheckIns($workshop, $receptionIds, $adminId, 5);

        $this->command?->info('Seeded 3 events with venues, days, sessions, questionnaires, invites, enrollments, and check-ins.');
    }

    /**
     * @param  list<int>  $receptionIds
     */
    private function seedCheckIns(
        Event $event,
        array $receptionIds,
        ?int $adminId,
        int $limit = 450,
    ): void {
        $event->loadMissing(['days', 'venues']);
        $days = $event->days;
        $venues = $event->venues;
        $itemIds = CheckInItem::query()->ordered()->pluck('id')->all();

        $enrollments = EventEnrollment::query()
            ->active()
            ->where('event_id', $event->id)
            ->orderBy('id')
            ->limit($limit)
            ->get(['id', 'member_id']);

        if ($days->isEmpty() || $enrollments->isEmpty()) {
            return;
        }

        $officerPool = $receptionIds !== [] ? $receptionIds : array_values(array_filter([$adminId]));

        if ($officerPool === []) {
            return;
        }

        foreach ($enrollments as $index => $enrollment) {
            $day = $days[$index % $days->count()];
            $venue = $venues->isNotEmpty() ? $venues[$index % $venues->count()] : null;
            $officerId = $officerPool[$index % count($officerPool)];

            $attendance = EventAttendance::query()->create([
                'event_id' => $event->id,
                'event_day_id' => $day->id,
                'event_venue_id' => $venue?->id,
                'member_id' => $enrollment->member_id,
                'event_enrollment_id' => $enrollment->id,
                'checked_in_at' => now()->subDays(($index % 5))->subMinutes(($index % 180) + 5),
                'checked_in_by' => $officerId,
            ]);

            if ($itemIds !== []) {
                $give = collect($itemIds)
                    ->take(($index % min(4, count($itemIds))) + 1)
                    ->mapWithKeys(fn ($id) => [$id => ['given_at' => now()->subMinutes(($index % 60) + 1)]])
                    ->all();
                $attendance->checkInItems()->sync($give);
            }
        }
    }

    private function seedAnswers(EventEnrollment $enrollment, Event $event, int $index): void
    {
        foreach ($event->days as $day) {
            foreach ($day->questions as $question) {
                $options = $question->options;
                if ($options->isEmpty()) {
                    continue;
                }

                $option = $options[$index % $options->count()];

                EventEnrollmentAnswer::query()->create([
                    'event_enrollment_id' => $enrollment->id,
                    'event_day_question_id' => $question->id,
                    'event_day_question_option_id' => $option->id,
                ]);
            }
        }
    }
}
