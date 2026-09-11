<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ielts_tests', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->enum('type', ['placement', 'full_mock'])->default('placement');
            $table->unsignedInteger('time_limit_minutes')->default(20);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('ielts_sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ielts_test_id')->constrained()->cascadeOnDelete();
            $table->enum('skill', ['reading', 'listening', 'writing', 'speaking']);
            $table->unsignedInteger('order')->default(1);
            $table->unsignedInteger('time_limit_minutes')->default(20);
            $table->timestamps();
        });

        Schema::create('ielts_passages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ielts_section_id')->constrained()->cascadeOnDelete();
            $table->string('title')->nullable();
            $table->longText('content')->nullable(); // reading text OR writing prompt OR speaking prompt
            $table->string('audio_url')->nullable(); // for listening
            $table->timestamps();
        });

        Schema::create('ielts_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ielts_passage_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['multiple_choice', 'true_false_ng', 'gap_fill', 'writing_task', 'speaking_task']);
            $table->text('question_text');
            $table->json('options')->nullable(); // for multiple_choice
            $table->string('correct_answer')->nullable(); // null for writing/speaking
            $table->unsignedInteger('order')->default(1);
            $table->timestamps();
        });

        Schema::create('ielts_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('ielts_test_id')->constrained()->cascadeOnDelete();
            $table->json('question_order')->nullable(); // shuffled order snapshot, mirrors ExamController pattern
            $table->timestamp('started_at')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->enum('status', ['in_progress', 'submitted', 'graded'])->default('in_progress');
            $table->unsignedInteger('rule_violation_count')->default(0); // reuse anti-cheat pattern from ExamController
            $table->timestamps();

            $table->unique(['user_id', 'ielts_test_id', 'started_at']);
        });

        Schema::create('ielts_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ielts_attempt_id')->constrained()->cascadeOnDelete();
            $table->foreignId('ielts_question_id')->constrained()->cascadeOnDelete();
            $table->longText('answer_text')->nullable();
            $table->boolean('is_correct')->nullable();
            $table->json('ai_feedback')->nullable(); // { band, criteria: {...}, comments }
            $table->timestamps();

            $table->unique(['ielts_attempt_id', 'ielts_question_id']);
        });

        Schema::create('ielts_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ielts_attempt_id')->constrained()->cascadeOnDelete();
            $table->json('section_bands'); // { reading: 6.5, listening: 7.0, writing: 6.0, speaking: null }
            $table->decimal('overall_band', 3, 1)->nullable();
            $table->string('level_label')->nullable(); // e.g. "Upper-Intermediate (B2)"
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ielts_results');
        Schema::dropIfExists('ielts_answers');
        Schema::dropIfExists('ielts_attempts');
        Schema::dropIfExists('ielts_questions');
        Schema::dropIfExists('ielts_passages');
        Schema::dropIfExists('ielts_sections');
        Schema::dropIfExists('ielts_tests');
    }
};
