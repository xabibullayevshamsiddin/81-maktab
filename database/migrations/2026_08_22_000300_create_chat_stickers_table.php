<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chat_stickers', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();           // masalan "fire_gold"
            $table->string('image_path');               // masalan "stickers/donor/fire_gold.png"
            $table->string('category')->default('umumiy');
            $table->boolean('is_donor_only')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_stickers');
    }
};
