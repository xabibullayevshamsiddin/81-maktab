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

        if (Schema::hasColumn('contact_messages', 'topic')) {
            Schema::table('contact_messages', function (Blueprint $table) {
                $table->dropColumn('topic');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('contact_messages')) {
            return;
        }

        if (! Schema::hasColumn('contact_messages', 'topic')) {
            Schema::table('contact_messages', function (Blueprint $table) {
                $table->string('topic', 255)->nullable()->after('phone');
            });
        }
    }
};
