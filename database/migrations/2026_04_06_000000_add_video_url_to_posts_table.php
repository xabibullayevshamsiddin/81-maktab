<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('posts')) {
            return;
        }

        if (! Schema::hasColumn('posts', 'video_url')) {
            Schema::table('posts', function (Blueprint $table) {
                $table->string('video_url', 500)->nullable()->after('image');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('posts') && Schema::hasColumn('posts', 'video_url')) {
            Schema::table('posts', function (Blueprint $table) {
                $table->dropColumn('video_url');
            });
        }
    }
};
