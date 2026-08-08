<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('event_enrollments', 'participation_mode')) {
            Schema::table('event_enrollments', function (Blueprint $table) {
                $table->string('participation_mode', 20)->nullable()->after('enrolled_at');
            });
        }

        if (! Schema::hasTable('event_day_questions')) {
            Schema::create('event_day_questions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('event_day_id')->constrained('event_days')->cascadeOnDelete();
                $table->unsignedTinyInteger('sort_order')->default(1);
                $table->string('question');
                $table->timestamps();

                $table->index(['event_day_id', 'sort_order'], 'edq_day_sort_idx');
            });
        }

        if (! Schema::hasTable('event_day_question_options')) {
            Schema::create('event_day_question_options', function (Blueprint $table) {
                $table->id();
                $table->foreignId('event_day_question_id')->constrained('event_day_questions')->cascadeOnDelete();
                $table->unsignedTinyInteger('sort_order')->default(1);
                $table->string('label');
                $table->timestamps();

                $table->index(['event_day_question_id', 'sort_order'], 'edqo_question_sort_idx');
            });
        }

        if (! Schema::hasTable('event_enrollment_answers')) {
            Schema::create('event_enrollment_answers', function (Blueprint $table) {
                $table->id();
                $table->foreignId('event_enrollment_id')->constrained('event_enrollments')->cascadeOnDelete();
                $table->foreignId('event_day_question_id')->constrained('event_day_questions')->cascadeOnDelete();
                $table->foreignId('event_day_question_option_id')->constrained('event_day_question_options')->cascadeOnDelete();
                $table->timestamps();

                $table->unique(['event_enrollment_id', 'event_day_question_id'], 'enrollment_question_unique');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('event_enrollment_answers');
        Schema::dropIfExists('event_day_question_options');
        Schema::dropIfExists('event_day_questions');

        if (Schema::hasColumn('event_enrollments', 'participation_mode')) {
            Schema::table('event_enrollments', function (Blueprint $table) {
                $table->dropColumn('participation_mode');
            });
        }
    }
};
