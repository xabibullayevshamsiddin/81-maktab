<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('teachers') || ! Schema::hasColumn('teachers', 'subject')) {
            return;
        }

        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        Schema::table('teachers', function (Blueprint $table): void {
            $table->string('subject')->nullable()->change();
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('teachers') || ! Schema::hasColumn('teachers', 'subject')) {
            return;
        }

        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        Schema::table('teachers', function (Blueprint $table): void {
            $table->string('subject')->nullable(false)->change();
        });
    }
};
