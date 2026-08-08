<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->string('invitation_letter_path')->nullable()->after('status');
            $table->string('invitation_card_path')->nullable()->after('invitation_letter_path');
            $table->json('invitation_letter_settings')->nullable()->after('invitation_card_path');
            $table->json('invitation_card_settings')->nullable()->after('invitation_letter_settings');
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn([
                'invitation_letter_path',
                'invitation_card_path',
                'invitation_letter_settings',
                'invitation_card_settings',
            ]);
        });
    }
};
