<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->timestamp('last_login_at')->nullable()->after('remember_token');
            $table->unsignedInteger('qr_download_count')->default(0)->after('last_login_at');
            $table->timestamp('qr_last_downloaded_at')->nullable()->after('qr_download_count');
        });
    }

    public function down(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->dropColumn(['last_login_at', 'qr_download_count', 'qr_last_downloaded_at']);
        });
    }
};
