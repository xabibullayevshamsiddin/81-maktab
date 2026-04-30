<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('feature_requests', function (Blueprint $table): void {
            if (! Schema::hasColumn('feature_requests', 'status')) {
                $table->string('status', 40)->default('pending')->index()->after('is_active');
            }
            if (! Schema::hasColumn('feature_requests', 'admin_note')) {
                $table->text('admin_note')->nullable()->after('status');
            }
            if (! Schema::hasColumn('feature_requests', 'announced_at')) {
                $table->timestamp('announced_at')->nullable()->after('admin_note');
            }
        });
    }

    public function down(): void
    {
        Schema::table('feature_requests', function (Blueprint $table): void {
            if (Schema::hasColumn('feature_requests', 'announced_at')) {
                $table->dropColumn('announced_at');
            }
            if (Schema::hasColumn('feature_requests', 'admin_note')) {
                $table->dropColumn('admin_note');
            }
            if (Schema::hasColumn('feature_requests', 'status')) {
                $table->dropColumn('status');
            }
        });
    }
};
