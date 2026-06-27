<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('exams')
            || ! Schema::hasColumn('exams', 'start_time')
            || ! Schema::hasColumn('exams', 'end_time')) {
            return;
        }

        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        Schema::table('exams', function (Blueprint $table) {
            $table->timestamp('start_time')->nullable()->change();
            $table->timestamp('end_time')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('exams')
            || ! Schema::hasColumn('exams', 'start_time')
            || ! Schema::hasColumn('exams', 'end_time')) {
            return;
        }

        DB::table('exams')->whereNull('start_time')->update(['start_time' => now()]);
        DB::table('exams')->whereNull('end_time')->update(['end_time' => now()]);

        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        Schema::table('exams', function (Blueprint $table) {
            $table->timestamp('start_time')->nullable(false)->change();
            $table->timestamp('end_time')->nullable(false)->change();
        });
    }
};
