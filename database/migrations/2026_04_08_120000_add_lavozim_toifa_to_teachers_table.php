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

        Schema::table('teachers', function (Blueprint $table): void {
            if (! Schema::hasColumn('teachers', 'lavozim')) {
                $table->string('lavozim')->nullable()->after('subject');
            }
            if (! Schema::hasColumn('teachers', 'lavozim_en')) {
                $table->string('lavozim_en')->nullable()->after('lavozim');
            }
            if (! Schema::hasColumn('teachers', 'toifa')) {
                $table->string('toifa')->nullable()->after('lavozim_en');
            }
            if (! Schema::hasColumn('teachers', 'toifa_en')) {
                $table->string('toifa_en')->nullable()->after('toifa');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('teachers')) {
            return;
        }

        Schema::table('teachers', function (Blueprint $table): void {
            $drop = [];
            foreach (['lavozim', 'lavozim_en', 'toifa', 'toifa_en'] as $col) {
                if (Schema::hasColumn('teachers', $col)) {
                    $drop[] = $col;
                }
            }
            if ($drop !== []) {
                $table->dropColumn($drop);
            }
        });
    }
};
