<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('teacher_comment_likes')) {
            return;
        }

        Schema::create('teacher_comment_likes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_comment_id')->constrained('teacher_comments')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['teacher_comment_id', 'user_id'], 'teacher_comment_likes_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teacher_comment_likes');
    }
};
