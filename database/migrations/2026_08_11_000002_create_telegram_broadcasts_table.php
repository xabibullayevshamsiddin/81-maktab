<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('telegram_broadcasts')) {
            return;
        }

        Schema::create('telegram_broadcasts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('admin_id')->constrained('users')->cascadeOnDelete();
            $table->text('message');
            $table->string('audience')->comment('all, teachers, donors, students');
            $table->integer('total_recipients')->default(0);
            $table->integer('sent_count')->default(0);
            $table->integer('failed_count')->default(0);
            $table->integer('skipped_count')->default(0);
            $table->string('status')->default('pending')->comment('pending, sending, completed, partial');
            $table->timestamps();

            $table->index(['admin_id', 'created_at']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('telegram_broadcasts');
    }
};
