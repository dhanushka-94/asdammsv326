<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('institutes', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('sub_institutes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institute_id')->constrained('institutes')->cascadeOnDelete();
            $table->string('name');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['institute_id', 'name']);
        });

        Schema::create('sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sub_institute_id')->constrained('sub_institutes')->cascadeOnDelete();
            $table->string('name');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['sub_institute_id', 'name']);
        });

        $this->seedFromExistingMembers();
    }

    public function down(): void
    {
        Schema::dropIfExists('sections');
        Schema::dropIfExists('sub_institutes');
        Schema::dropIfExists('institutes');
    }

    private function seedFromExistingMembers(): void
    {
        if (! Schema::hasTable('members')) {
            return;
        }

        $now = now();
        $rows = DB::table('members')
            ->select('institute', 'sub_institute', 'section')
            ->whereNotNull('institute')
            ->where('institute', '!=', '')
            ->get();

        $instituteIds = [];
        $subInstituteIds = [];

        foreach ($rows as $row) {
            $instituteName = trim((string) $row->institute);
            if ($instituteName === '') {
                continue;
            }

            $instituteKey = Str::lower($instituteName);
            if (! isset($instituteIds[$instituteKey])) {
                $id = DB::table('institutes')->whereRaw('LOWER(name) = ?', [$instituteKey])->value('id');
                if (! $id) {
                    $id = DB::table('institutes')->insertGetId([
                        'name' => $instituteName,
                        'is_active' => true,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
                $instituteIds[$instituteKey] = $id;
            }

            $subName = trim((string) ($row->sub_institute ?? ''));
            if ($subName === '') {
                continue;
            }

            $subKey = $instituteIds[$instituteKey].'|'.Str::lower($subName);
            if (! isset($subInstituteIds[$subKey])) {
                $id = DB::table('sub_institutes')
                    ->where('institute_id', $instituteIds[$instituteKey])
                    ->whereRaw('LOWER(name) = ?', [Str::lower($subName)])
                    ->value('id');
                if (! $id) {
                    $id = DB::table('sub_institutes')->insertGetId([
                        'institute_id' => $instituteIds[$instituteKey],
                        'name' => $subName,
                        'is_active' => true,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
                $subInstituteIds[$subKey] = $id;
            }

            $sectionName = trim((string) ($row->section ?? ''));
            if ($sectionName === '') {
                continue;
            }

            $exists = DB::table('sections')
                ->where('sub_institute_id', $subInstituteIds[$subKey])
                ->whereRaw('LOWER(name) = ?', [Str::lower($sectionName)])
                ->exists();

            if (! $exists) {
                DB::table('sections')->insert([
                    'sub_institute_id' => $subInstituteIds[$subKey],
                    'name' => $sectionName,
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }
};
