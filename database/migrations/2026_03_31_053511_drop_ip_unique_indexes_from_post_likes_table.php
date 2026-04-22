<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('post_likes')) {
            return;
        }

        Schema::table('post_likes', function (Blueprint $table) {
            try { $table->dropUnique('post_likes_unique_ip'); } catch (\Exception $e) {}
            try { $table->dropIndex('post_likes_post_id_ip_address_index'); } catch (\Exception $e) {}
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('post_likes')) {
            return;
        }

        Schema::table('post_likes', function (Blueprint $table) {
            try { $table->unique(['post_id', 'ip_address'], 'post_likes_unique_ip'); } catch (\Exception $e) {}
            try { $table->index(['post_id', 'ip_address'], 'post_likes_post_id_ip_address_index'); } catch (\Exception $e) {}
        });
    }
};
