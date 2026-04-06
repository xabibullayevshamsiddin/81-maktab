<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('posts')) {
            Schema::table('posts', function (Blueprint $table): void {
                $table->index('views', 'posts_views_index');
                $table->index('created_at', 'posts_created_at_index');
            });

            if (Schema::hasColumn('posts', 'post_kind')) {
                Schema::table('posts', function (Blueprint $table): void {
                    $table->index('post_kind', 'posts_post_kind_index');
                });
            }
        }

        if (
            Schema::hasTable('comments')
            && Schema::hasColumn('comments', 'post_id')
            && Schema::hasColumn('comments', 'parent_id')
            && Schema::hasColumn('comments', 'created_at')
        ) {
            Schema::table('comments', function (Blueprint $table): void {
                $table->index(['post_id', 'parent_id', 'created_at'], 'comments_post_parent_created_index');
            });
        }

        if (Schema::hasTable('teachers') && Schema::hasColumn('teachers', 'is_active')) {
            Schema::table('teachers', function (Blueprint $table): void {
                $table->index('is_active', 'teachers_is_active_index');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('posts')) {
            Schema::table('posts', function (Blueprint $table): void {
                $table->dropIndex('posts_views_index');
                $table->dropIndex('posts_created_at_index');
            });

            if (Schema::hasColumn('posts', 'post_kind')) {
                Schema::table('posts', function (Blueprint $table): void {
                    $table->dropIndex('posts_post_kind_index');
                });
            }
        }

        if (Schema::hasTable('comments')) {
            Schema::table('comments', function (Blueprint $table): void {
                $table->dropIndex('comments_post_parent_created_index');
            });
        }

        if (Schema::hasTable('teachers') && Schema::hasColumn('teachers', 'is_active')) {
            Schema::table('teachers', function (Blueprint $table): void {
                $table->dropIndex('teachers_is_active_index');
            });
        }
    }
};
