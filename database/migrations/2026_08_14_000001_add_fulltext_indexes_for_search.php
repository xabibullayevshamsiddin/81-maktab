<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Posts — title, title_en, short_content, content ustunlari uchun FULLTEXT
        Schema::table('posts', function (Blueprint $table) {
            $table->fullText(['title', 'title_en', 'short_content']);
        });

        // Teachers — full_name, subject, lavozim ustunlari uchun FULLTEXT
        Schema::table('teachers', function (Blueprint $table) {
            $table->fullText(['full_name', 'subject', 'lavozim']);
        });

        // Courses — title, title_en, description ustunlari uchun FULLTEXT
        Schema::table('courses', function (Blueprint $table) {
            $table->fullText(['title', 'title_en', 'description']);
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropFullText(['title', 'title_en', 'short_content']);
        });

        Schema::table('teachers', function (Blueprint $table) {
            $table->dropFullText(['full_name', 'subject', 'lavozim']);
        });

        Schema::table('courses', function (Blueprint $table) {
            $table->dropFullText(['title', 'title_en', 'description']);
        });
    }
};
