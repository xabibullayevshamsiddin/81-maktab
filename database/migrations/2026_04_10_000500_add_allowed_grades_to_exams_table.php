<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('exams')) {
            return;
        }

        Schema::table('exams', function (Blueprint $table) {
            if (! Schema::hasColumn('exams', 'allowed_grades')) {
                $table->json('allowed_grades')->nullable()->after('passing_points');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('exams') || ! Schema::hasColumn('exams', 'allowed_grades')) {
            return;
        }

        Schema::table('exams', function (Blueprint $table) {
            $table->dropColumn('allowed_grades');
        });
    }
};
