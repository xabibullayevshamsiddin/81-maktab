<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('users', 'course_open_request_reason')) {
            return;
        }

        Schema::table('users', function (Blueprint $table): void {
            $table->text('course_open_request_reason')->nullable()->after('course_open_requested_at');
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('users', 'course_open_request_reason')) {
            return;
        }

        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn('course_open_request_reason');
        });
    }
};
