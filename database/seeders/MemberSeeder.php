<?php

namespace Database\Seeders;

use App\Models\Designation;
use App\Models\Institute;
use App\Models\Member;
use App\Models\MemberCategory;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class MemberSeeder extends Seeder
{
    private const TOTAL = 1100;

    /** @var list<string> */
    private array $titles = ['Dr', 'Mr', 'Mrs', 'Ms', 'Prof', 'Eng'];

    /** @var list<string> */
    private array $firstNames = [
        'Chandana', 'Malini', 'Pradeep', 'Nadeesha', 'Lahiru', 'Harsha', 'Sajith', 'Priyanka',
        'Dineth', 'Thilini', 'Kasun', 'Ishara', 'Nuwan', 'Sanduni', 'Asanka', 'Dilani',
        'Roshan', 'Sewwandi', 'Amila', 'Chathuri', 'Buddhika', 'Nimali', 'Gayan', 'Hashini',
        'Thusitha', 'Madhavi', 'Ruwan', 'Anusha', 'Saman', 'Kumari', 'Janaka', 'Shalika',
        'Eranga', 'Hasitha', 'Menaka', 'Dulanjali', 'Suranga', 'Nirosha', 'Ajith', 'Sithara',
        'Ishan', 'Kavindi', 'Mahesh', 'Tharushi', 'Pasindu', 'Nethmi', 'Chamara', 'Imasha',
    ];

    /** @var list<string> */
    private array $lastNames = [
        'Weerasinghe', 'Abeysekera', 'Jayawardena', 'Fonseka', 'Wickramasinghe', 'Dissanayake',
        'Ranatunga', 'Hettiarachchi', 'Karunaratne', 'Samarasinghe', 'Perera', 'Silva',
        'Fernando', 'Gunasekara', 'Bandara', 'Rajapaksa', 'Senanayake', 'Wijesinghe',
        'Pathirana', 'Jayasuriya', 'Kumara', 'Rathnayake', 'Ekanayake', 'Herath',
        'Gamage', 'Liyanage', 'De Silva', 'Jayasinghe', 'Amarasinghe', 'Seneviratne',
    ];

    /** @var list<string> */
    private array $cities = [
        'Peradeniya', 'Gannoruwa', 'Kandy', 'Colombo', 'Anuradhapura', 'Kurunegala',
        'Badulla', 'Ampara', 'Galle', 'Matara', 'Jaffna', 'Ratnapura', 'Polonnaruwa', 'Batticaloa',
        'Hambantota', 'Gampaha', 'Kalutara', 'Matale', 'Nuwara Eliya', 'Puttalam',
    ];

    public function run(): void
    {
        $designationIds = Designation::query()->orderBy('id')->pluck('id')->all();
        $categoryIds = MemberCategory::query()->orderBy('id')->pluck('id')->all();
        $adminId = User::query()->where('email', 'admin@asdamms.com')->value('id');

        $orgSlots = Institute::query()
            ->with(['subInstitutes.sections'])
            ->orderBy('id')
            ->get()
            ->flatMap(function (Institute $institute) {
                return $institute->subInstitutes->flatMap(function ($sub) use ($institute) {
                    if ($sub->sections->isEmpty()) {
                        return [[
                            'institute' => $institute->name,
                            'sub_institute' => $sub->name,
                            'section' => null,
                        ]];
                    }

                    return $sub->sections->map(fn ($section) => [
                        'institute' => $institute->name,
                        'sub_institute' => $sub->name,
                        'section' => $section->name,
                    ]);
                });
            })
            ->values()
            ->all();

        if ($designationIds === [] || $orgSlots === []) {
            $this->command?->error('Designations / org structure missing. Seed those first.');

            return;
        }

        $passwordCache = [];
        $usedNics = [];
        $now = now();
        $rows = [];

        for ($i = 1; $i <= self::TOTAL; $i++) {
            $nic = $this->uniqueNic($i, $usedNics);
            $usedNics[$nic] = true;

            $registration = $this->registrationStatus($i);
            $status = $registration === 'approved'
                ? ($i % 19 === 0 ? 'inactive' : 'active')
                : 'inactive';

            $plainPassword = Member::defaultPasswordForNic($nic);
            $passwordCache[$plainPassword] ??= Hash::make($plainPassword);

            $org = $orgSlots[($i - 1) % count($orgSlots)];

            $rows[] = [
                'unique_id' => sprintf('ASDA26%06d', $i),
                'title' => $this->titles[$i % count($this->titles)],
                'full_name' => $this->firstNames[$i % count($this->firstNames)].' '.$this->lastNames[$i % count($this->lastNames)],
                'nic' => $nic,
                'designation_id' => $designationIds[$i % count($designationIds)],
                'member_category_id' => $categoryIds === [] ? null : $categoryIds[$i % count($categoryIds)],
                'mobile_1' => sprintf('07%08d', 70000000 + ($i % 9999999)),
                'mobile_2' => $i % 4 === 0 ? sprintf('07%08d', 71000000 + ($i % 9999999)) : null,
                'whatsapp' => sprintf('07%08d', 70000000 + ($i % 9999999)),
                'office_telephone' => $i % 3 === 0 ? sprintf('0112%06d', 100000 + ($i % 899999)) : null,
                'email' => 'member'.$i.'@asda-sample.lk',
                'institute' => $org['institute'],
                'sub_institute' => $org['sub_institute'],
                'section' => $org['section'],
                'address' => $this->cities[$i % count($this->cities)].', Sri Lanka',
                'profile_image' => null,
                'registration_status' => $registration,
                'status' => $status,
                'password' => $passwordCache[$plainPassword],
                'must_change_password' => true,
                'rejection_reason' => $registration === 'rejected' ? 'Sample rejection for testing.' : null,
                'approved_at' => $registration === 'approved' ? $now->copy()->subDays(($i % 90) + 1) : null,
                'approved_by' => $registration === 'approved' ? $adminId : null,
                'remember_token' => null,
                'last_login_at' => $registration === 'approved' && $i % 6 === 0
                    ? $now->copy()->subHours(($i % 120) + 1)
                    : null,
                'qr_download_count' => $registration === 'approved' && $i % 8 === 0 ? ($i % 5) + 1 : 0,
                'qr_last_downloaded_at' => $registration === 'approved' && $i % 8 === 0
                    ? $now->copy()->subDays(($i % 30) + 1)
                    : null,
                'created_at' => $now->copy()->subDays(($i % 120) + 1),
                'updated_at' => $now,
            ];

            if (count($rows) >= 200) {
                DB::table('members')->insert($rows);
                $rows = [];
            }
        }

        if ($rows !== []) {
            DB::table('members')->insert($rows);
        }

        $this->command?->info('Seeded '.self::TOTAL.' sample members (QR generated on demand).');
    }

    /**
     * @param  array<string, bool>  $usedNics
     */
    private function uniqueNic(int $index, array $usedNics): string
    {
        $year = 1960 + ($index % 45);
        $dayOfYear = ($index % 365) + 1;
        $serial = 10000 + $index;

        do {
            $nic = sprintf('%04d%03d%05d', $year, $dayOfYear, $serial % 100000);
            $serial++;
        } while (isset($usedNics[$nic]));

        return $nic;
    }

    private function registrationStatus(int $index): string
    {
        return match (true) {
            $index % 29 === 0 => 'rejected',
            $index % 13 === 0 => 'pending',
            default => 'approved',
        };
    }
}
