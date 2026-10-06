<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Group Chat funksiyasini butunlay olib tashlash.
 *
 * MUHIM: Global Chat xabarlari (chat_messages.chat_group_id = NULL) TO'LIQ SAQLANADI.
 * Faqat guruh xabarlari (chat_group_id NOT NULL) va guruh jadvallari o'chiriladi.
 */
return new class extends Migration
{
    public function up(): void
    {
        // 1-qadam: faqat guruh xabarlarini o'chir (Global Chat xabarlari chat_group_id = NULL bo'lgani uchun saqlanadi)
        if (Schema::hasColumn('chat_messages', 'chat_group_id')) {
            DB::table('chat_messages')
                ->whereNotNull('chat_group_id')
                ->delete();

            // 2-qadam: chat_messages.chat_group_id ustunini olib tashlash (avval FK va index, keyin column)
            Schema::table('chat_messages', function (Blueprint $table) {
                // SQLite dropForeign'ni qo'llab-quvvatlamaydi (testlar uchun)
                if (Schema::getConnection()->getDriverName() !== 'sqlite') {
                    $table->dropForeign(['chat_group_id']);
                }
                $table->dropIndex(['chat_group_id']);
                $table->dropColumn('chat_group_id');
            });
        }

        // 3-qadam: guruh jadvallarini FK tartibida o'chirish
        Schema::dropIfExists('chat_group_join_requests');
        Schema::dropIfExists('chat_group_members');
        Schema::dropIfExists('chat_groups');
    }

    /**
     * Rollback: faqat SXEMANI qaytaradi. Group Chat MA'LUMOTLARI (guruhlar, a'zolar,
     * so'rovlar va guruh xabarlari) butunlay yo'qoladi — ularni tiklash mumkin emas.
     */
    public function down(): void
    {
        Schema::create('chat_groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
            $table->string('name', 120);
            $table->text('description')->nullable();
            $table->timestamps();

            $table->unique(['owner_id'], 'chat_groups_owner_unique');
            $table->index('name');
        });

        Schema::create('chat_group_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chat_group_id')->constrained('chat_groups')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('role', 20)->default('member');
            $table->timestamps();

            $table->unique(['chat_group_id', 'user_id'], 'chat_group_member_unique');
            $table->index(['user_id', 'chat_group_id']);
        });

        Schema::create('chat_group_join_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chat_group_id')->constrained('chat_groups')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('status', 24)->default('pending');
            $table->timestamps();

            $table->unique(['chat_group_id', 'user_id'], 'chat_group_join_request_unique');
            $table->index(['status', 'chat_group_id']);
        });

        Schema::table('chat_messages', function (Blueprint $table) {
            $table->foreignId('chat_group_id')->nullable()->after('user_id')->constrained('chat_groups')->nullOnDelete();
            $table->index('chat_group_id');
        });
    }
};
