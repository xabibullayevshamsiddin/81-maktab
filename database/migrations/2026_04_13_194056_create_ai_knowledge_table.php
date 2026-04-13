<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ai_knowledges', function (Blueprint $table) {
            $table->id();
            $table->text('question');
            $table->text('question_en')->nullable();
            $table->text('answer');
            $table->text('answer_en')->nullable();
            $table->string('keywords')->nullable()->index(); // For faster search
            $table->string('category')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_knowledges');
    }
};
