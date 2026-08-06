<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_blocked')->default(false)->after('email_verified_at');
            $table->timestamp('blocked_until')->nullable()->after('is_blocked');
            $table->text('blocked_reason')->nullable()->after('blocked_until');
            $table->unsignedBigInteger('blocked_by')->nullable()->after('blocked_reason');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['is_blocked', 'blocked_until', 'blocked_reason', 'blocked_by']);
        });
    }
};
