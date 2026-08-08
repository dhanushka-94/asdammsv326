<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE users MODIFY role ENUM('super_admin','admin','viewer','reception') NOT NULL DEFAULT 'viewer'");
    }

    public function down(): void
    {
        DB::table('users')->where('role', 'reception')->update(['role' => 'viewer']);
        DB::statement("ALTER TABLE users MODIFY role ENUM('super_admin','admin','viewer') NOT NULL DEFAULT 'viewer'");
    }
};
