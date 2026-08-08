<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('members', function (Blueprint $table) {
            $table->id();
            $table->string('unique_id')->nullable()->unique();
            $table->string('title');
            $table->string('full_name');
            $table->string('nic')->unique();
            $table->foreignId('designation_id')->nullable()->constrained()->nullOnDelete();
            $table->string('mobile_1');
            $table->string('mobile_2')->nullable();
            $table->string('whatsapp')->nullable();
            $table->string('office_telephone')->nullable();
            $table->string('email')->nullable();
            $table->string('institute')->nullable();
            $table->string('sub_institute')->nullable();
            $table->string('section')->nullable();
            $table->text('address')->nullable();
            $table->string('profile_image')->nullable();
            $table->enum('registration_status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->enum('status', ['active', 'inactive'])->default('inactive');
            $table->string('password');
            $table->text('rejection_reason')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->rememberToken();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('members');
    }
};
