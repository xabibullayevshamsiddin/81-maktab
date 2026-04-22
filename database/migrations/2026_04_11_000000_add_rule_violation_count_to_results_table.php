<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('results', function (Blueprint $table) {
            if (! Schema::hasColumn('results', 'rule_violation_count')) {
                $table->unsignedSmallInteger('rule_violation_count')->default(0);
            }
        });
    }

    public function down(): void
    {
        Schema::table('results', function (Blueprint $table) {
            if (Schema::hasColumn('results', 'rule_violation_count')) {
                $table->dropColumn('rule_violation_count');
            }
        });
    }
};
