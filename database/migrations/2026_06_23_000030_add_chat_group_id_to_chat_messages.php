<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('chat_messages', function (Blueprint $table) {
            $table->foreignId('chat_group_id')->nullable()->after('user_id')->constrained('chat_groups')->nullOnDelete();
            $table->index('chat_group_id');
        });
    }

    public function down(): void
    {
        Schema::table('chat_messages', function (Blueprint $table) {
            $table->dropForeign(['chat_group_id']);
            $table->dropIndex(['chat_group_id']);
            $table->dropColumn('chat_group_id');
        });
    }
};
