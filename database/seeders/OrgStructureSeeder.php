<?php

namespace Database\Seeders;

use App\Models\Institute;
use Illuminate\Database\Seeder;

class OrgStructureSeeder extends Seeder
{
    public function run(): void
    {
        $tree = [
            'Department of Agriculture' => [
                'Head Office' => ['Directorate', 'Administration', 'Finance', 'Planning'],
                'Extension Division' => ['Field Extension', 'Training', 'Communication'],
                'Research Coordination' => ['Crop Research', 'Soil & Water', 'Plant Protection'],
            ],
            'Provincial Department of Agriculture' => [
                'Western Province' => ['Colombo District', 'Gampaha District', 'Kalutara District'],
                'Central Province' => ['Kandy District', 'Matale District', 'Nuwara Eliya District'],
                'Southern Province' => ['Galle District', 'Matara District', 'Hambantota District'],
                'North Central Province' => ['Anuradhapura District', 'Polonnaruwa District'],
            ],
            'Seed Certification Service' => [
                'Seed Certification HQ' => ['Certification', 'Laboratory', 'Quality Assurance'],
                'Regional Seed Units' => ['Peradeniya Unit', 'Maha Illuppallama Unit', 'Kundasale Unit'],
            ],
            'HORDI' => [
                'Horticulture Research' => ['Fruits', 'Vegetables', 'Floriculture'],
                'Technology Transfer' => ['Demonstration', 'Farmer Training'],
            ],
            'RRDI' => [
                'Rice Research' => ['Varietal Development', 'Agronomy', 'Pest Management'],
                'Outreach' => ['Farmer Schools', 'Seed Production'],
            ],
            'Farm Mechanization Research Centre' => [
                'Engineering' => ['Machinery Design', 'Testing', 'Maintenance'],
                'Field Services' => ['Demonstrations', 'Operator Training'],
            ],
            'Extension & Training Centre' => [
                'Training Wing' => ['Induction', 'In-service', 'Special Courses'],
                'E-learning Unit' => ['Content Development', 'Support Desk'],
            ],
            'District Agriculture Office' => [
                'Kandy DAO' => ['Crop Advisory', 'Input Supply', 'Statistics'],
                'Kurunegala DAO' => ['Crop Advisory', 'Irrigation Support', 'Statistics'],
                'Anuradhapura DAO' => ['Crop Advisory', 'Dry Zone Programmes', 'Statistics'],
                'Ampara DAO' => ['Crop Advisory', 'Paddy Programme', 'Statistics'],
            ],
        ];

        $countInstitutes = 0;
        $countSubs = 0;
        $countSections = 0;

        foreach ($tree as $instituteName => $subs) {
            $institute = Institute::query()->create([
                'name' => $instituteName,
                'is_active' => true,
            ]);
            $countInstitutes++;

            foreach ($subs as $subName => $sections) {
                $sub = $institute->subInstitutes()->create([
                    'name' => $subName,
                    'is_active' => true,
                ]);
                $countSubs++;

                foreach ($sections as $sectionName) {
                    $sub->sections()->create([
                        'name' => $sectionName,
                        'is_active' => true,
                    ]);
                    $countSections++;
                }
            }
        }

        $this->command?->info(
            "Seeded {$countInstitutes} institutes, {$countSubs} sub-institutes, {$countSections} sections."
        );
    }
}
