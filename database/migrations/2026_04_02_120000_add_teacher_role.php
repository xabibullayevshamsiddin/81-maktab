<?php

use App\Models\Role;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (! DB::table('roles')->where('name', Role::NAME_TEACHER)->exists()) {
            DB::table('roles')->insert([
                'name' => Role::NAME_TEACHER,
                'label' => 'O\'qituvchi',
                'level' => 2,
                'is_system' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        if (DB::table('roles')->where('name', Role::NAME_TEACHER)->exists()) {
            DB::table('roles')->where('name', Role::NAME_TEACHER)->delete();
        }
    }
};

