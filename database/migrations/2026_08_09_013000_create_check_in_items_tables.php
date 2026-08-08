<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('check_in_items', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('event_attendance_check_in_item', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_attendance_id')->constrained('event_attendances')->cascadeOnDelete();
            $table->foreignId('check_in_item_id')->constrained('check_in_items')->restrictOnDelete();
            $table->timestamp('given_at')->nullable();
            $table->timestamps();

            $table->unique(['event_attendance_id', 'check_in_item_id'], 'attendance_item_unique');
        });

        $now = now();
        $defaults = [
            'Meal Token',
            'Laptop Pouch',
            'Flask',
            'Pendrive',
            'Notebook & Pen',
            'Dinner Invitation',
        ];

        foreach ($defaults as $index => $name) {
            DB::table('check_in_items')->insert([
                'name' => $name,
                'sort_order' => $index + 1,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('event_attendance_check_in_item');
        Schema::dropIfExists('check_in_items');
    }
};
