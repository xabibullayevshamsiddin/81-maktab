<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('donor_warnings')) {
            return;
        }

        Schema::create('donor_warnings', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('rank')->comment('supporter, premium, vip');
            $table->integer('days_before')->comment('necha kun qoldi: 3 yoki 1');
            $table->boolean('notified_in_app')->default(false);
            $table->boolean('notified_telegram')->default(false);
            $table->timestamp('expires_at')->comment('donor muddati tugash sanasi');
            $table->timestamps();

            $table->unique(['user_id', 'days_before', 'expires_at'], 'donor_warning_unique');
            $table->index(['expires_at', 'days_before']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('donor_warnings');
    }
};
