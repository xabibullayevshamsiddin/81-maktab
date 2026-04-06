<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('results') || Schema::hasColumn('results', 'option_order_json')) {
            return;
        }

        Schema::table('results', function (Blueprint $table) {
            $table->json('option_order_json')->nullable()->after('question_order_json');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('results') || ! Schema::hasColumn('results', 'option_order_json')) {
            return;
        }

        Schema::table('results', function (Blueprint $table) {
            $table->dropColumn('option_order_json');
        });
    }
};
