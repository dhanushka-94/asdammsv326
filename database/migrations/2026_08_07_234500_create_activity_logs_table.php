<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->string('guard')->nullable()->index();
            $table->nullableMorphs('causer');
            $table->string('causer_name')->nullable();
            $table->string('causer_role')->nullable();
            $table->string('action', 50)->index();
            $table->string('description');
            $table->nullableMorphs('subject');
            $table->string('subject_label')->nullable();
            $table->string('route_name')->nullable()->index();
            $table->string('method', 10)->nullable();
            $table->string('url', 500)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->json('properties')->nullable();
            $table->timestamp('created_at')->useCurrent()->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
