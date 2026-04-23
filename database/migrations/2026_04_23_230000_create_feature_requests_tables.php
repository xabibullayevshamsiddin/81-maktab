<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('feature_requests', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title', 180);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('feature_request_votes', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('feature_request_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['feature_request_id', 'user_id'], 'feature_request_user_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feature_request_votes');
        Schema::dropIfExists('feature_requests');
    }
};
