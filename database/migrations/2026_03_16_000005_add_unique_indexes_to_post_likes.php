<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('post_likes', function (Blueprint $table) {
            // Prevent duplicates for logged-in users and for guests (by IP).
            $table->unique(['post_id', 'user_id'], 'post_likes_unique_user');
            $table->unique(['post_id', 'ip_address'], 'post_likes_unique_ip');
        });
    }

    public function down(): void
    {
        Schema::table('post_likes', function (Blueprint $table) {
            $table->dropUnique('post_likes_unique_user');
            $table->dropUnique('post_likes_unique_ip');
        });
    }
};

