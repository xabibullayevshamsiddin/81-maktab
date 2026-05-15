<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('ai_knowledges')) {
            return;
        }

        Schema::table('ai_knowledges', function (Blueprint $table): void {
            if (! Schema::hasColumn('ai_knowledges', 'synonyms')) {
                $table->text('synonyms')->nullable()->after('keywords');
            }

            if (! Schema::hasColumn('ai_knowledges', 'priority')) {
                $table->integer('priority')->default(0)->after('is_active');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('ai_knowledges')) {
            return;
        }

        Schema::table('ai_knowledges', function (Blueprint $table): void {
            if (Schema::hasColumn('ai_knowledges', 'synonyms')) {
                $table->dropColumn('synonyms');
            }

            if (Schema::hasColumn('ai_knowledges', 'priority')) {
                $table->dropColumn('priority');
            }
        });
    }
};
