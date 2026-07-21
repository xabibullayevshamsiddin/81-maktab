<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Users jadvaliga rank ustuni qo'shamiz
        if (!Schema::hasColumn("users", "donation_rank")) {
            Schema::table("users", function (Blueprint $table) {
                $table->string("donation_rank")->nullable()->after("is_parent")
                    ->comment("supporter, premium, vip");
                $table->timestamp("donation_rank_expires_at")->nullable()->after("donation_rank");
                $table->unsignedBigInteger("total_donated")->default(0)->after("donation_rank_expires_at");
            });
        }

        // 2. Donations jadvali (to'lovlar tarixi)
        if (!Schema::hasTable("donations")) {
            Schema::create("donations", function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger("user_id");
                $table->string("rank")->comment("supporter, premium, vip");
                $table->unsignedInteger("amount")->comment("to'lov summasi (so'm)");
                $table->string("payment_system")->nullable()->comment("click, payme, stripe, etc.");
                $table->string("payment_id")->nullable()->comment("to'lov tizimidagi ID");
                $table->string("status")->default("pending")->comment("pending, completed, failed, refunded");
                $table->timestamp("paid_at")->nullable();
                $table->timestamp("expires_at")->nullable();
                $table->json("meta")->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists("donations");

        if (Schema::hasColumn("users", "donation_rank")) {
            Schema::table("users", function (Blueprint $table) {
                $table->dropColumn(["donation_rank", "donation_rank_expires_at", "total_donated"]);
            });
        }
    }
};