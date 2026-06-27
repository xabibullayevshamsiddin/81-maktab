<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create("activation_keys", function (Blueprint $table) {
            $table->id();
            $table->string("code", 20)->unique()->index();
            $table->string("rank")->comment("supporter, premium, vip");
            $table->string("duration")->comment("1month, 3months, 1year");
            $table->unsignedInteger("duration_days");
            $table->unsignedBigInteger("generated_by")->nullable()->comment("Admin user_id");
            $table->unsignedBigInteger("used_by")->nullable()->comment("Foydalanuvchi user_id");
            $table->timestamp("used_at")->nullable();
            $table->timestamp("expires_at")->nullable();
            $table->boolean("is_used")->default(false);
            $table->timestamps();
        });

        // usersga telegram_userni qoshish
        if (!Schema::hasColumn("users", "telegram")) {
            Schema::table("users", function (Blueprint $table) {
                $table->string("telegram")->nullable()->after("email");
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists("activation_keys");
        if (Schema::hasColumn("users", "telegram")) {
            Schema::table("users", function (Blueprint $table) {
                $table->dropColumn("telegram");
            });
        }
    }
};