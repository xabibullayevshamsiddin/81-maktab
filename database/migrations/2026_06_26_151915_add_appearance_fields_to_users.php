<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table("users", function (Blueprint $table) {
            if (!Schema::hasColumn("users", "badge_style")) {
                $table->string("badge_style", 30)->nullable()->after("profile_theme");
            }
            if (!Schema::hasColumn("users", "comment_style")) {
                $table->string("comment_style", 30)->nullable()->after("badge_style");
            }
            if (!Schema::hasColumn("users", "chat_style")) {
                $table->string("chat_style", 30)->nullable()->after("comment_style");
            }
            if (!Schema::hasColumn("users", "show_expiry_badge")) {
                $table->string("show_expiry_badge", 5)->default("1")->after("chat_style");
            }
            if (!Schema::hasColumn("users", "name_font_weight")) {
                $table->string("name_font_weight", 5)->default("700")->after("show_expiry_badge");
            }
        });
    }

    public function down(): void
    {
        Schema::table("users", function (Blueprint $table) {
            $table->dropColumn(["badge_style", "comment_style", "chat_style", "show_expiry_badge", "name_font_weight"]);
        });
    }
};