<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('telegram_verifications', function (Blueprint $table) {
            $table->id();
            $table->string('token', 40)->unique();
            $table->string('purpose'); // login | register | password_reset
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('email')->index();
            $table->string('phone')->index();
            $table->json('session_payload')->nullable();
            $table->bigInteger('telegram_chat_id')->nullable();
            $table->string('status')->default('pending'); // pending | verified | expired | completed
            $table->timestamp('expires_at')->index();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('telegram_verifications');
    }
};
