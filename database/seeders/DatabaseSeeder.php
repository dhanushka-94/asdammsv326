<?php

namespace Database\Seeders;

use App\Models\ActivityLog;
use App\Models\CheckInItem;
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
        $this->wipeSampleData();
        $this->ensureSystemUsers();
        $this->seedSettings();
        $this->seedDesignations();
        $this->seedMemberCategories();
        $this->seedCheckInItems();

        $this->call([
            OrgStructureSeeder::class,
            MemberSeeder::class,
            EventSeeder::class,
        ]);
    }

    private function ensureSystemUsers(): void
    {
        $users = [
            [
                'email' => 'admin@asdamms.com',
                'name' => 'Super Admin',
                'phone' => '0770000001',
                'role' => UserRole::SUPER_ADMIN,
            ],
            [
                'email' => 'manager@asdamms.com',
                'name' => 'System Admin',
                'phone' => '0770000002',
                'role' => UserRole::ADMIN,
            ],
            [
                'email' => 'viewer@asdamms.com',
                'name' => 'System Viewer',
                'phone' => '0770000003',
                'role' => UserRole::VIEWER,
            ],
            [
                'email' => 'reception@asdamms.com',
                'name' => 'Reception Officer',
                'phone' => '0770000004',
                'role' => UserRole::RECEPTION,
            ],
            [
                'email' => 'reception2@asdamms.com',
                'name' => 'Reception Desk 2',
                'phone' => '0770000005',
                'role' => UserRole::RECEPTION,
            ],
        ];

        foreach ($users as $user) {
            User::query()->updateOrCreate(
                ['email' => $user['email']],
                [
                    'name' => $user['name'],
                    'phone' => $user['phone'],
                    'role' => $user['role'],
                    'status' => 'active',
                    'password' => 'password',
                    'email_verified_at' => now(),
                    'must_change_password' => false,
                    'profile_image' => null,
                    'desk_pin_hash' => null,
                ]
            );
        }
    }

    private function wipeSampleData(): void
    {
        Schema::disableForeignKeyConstraints();

        $tables = [
            'event_attendance_check_in_item',
            'event_attendances',
            'event_enrollment_answers',
            'event_enrollments',
            'event_day_question_options',
            'event_day_questions',
            'event_day_sessions',
            'event_days',
            'event_venues',
            'event_reception_user',
            'events',
            'check_in_items',
            'activity_logs',
            'members',
            'sections',
            'sub_institutes',
            'institutes',
            'designations',
            'member_categories',
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                DB::table($table)->delete();
            }
        }

        // Keep only the known demo system accounts when re-seeding without migrate:fresh.
        if (Schema::hasTable('users')) {
            User::query()
                ->whereNotIn('email', [
                    'admin@asdamms.com',
                    'manager@asdamms.com',
                    'viewer@asdamms.com',
                    'reception@asdamms.com',
                    'reception2@asdamms.com',
                ])
                ->delete();
        }

        Schema::enableForeignKeyConstraints();

        foreach ([MemberQrCode::DIRECTORY, 'members/profiles', 'users/profiles'] as $directory) {
            if (Storage::disk('public')->exists($directory)) {
                Storage::disk('public')->deleteDirectory($directory);
            }
        }

        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                DB::statement('ALTER TABLE `'.$table.'` AUTO_INCREMENT = 1');
            }
        }

        $this->command?->info('Cleared existing sample / business data.');
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
            'Field Assistant',
            'Research Assistant',
            'Lecturer',
            'Student Officer',
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
            'Guest',
            'Exhibitor',
        ];

        foreach ($categories as $name) {
            MemberCategory::query()->create([
                'name' => $name,
                'is_active' => true,
            ]);
        }
    }

    private function seedCheckInItems(): void
    {
        $items = [
            'Meal Token',
            'Conference Bag',
            'ID Lanyard',
            'Water Bottle',
            'Programme Booklet',
            'USB Drive',
            'Cap',
            'Notebook',
        ];

        foreach ($items as $index => $name) {
            CheckInItem::query()->create([
                'name' => $name,
                'sort_order' => $index + 1,
                'is_active' => true,
            ]);
        }
    }
}
