<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('post_likes')) {
            return;
        }

        if ($this->indexExists('post_likes', 'post_likes_unique_ip')) {
            DB::statement('ALTER TABLE `post_likes` DROP INDEX `post_likes_unique_ip`');
        }

        if ($this->indexExists('post_likes', 'post_likes_post_id_ip_address_index')) {
            DB::statement('ALTER TABLE `post_likes` DROP INDEX `post_likes_post_id_ip_address_index`');
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('post_likes')) {
            return;
        }

        if (! $this->indexExists('post_likes', 'post_likes_unique_ip')) {
            DB::statement('ALTER TABLE `post_likes` ADD UNIQUE `post_likes_unique_ip` (`post_id`, `ip_address`)');
        }

        if (! $this->indexExists('post_likes', 'post_likes_post_id_ip_address_index')) {
            DB::statement('ALTER TABLE `post_likes` ADD INDEX `post_likes_post_id_ip_address_index` (`post_id`, `ip_address`)');
        }
    }

    private function indexExists(string $table, string $indexName): bool
    {
        $result = DB::select("SHOW INDEX FROM `{$table}` WHERE Key_name = ?", [$indexName]);

        return ! empty($result);
    }
};
