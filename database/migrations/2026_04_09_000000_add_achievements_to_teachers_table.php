<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('teachers')) {
            return;
        }

        Schema::table('teachers', function (Blueprint $table) {
            if (! Schema::hasColumn('teachers', 'achievements')) {
                $table->text('achievements')->nullable()->after('grades');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('teachers') || ! Schema::hasColumn('teachers', 'achievements')) {
            return;
        }

        Schema::table('teachers', function (Blueprint $table) {
            $table->dropColumn('achievements');
        });
    }
};
