<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('contact_messages')) {
            return;
        }

        Schema::table('contact_messages', function (Blueprint $table) {
            if (! Schema::hasColumn('contact_messages', 'read_at')) {
                $table->timestamp('read_at')->nullable()->after('message');
            }
            if (! Schema::hasColumn('contact_messages', 'read_by_user_id')) {
                $table->foreignId('read_by_user_id')->nullable()->after('read_at')->constrained('users')->nullOnDelete();
            }
            if (! Schema::hasColumn('contact_messages', 'is_blocked')) {
                $table->boolean('is_blocked')->default(false)->after('read_by_user_id');
            }
            if (! Schema::hasColumn('contact_messages', 'blocked_at')) {
                $table->timestamp('blocked_at')->nullable()->after('is_blocked');
            }
            if (! Schema::hasColumn('contact_messages', 'blocked_by_user_id')) {
                $table->foreignId('blocked_by_user_id')->nullable()->after('blocked_at')->constrained('users')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('contact_messages')) {
            return;
        }

        Schema::table('contact_messages', function (Blueprint $table) {
            if (Schema::hasColumn('contact_messages', 'blocked_by_user_id')) {
                $table->dropForeign(['blocked_by_user_id']);
                $table->dropColumn('blocked_by_user_id');
            }
            if (Schema::hasColumn('contact_messages', 'blocked_at')) {
                $table->dropColumn('blocked_at');
            }
            if (Schema::hasColumn('contact_messages', 'is_blocked')) {
                $table->dropColumn('is_blocked');
            }
            if (Schema::hasColumn('contact_messages', 'read_by_user_id')) {
                $table->dropForeign(['read_by_user_id']);
                $table->dropColumn('read_by_user_id');
            }
            if (Schema::hasColumn('contact_messages', 'read_at')) {
                $table->dropColumn('read_at');
            }
        });
    }
};
