<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('posts')) {
            return;
        }

        if (! Schema::hasColumn('posts', 'slug')) {
            Schema::table('posts', function (Blueprint $table) {
                $table->string('slug')->nullable()->after('title');
            });
        }

        if (! Schema::hasColumn('posts', 'views')) {
            Schema::table('posts', function (Blueprint $table) {
                $table->unsignedInteger('views')->default(0)->after('image');
            });
        }

        $posts = DB::table('posts')->select('id', 'title', 'slug')->orderBy('id')->get();
        foreach ($posts as $post) {
            if (! empty($post->slug)) {
                continue;
            }

            $base = Str::slug((string) $post->title);
            $base = $base !== '' ? $base : 'post';
            $slug = $base;
            $i = 2;

            while (DB::table('posts')->where('slug', $slug)->where('id', '!=', $post->id)->exists()) {
                $slug = $base.'-'.$i;
                $i++;
            }

            DB::table('posts')->where('id', $post->id)->update(['slug' => $slug]);
        }

        if (Schema::hasColumn('posts', 'slug')) {
            Schema::table('posts', function (Blueprint $table) {
                $table->unique('slug');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('posts')) {
            return;
        }

        if (Schema::hasColumn('posts', 'slug')) {
            Schema::table('posts', function (Blueprint $table) {
                $table->dropUnique(['slug']);
                $table->dropColumn('slug');
            });
        }

        if (Schema::hasColumn('posts', 'views')) {
            Schema::table('posts', function (Blueprint $table) {
                $table->dropColumn('views');
            });
        }
    }
};

