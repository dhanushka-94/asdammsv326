<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('event_days', function (Blueprint $table) {
            $table->string('venue_name')->nullable()->after('session_name');
            $table->string('floor')->nullable()->after('venue_name');
            $table->string('hall_room')->nullable()->after('floor');
            $table->decimal('latitude', 10, 7)->nullable()->after('description');
            $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
            $table->string('maps_url', 1000)->nullable()->after('longitude');
        });
    }

    public function down(): void
    {
        Schema::table('event_days', function (Blueprint $table) {
            $table->dropColumn([
                'venue_name',
                'floor',
                'hall_room',
                'latitude',
                'longitude',
                'maps_url',
            ]);
        });
    }
};
