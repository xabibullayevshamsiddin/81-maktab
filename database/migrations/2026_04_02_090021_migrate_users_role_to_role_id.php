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
        if (! Schema::hasTable('users')) {
            return;
        }

        if (! Schema::hasColumn('users', 'role_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->foreignId('role_id')
                    ->nullable()
                    ->after('password')
                    ->constrained('roles')
                    ->nullOnDelete();
            });
        }

        $rolesByName = DB::table('roles')
            ->pluck('id', 'name')
            ->all();

        if (Schema::hasColumn('users', 'role')) {
            foreach ($rolesByName as $name => $id) {
                DB::table('users')
                    ->where('role', $name)
                    ->update(['role_id' => $id]);
            }

            $defaultRoleId = $rolesByName[Role::NAME_USER] ?? null;
            if ($defaultRoleId) {
                DB::table('users')
                    ->whereNull('role_id')
                    ->update(['role_id' => $defaultRoleId]);
            }

            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('role');
            });
        }

    }

    public function down(): void
    {
        if (! Schema::hasTable('users')) {
            return;
        }

        if (! Schema::hasColumn('users', 'role')) {
            Schema::table('users', function (Blueprint $table) {
                $table->enum('role', ['super_admin', 'admin', 'editor', 'moderator', 'user'])
                    ->default('user')
                    ->after('password');
            });
        }

        if (Schema::hasColumn('users', 'role_id')) {
            $rolesById = DB::table('roles')
                ->pluck('name', 'id')
                ->all();

            foreach ($rolesById as $id => $name) {
                DB::table('users')
                    ->where('role_id', $id)
                    ->update(['role' => $name]);
            }

            Schema::table('users', function (Blueprint $table) {
                $table->dropConstrainedForeignId('role_id');
            });
        }
    }
};
