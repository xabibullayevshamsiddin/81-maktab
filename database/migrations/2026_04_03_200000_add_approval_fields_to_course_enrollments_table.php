<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('course_enrollments', function (Blueprint $table) {
            $table->string('status', 32)->default('pending')->after('note');
            $table->string('contact_phone', 40)->nullable()->after('status');
            $table->string('grade', 32)->nullable()->after('contact_phone');
            $table->string('subject_level', 120)->nullable()->after('grade');
            $table->timestamp('reviewed_at')->nullable()->after('subject_level');
            $table->foreignId('reviewed_by')->nullable()->after('reviewed_at')->constrained('users')->nullOnDelete();
        });

        DB::table('course_enrollments')->update(['status' => 'approved']);
    }

    public function down(): void
    {
        Schema::table('course_enrollments', function (Blueprint $table) {
            $table->dropForeign(['reviewed_by']);
            $table->dropColumn(['reviewed_by', 'reviewed_at', 'subject_level', 'grade', 'contact_phone', 'status']);
        });
    }
};
