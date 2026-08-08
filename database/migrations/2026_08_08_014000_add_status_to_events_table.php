<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->string('status', 20)->default('active')->after('end_time');
        });

        DB::table('events')->update([
            'status' => DB::raw("CASE WHEN is_published = 1 THEN 'active' ELSE 'inactive' END"),
        ]);

        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn('is_published');
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->boolean('is_published')->default(true)->after('end_time');
        });

        DB::table('events')->update([
            'is_published' => DB::raw("CASE WHEN status = 'active' THEN 1 ELSE 0 END"),
        ]);

        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
