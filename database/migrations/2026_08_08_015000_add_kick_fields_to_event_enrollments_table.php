<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('event_enrollments', function (Blueprint $table) {
            $table->timestamp('kicked_at')->nullable()->after('enrolled_at');
            $table->text('kick_reason')->nullable()->after('kicked_at');
            $table->foreignId('kicked_by')->nullable()->after('kick_reason')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('event_enrollments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('kicked_by');
            $table->dropColumn(['kicked_at', 'kick_reason']);
        });
    }
};
