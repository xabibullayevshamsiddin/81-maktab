<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_interactions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('contact_message_id')->nullable()->constrained()->nullOnDelete();
            $table->text('question');
            $table->string('normalized_question', 255)->nullable()->index();
            $table->text('response_text')->nullable();
            $table->string('response_source', 80)->nullable()->index();
            $table->string('response_type', 80)->nullable()->index();
            $table->string('user_role', 50)->nullable()->index();
            $table->string('suggested_route', 120)->nullable()->index();
            $table->string('suggested_url', 500)->nullable();
            $table->boolean('is_unanswered')->default(false)->index();
            $table->boolean('clarification_requested')->default(false)->index();
            $table->boolean('support_converted')->default(false)->index();
            $table->boolean('is_helpful')->nullable()->index();
            $table->json('meta')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_interactions');
    }
};
