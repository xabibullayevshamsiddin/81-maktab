<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('school_classes', function (Blueprint $table): void {
            if (! Schema::hasColumn('school_classes', 'max_students')) {
                // null means unlimited; positive integer = max allowed students
                $table->unsignedSmallInteger('max_students')->nullable()->default(null)->after('sort_order');
            }
        });
    }

    public function down(): void
    {
        Schema::table('school_classes', function (Blueprint $table): void {
            if (Schema::hasColumn('school_classes', 'max_students')) {
                $table->dropColumn('max_students');
            }
        });
    }
};
