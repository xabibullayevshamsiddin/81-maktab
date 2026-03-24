<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            if (!Schema::hasColumn('posts', 'category_id')) {
                $table->foreignId('category_id')->nullable()->after('title')->constrained('categories')->nullOnDelete();
            }
        });

        // Migrate old string category values to categories table and bind posts.
        if (Schema::hasColumn('posts', 'category')) {
            $names = DB::table('posts')
                ->whereNotNull('category')
                ->where('category', '!=', '')
                ->distinct()
                ->pluck('category');

            foreach ($names as $name) {
                DB::table('categories')->updateOrInsert(
                    ['name' => $name],
                    ['name' => $name, 'updated_at' => now(), 'created_at' => now()]
                );
            }

            $categories = DB::table('categories')->pluck('id', 'name');

            $posts = DB::table('posts')
                ->select('id', 'category')
                ->whereNotNull('category')
                ->where('category', '!=', '')
                ->get();

            foreach ($posts as $post) {
                $categoryId = $categories[$post->category] ?? null;
                if ($categoryId) {
                    DB::table('posts')->where('id', $post->id)->update(['category_id' => $categoryId]);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            if (Schema::hasColumn('posts', 'category_id')) {
                $table->dropConstrainedForeignId('category_id');
            }
        });
    }
};

