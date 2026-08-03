<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('comments', function (Blueprint $table) {
            $table->foreignId('parent_id')->nullable()->constrained('comments')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('comments', function (Blueprint $table) {
            // SQLite dropForeign'ni qo'llab-quvvatlamaydi (testlar uchun)
            if (Schema::getConnection()->getDriverName() !== 'sqlite') {
                $table->dropForeign(['parent_id']);
            }
            $table->dropColumn('parent_id');
        });
    }
};
