<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exams', function (Blueprint $blueprint) {
            $blueprint->softDeletes();
        });

        Schema::table('results', function (Blueprint $blueprint) {
            $blueprint->string('user_grade')->nullable()->after('user_id');
        });
    }

    public function down(): void
    {
        Schema::table('exams', function (Blueprint $blueprint) {
            $blueprint->dropSoftDeletes();
        });

        Schema::table('results', function (Blueprint $blueprint) {
            $blueprint->dropColumn('user_grade');
        });
    }
};
