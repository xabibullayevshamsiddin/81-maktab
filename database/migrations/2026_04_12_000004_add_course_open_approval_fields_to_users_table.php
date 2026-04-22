<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->boolean('course_open_approved')->default(false)->after('is_active');
            $table->boolean('course_open_request_pending')->default(false)->after('course_open_approved');
            $table->timestamp('course_open_requested_at')->nullable()->after('course_open_request_pending');
            $table->timestamp('course_open_approved_at')->nullable()->after('course_open_requested_at');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn([
                'course_open_approved',
                'course_open_request_pending',
                'course_open_requested_at',
                'course_open_approved_at',
            ]);
        });
    }
};
