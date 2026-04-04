<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('posts') || ! Schema::hasColumn('posts', 'content')) {
            return;
        }

        $driver = Schema::getConnection()->getDriverName();

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            DB::statement('ALTER TABLE posts MODIFY content TEXT NOT NULL');
        }

        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE posts ALTER COLUMN content TYPE TEXT');
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('posts') || ! Schema::hasColumn('posts', 'content')) {
            return;
        }

        $driver = Schema::getConnection()->getDriverName();

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            DB::statement('ALTER TABLE posts MODIFY content VARCHAR(255) NOT NULL');
        }

        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE posts ALTER COLUMN content TYPE VARCHAR(255)');
        }
    }
};
