<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table): void {
            $table->string('name_en')->nullable()->after('name');
        });

        Schema::table('posts', function (Blueprint $table): void {
            $table->string('title_en')->nullable()->after('title');
            $table->text('short_content_en')->nullable()->after('short_content');
            $table->longText('content_en')->nullable()->after('content');
        });

        Schema::table('teachers', function (Blueprint $table): void {
            $table->string('subject_en')->nullable()->after('subject');
            $table->text('achievements_en')->nullable()->after('achievements');
            $table->text('bio_en')->nullable()->after('bio');
        });

        Schema::table('courses', function (Blueprint $table): void {
            $table->string('title_en')->nullable()->after('title');
            $table->string('price_en')->nullable()->after('price');
            $table->string('duration_en')->nullable()->after('duration');
            $table->text('description_en')->nullable()->after('description');
        });

        Schema::table('calendar_events', function (Blueprint $table): void {
            $table->string('title_en')->nullable()->after('title');
            $table->text('body_en')->nullable()->after('body');
            $table->string('time_note_en')->nullable()->after('time_note');
        });
    }

    public function down(): void
    {
        Schema::table('calendar_events', function (Blueprint $table): void {
            $table->dropColumn(['title_en', 'body_en', 'time_note_en']);
        });

        Schema::table('courses', function (Blueprint $table): void {
            $table->dropColumn(['title_en', 'price_en', 'duration_en', 'description_en']);
        });

        Schema::table('teachers', function (Blueprint $table): void {
            $table->dropColumn(['subject_en', 'achievements_en', 'bio_en']);
        });

        Schema::table('posts', function (Blueprint $table): void {
            $table->dropColumn(['title_en', 'short_content_en', 'content_en']);
        });

        Schema::table('categories', function (Blueprint $table): void {
            $table->dropColumn(['name_en']);
        });
    }
};
