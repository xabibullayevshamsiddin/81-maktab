<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('telegram_registration_verifications', function (Blueprint $table): void {
            $table->id();
            $table->string('token', 80)->unique();
            $table->string('email')->index();
            $table->string('phone', 20)->index();
            $table->json('payload');
            $table->unsignedBigInteger('telegram_user_id')->nullable()->index();
            $table->unsignedBigInteger('telegram_chat_id')->nullable()->index();
            $table->string('telegram_username')->nullable();
            $table->string('telegram_phone', 20)->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('expires_at')->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('telegram_registration_verifications');
    }
};
