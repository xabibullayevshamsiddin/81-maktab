<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('role_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique('user_id');
            $table->index('role_id');
        });

        if (! Schema::hasTable('users')) {
            return;
        }

        $rows = DB::table('users')
            ->whereNotNull('role_id')
            ->get(['id', 'role_id']);

        if ($rows->isEmpty()) {
            return;
        }

        $now = now();
        $payload = $rows->map(static function ($row) use ($now) {
            return [
                'user_id' => $row->id,
                'role_id' => $row->role_id,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        })->all();

        DB::table('roles_user')->insert($payload);
    }

    public function down(): void
    {
        Schema::dropIfExists('roles_user');
    }
};
