<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // MySQL-specific: allow long post body.
        DB::statement('ALTER TABLE posts MODIFY content TEXT');
    }

    public function down(): void
    {
        // Revert back to VARCHAR(255) (previous behavior).
        DB::statement('ALTER TABLE posts MODIFY content VARCHAR(255)');
    }
};

