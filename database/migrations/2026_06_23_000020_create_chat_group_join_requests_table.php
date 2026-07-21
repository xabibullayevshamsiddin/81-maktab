<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chat_group_join_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chat_group_id')->constrained('chat_groups')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('status', 24)->default('pending');
            $table->timestamps();

            $table->unique(['chat_group_id', 'user_id'], 'chat_group_join_request_unique');
            $table->index(['status', 'chat_group_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_group_join_requests');
    }
};
