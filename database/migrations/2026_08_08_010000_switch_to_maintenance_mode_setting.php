<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $old = DB::table('settings')->where('key', 'public_access_enabled')->first();

        if ($old) {
            // Invert: public ON (1) → maintenance OFF (0)
            $maintenanceOn = in_array(strtolower((string) $old->value), ['1', 'true', 'yes', 'on'], true) ? '0' : '1';

            DB::table('settings')->updateOrInsert(
                ['key' => 'maintenance_mode'],
                [
                    'value' => $maintenanceOn,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );

            DB::table('settings')->where('key', 'public_access_enabled')->delete();
        } else {
            DB::table('settings')->updateOrInsert(
                ['key' => 'maintenance_mode'],
                [
                    'value' => '0',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }

    public function down(): void
    {
        $mode = DB::table('settings')->where('key', 'maintenance_mode')->first();

        if ($mode) {
            $publicOn = in_array(strtolower((string) $mode->value), ['1', 'true', 'yes', 'on'], true) ? '0' : '1';

            DB::table('settings')->updateOrInsert(
                ['key' => 'public_access_enabled'],
                [
                    'value' => $publicOn,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );

            DB::table('settings')->where('key', 'maintenance_mode')->delete();
        }
    }
};
