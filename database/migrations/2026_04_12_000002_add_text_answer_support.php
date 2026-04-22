<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->string('question_type', 30)->default('multiple_choice')->after('body');
            $table->text('model_answer')->nullable()->after('question_type');
        });

        Schema::table('answers', function (Blueprint $table) {
            $table->text('text_answer')->nullable()->after('option_id');
            $table->tinyInteger('is_correct_override')->nullable()->after('text_answer');
        });
    }

    public function down(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->dropColumn(['question_type', 'model_answer']);
        });

        Schema::table('answers', function (Blueprint $table) {
            $table->dropColumn(['text_answer', 'is_correct_override']);
        });
    }
};
