<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'profile_bg_style')) {
                $table->string('profile_bg_style', 30)->default('plain');
            }
            if (!Schema::hasColumn('users', 'badge_position')) {
                $table->string('badge_position', 10)->default('after');
            }
            if (!Schema::hasColumn('users', 'banner_animation')) {
                $table->string('banner_animation', 30)->default('none');
            }
            if (!Schema::hasColumn('users', 'status_emoji')) {
                $table->string('status_emoji', 10)->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['profile_bg_style', 'badge_position', 'banner_animation', 'status_emoji']);
        });
    }
};
