<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('post_likes')) {
            return;
        }

        Schema::create('post_likes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->constrained('posts')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 255)->nullable();
            $table->timestamps();

            $table->unique(['post_id', 'user_id']);
            $table->unique(['post_id', 'ip_address'], 'post_likes_unique_ip');
            $table->index(['post_id', 'ip_address'], 'post_likes_post_id_ip_address_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('post_likes');
    }
};
