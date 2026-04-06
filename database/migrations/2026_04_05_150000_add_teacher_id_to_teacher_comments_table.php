<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('teacher_comments') || Schema::hasColumn('teacher_comments', 'teacher_id')) {
            return;
        }

        Schema::table('teacher_comments', function (Blueprint $table) {
            $table->foreignId('teacher_id')
                ->nullable()
                ->after('user_id')
                ->constrained('teachers')
                ->cascadeOnDelete();

            $table->index(['teacher_id', 'parent_id', 'created_at'], 'teacher_comments_teacher_parent_created_idx');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('teacher_comments') || ! Schema::hasColumn('teacher_comments', 'teacher_id')) {
            return;
        }

        Schema::table('teacher_comments', function (Blueprint $table) {
            $table->dropIndex('teacher_comments_teacher_parent_created_idx');
            $table->dropConstrainedForeignId('teacher_id');
        });
    }
};
