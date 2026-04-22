<?php

use App\Models\Role;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('label');
            $table->unsignedTinyInteger('level')->default(1);
            $table->boolean('is_system')->default(true);
            $table->timestamps();
        });

        DB::table('roles')->insert(array_map(static function (array $role) {
            return [
                'name' => $role['name'],
                'label' => $role['label'],
                'level' => $role['level'],
                'is_system' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }, Role::defaultRoles()));
    }

    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};
