<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            if (! Schema::hasColumn('questions', 'points')) {
                $table->unsignedInteger('points')->default(1)->after('sort_order');
            }
        });

        Schema::table('exams', function (Blueprint $table) {
            if (! Schema::hasColumn('exams', 'passing_points')) {
                $table->unsignedInteger('passing_points')->nullable()->after('total_points');
            }
        });

        Schema::table('results', function (Blueprint $table) {
            if (! Schema::hasColumn('results', 'passed')) {
                $table->boolean('passed')->nullable()->after('points_max');
            }
        });

        foreach (DB::table('exams')->whereNull('passing_points')->get() as $row) {
            $tp = (int) ($row->total_points ?? 100);
            DB::table('exams')->where('id', $row->id)->update([
                'passing_points' => max(1, (int) floor($tp / 2)),
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            if (Schema::hasColumn('questions', 'points')) {
                $table->dropColumn('points');
            }
        });

        Schema::table('exams', function (Blueprint $table) {
            if (Schema::hasColumn('exams', 'passing_points')) {
                $table->dropColumn('passing_points');
            }
        });

        Schema::table('results', function (Blueprint $table) {
            if (Schema::hasColumn('results', 'passed')) {
                $table->dropColumn('passed');
            }
        });
    }
};
