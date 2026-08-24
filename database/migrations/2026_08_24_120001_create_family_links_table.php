<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('family_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('student_user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamp('linked_at')->useCurrent();
            $table->unique(['parent_user_id', 'student_user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('family_links');
    }
};
