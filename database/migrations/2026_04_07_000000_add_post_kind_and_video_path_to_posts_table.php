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

        Schema::table('posts', function (Blueprint $table) {
            if (! Schema::hasColumn('posts', 'post_kind')) {
                if (Schema::hasColumn('posts', 'category_id')) {
                    $table->string('post_kind', 32)->default('general')->after('category_id');
                } else {
                    $table->string('post_kind', 32)->default('general');
                }
            }
            if (! Schema::hasColumn('posts', 'video_path')) {
                if (Schema::hasColumn('posts', 'video_url')) {
                    $table->string('video_path', 500)->nullable()->after('video_url');
                } else {
                    $table->string('video_path', 500)->nullable();
                }
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('posts')) {
            return;
        }

        Schema::table('posts', function (Blueprint $table) {
            if (Schema::hasColumn('posts', 'video_path')) {
                $table->dropColumn('video_path');
            }
            if (Schema::hasColumn('posts', 'post_kind')) {
                $table->dropColumn('post_kind');
            }
        });
    }
};
