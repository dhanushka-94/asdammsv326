<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_day_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_day_id')->constrained('event_days')->cascadeOnDelete();
            $table->unsignedTinyInteger('sort_order')->default(1);
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index(['event_day_id', 'sort_order']);
        });

        $days = DB::table('event_days')->orderBy('id')->get();

        foreach ($days as $day) {
            $sessionName = trim((string) ($day->session_name ?? ''));
            if ($sessionName === '') {
                continue;
            }

            DB::table('event_day_sessions')->insert([
                'event_day_id' => $day->id,
                'sort_order' => 1,
                'name' => $sessionName,
                'description' => $day->description,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        Schema::table('event_days', function (Blueprint $table) {
            $table->dropColumn('session_name');
        });
    }

    public function down(): void
    {
        Schema::table('event_days', function (Blueprint $table) {
            $table->string('session_name')->nullable()->after('day_number');
        });

        $sessions = DB::table('event_day_sessions')
            ->orderBy('event_day_id')
            ->orderBy('sort_order')
            ->get()
            ->groupBy('event_day_id');

        foreach ($sessions as $dayId => $daySessions) {
            $first = $daySessions->first();
            DB::table('event_days')->where('id', $dayId)->update([
                'session_name' => $first->name,
                'description' => $first->description,
            ]);
        }

        Schema::table('event_days', function (Blueprint $table) {
            $table->string('session_name')->nullable(false)->change();
        });

        Schema::dropIfExists('event_day_sessions');
    }
};
