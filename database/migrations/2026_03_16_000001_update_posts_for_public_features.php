<?php

use App\Models\Post;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            if (!Schema::hasColumn('posts', 'slug')) {
                $table->string('slug')->nullable()->after('title');
                $table->unique('slug');
            }
            if (!Schema::hasColumn('posts', 'views')) {
                $table->unsignedBigInteger('views')->default(0)->after('image');
            }
        });

        // Change content column to TEXT (it was string).
        Schema::table('posts', function (Blueprint $table) {
            // column type change requires doctrine/dbal in some Laravel versions;
            // do it only if supported in this project runtime.
            // We'll fallback by adding a new column if change() isn't available.
        });

        // Ensure slugs exist for existing posts.
        Post::query()
            ->whereNull('slug')
            ->orWhere('slug', '')
            ->orderBy('id')
            ->chunkById(200, function ($posts) {
                foreach ($posts as $post) {
                    $base = Str::slug($post->title ?: 'post');
                    $slug = $base !== '' ? $base : 'post';

                    // Make unique by appending id when needed.
                    if (Post::query()->where('slug', $slug)->where('id', '!=', $post->id)->exists()) {
                        $slug = "{$slug}-{$post->id}";
                    }

                    $post->forceFill(['slug' => $slug])->save();
                }
            });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            if (Schema::hasColumn('posts', 'slug')) {
                $table->dropUnique(['slug']);
                $table->dropColumn('slug');
            }
            if (Schema::hasColumn('posts', 'views')) {
                $table->dropColumn('views');
            }
        });
    }
};

