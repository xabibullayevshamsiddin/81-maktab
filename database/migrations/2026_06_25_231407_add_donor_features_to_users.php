<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table("users", function (Blueprint $table) {
            if (!Schema::hasColumn("users", "banner_image")) {
                $table->string("banner_image")->nullable()->after("avatar");
            }
            if (!Schema::hasColumn("users", "username_color")) {
                $table->string("username_color")->nullable()->after("donation_rank");
            }
            if (!Schema::hasColumn("users", "profile_theme")) {
                $table->string("profile_theme")->nullable()->after("username_color");
            }
        });
    }

    public function down(): void
    {
        Schema::table("users", function (Blueprint $table) {
            $table->dropColumn(["banner_image", "username_color", "profile_theme"]);
        });
    }
};