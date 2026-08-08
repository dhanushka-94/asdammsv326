<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('event_venues', function (Blueprint $table) {
            $table->text('description')->nullable()->after('hall_room');
        });

        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE events MODIFY method VARCHAR(20) NOT NULL DEFAULT 'physical'");
        } elseif ($driver === 'pgsql') {
            DB::statement('ALTER TABLE events ALTER COLUMN method TYPE VARCHAR(20)');
        }
    }

    public function down(): void
    {
        DB::table('events')->where('method', 'both')->update(['method' => 'physical']);

        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE events MODIFY method ENUM('physical', 'online') NOT NULL DEFAULT 'physical'");
        }

        Schema::table('event_venues', function (Blueprint $table) {
            $table->dropColumn('description');
        });
    }
};
