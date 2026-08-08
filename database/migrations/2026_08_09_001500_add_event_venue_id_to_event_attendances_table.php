<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('event_attendances', function (Blueprint $table) {
            $table->foreignId('event_venue_id')
                ->nullable()
                ->after('event_day_id')
                ->constrained('event_venues')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('event_attendances', function (Blueprint $table) {
            $table->dropConstrainedForeignId('event_venue_id');
        });
    }
};
