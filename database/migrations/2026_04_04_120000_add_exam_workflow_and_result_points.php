<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exams', function (Blueprint $table) {
            if (! Schema::hasColumn('exams', 'required_questions')) {
                $table->unsignedInteger('required_questions')->default(1);
            }
            if (! Schema::hasColumn('exams', 'total_points')) {
                $table->unsignedInteger('total_points')->default(100);
            }
        });

        Schema::table('results', function (Blueprint $table) {
            if (! Schema::hasColumn('results', 'points_earned')) {
                $table->unsignedInteger('points_earned')->nullable();
            }
            if (! Schema::hasColumn('results', 'points_max')) {
                $table->unsignedInteger('points_max')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('exams', function (Blueprint $table) {
            if (Schema::hasColumn('exams', 'required_questions')) {
                $table->dropColumn('required_questions');
            }
            if (Schema::hasColumn('exams', 'total_points')) {
                $table->dropColumn('total_points');
            }
        });

        Schema::table('results', function (Blueprint $table) {
            if (Schema::hasColumn('results', 'points_earned')) {
                $table->dropColumn('points_earned');
            }
            if (Schema::hasColumn('results', 'points_max')) {
                $table->dropColumn('points_max');
            }
        });
    }
};
