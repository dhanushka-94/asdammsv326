<?php

namespace Database\Seeders;

use App\Models\ActivityLog;
use App\Models\Designation;
use App\Models\Event;
use App\Models\Institute;
use App\Models\Member;
use App\Models\MemberCategory;
use App\Models\Section;
use App\Models\SubInstitute;
use App\Models\User;
use App\Support\AppSettings;
use App\Support\MemberQrCode;
use App\Support\UserRole;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->ensureSystemUsers();
        $this->wipeSampleData();
        $this->seedSettings();
        $this->seedDesignations();
        $this->seedMemberCategories();

        $this->call([
            OrgStructureSeeder::class,
            MemberSeeder::class,
            EventSeeder::class,
        ]);
    }

    private function ensureSystemUsers(): void
    {
        User::query()->updateOrCreate(
            ['email' => 'admin@asdamms.com'],
            [
                'name' => 'Super Admin',
                'phone' => '0770000001',
                'role' => UserRole::SUPER_ADMIN,
                'status' => 'active',
                'password' => 'password',
                'email_verified_at' => now(),
                'must_change_password' => false,
            ]
        );

        User::query()->updateOrCreate(
            ['email' => 'manager@asdamms.com'],
            [
                'name' => 'System Admin',
                'phone' => '0770000002',
                'role' => UserRole::ADMIN,
                'status' => 'active',
                'password' => 'password',
                'email_verified_at' => now(),
                'must_change_password' => false,
            ]
        );

        User::query()->updateOrCreate(
            ['email' => 'viewer@asdamms.com'],
            [
                'name' => 'System Viewer',
                'phone' => '0770000003',
                'role' => UserRole::VIEWER,
                'status' => 'active',
                'password' => 'password',
                'email_verified_at' => now(),
                'must_change_password' => false,
            ]
        );
    }

    private function wipeSampleData(): void
    {
        Schema::disableForeignKeyConstraints();

        ActivityLog::query()->delete();
        Event::query()->delete();
        Member::query()->delete();
        Section::query()->delete();
        SubInstitute::query()->delete();
        Institute::query()->delete();
        Designation::query()->delete();
        MemberCategory::query()->delete();

        Schema::enableForeignKeyConstraints();

        if (Storage::disk('public')->exists(MemberQrCode::DIRECTORY)) {
            Storage::disk('public')->deleteDirectory(MemberQrCode::DIRECTORY);
        }

        if (Storage::disk('public')->exists('members/profiles')) {
            Storage::disk('public')->deleteDirectory('members/profiles');
        }

        // Reset auto-increments where helpful for clean sample IDs.
        foreach ([
            'members',
            'events',
            'event_venues',
            'event_days',
            'event_day_sessions',
            'event_day_questions',
            'event_day_question_options',
            'event_enrollments',
            'event_enrollment_answers',
            'institutes',
            'sub_institutes',
            'sections',
            'designations',
            'member_categories',
            'activity_logs',
        ] as $table) {
            if (Schema::hasTable($table)) {
                DB::statement('ALTER TABLE `'.$table.'` AUTO_INCREMENT = 1');
            }
        }

        $this->command?->info('Cleared sample data (system users kept).');
    }

    private function seedSettings(): void
    {
        AppSettings::setMaintenanceMode(false);
        AppSettings::setMaintenanceMessage(
            'The ASDA Member Management System is temporarily unavailable while we perform maintenance. Please check back soon.'
        );
        AppSettings::setMemberRegistrationEnabled(true);
    }

    private function seedDesignations(): void
    {
        $designations = [
            'Director',
            'Additional Director',
            'Deputy Director',
            'Assistant Director',
            'Agricultural Officer',
            'Research Officer',
            'Subject Matter Specialist',
            'Development Officer',
            'Technical Officer',
            'Extension Officer',
            'Programme Officer',
            'Administrative Officer',
        ];

        foreach ($designations as $name) {
            Designation::query()->create([
                'name' => $name,
                'is_active' => true,
            ]);
        }
    }

    private function seedMemberCategories(): void
    {
        $categories = [
            'General',
            'Executive Committee',
            'Author',
            'Poster',
            'Retired Officer',
            'University',
            'Media',
            'VIP',
            'VVIP',
            'Student',
        ];

        foreach ($categories as $name) {
            MemberCategory::query()->create([
                'name' => $name,
                'is_active' => true,
            ]);
        }
    }
}
