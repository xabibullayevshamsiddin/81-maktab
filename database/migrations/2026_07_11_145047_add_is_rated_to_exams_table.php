<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('exams', function (Blueprint $table) {
            // Reyting imtihoni — faqat admin belgilaydi.
            // Ball faqat shu imtihonlar uchun Gamification badjlarga kiritiladi.
            $table->boolean('is_rated')->default(false)->after('security_enabled');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('exams', function (Blueprint $table) {
            $table->dropColumn('is_rated');
        });
    }
};
