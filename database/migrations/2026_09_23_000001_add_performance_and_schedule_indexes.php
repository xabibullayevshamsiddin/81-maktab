<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. exams.is_active index
        if (Schema::hasTable('exams') && Schema::hasColumn('exams', 'is_active')) {
            Schema::table('exams', function (Blueprint $table) {
                $table->index('is_active', 'exams_is_active_index');
            });
        }

        // 2. results.user_id index (to speed up Result::where('user_id', ...))
        if (Schema::hasTable('results') && Schema::hasColumn('results', 'user_id')) {
            Schema::table('results', function (Blueprint $table) {
                $table->index('user_id', 'results_user_id_index');
            });
        }

        // 3. donations (user_id, status)
        if (Schema::hasTable('donations') && Schema::hasColumn('donations', 'user_id')) {
            Schema::table('donations', function (Blueprint $table) {
                $table->index(['user_id', 'status'], 'donations_user_status_index');
            });
        }

        // 4. users (is_blocked, blocked_until)
        if (Schema::hasTable('users') && Schema::hasColumn('users', 'is_blocked')) {
            Schema::table('users', function (Blueprint $table) {
                $table->index(['is_blocked', 'blocked_until'], 'users_blocked_check_index');
            });
        }

        // 5. courses (status, created_at)
        if (Schema::hasTable('courses') && Schema::hasColumn('courses', 'status')) {
            Schema::table('courses', function (Blueprint $table) {
                $table->index(['status', 'created_at'], 'courses_status_created_at_index');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('exams')) {
            Schema::table('exams', function (Blueprint $table) {
                $table->dropIndex('exams_is_active_index');
            });
        }

        if (Schema::hasTable('results')) {
            Schema::table('results', function (Blueprint $table) {
                $table->dropIndex('results_user_id_index');
            });
        }

        if (Schema::hasTable('donations')) {
            Schema::table('donations', function (Blueprint $table) {
                $table->dropIndex('donations_user_status_index');
            });
        }

        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropIndex('users_blocked_check_index');
            });
        }

        if (Schema::hasTable('courses')) {
            Schema::table('courses', function (Blueprint $table) {
                $table->dropIndex('courses_status_created_at_index');
            });
        }
    }
};
