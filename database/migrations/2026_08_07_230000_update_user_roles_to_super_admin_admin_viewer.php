<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE users MODIFY role VARCHAR(50) NOT NULL DEFAULT 'viewer'");

        // Legacy roles → new roles
        DB::table('users')->where('role', 'admin')->update(['role' => 'super_admin']);
        DB::table('users')->where('role', 'manager')->update(['role' => 'admin']);
        DB::table('users')->where('role', 'staff')->update(['role' => 'viewer']);

        DB::statement("ALTER TABLE users MODIFY role ENUM('super_admin','admin','viewer') NOT NULL DEFAULT 'viewer'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE users MODIFY role VARCHAR(50) NOT NULL DEFAULT 'staff'");

        DB::table('users')->where('role', 'super_admin')->update(['role' => 'admin']);
        DB::table('users')->where('role', 'admin')->update(['role' => 'manager']);
        DB::table('users')->where('role', 'viewer')->update(['role' => 'staff']);

        DB::statement("ALTER TABLE users MODIFY role ENUM('admin','manager','staff') NOT NULL DEFAULT 'staff'");
    }
};
