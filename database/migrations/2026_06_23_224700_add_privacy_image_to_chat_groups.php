<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('chat_groups', function (Blueprint $table) {
            $table->string('privacy', 16)->default('closed')->after('description');
            $table->string('image', 255)->nullable()->after('privacy');
        });

        Schema::table('chat_group_members', function (Blueprint $table) {
            $table->string('role', 32)->default('member')->after('user_id');
        });
    }

    public function down(): void
    {
        Schema::table('chat_groups', function (Blueprint $table) {
            $table->dropColumn(['privacy', 'image']);
        });

        Schema::table('chat_group_members', function (Blueprint $table) {
            $table->dropColumn('role');
        });
    }
};
