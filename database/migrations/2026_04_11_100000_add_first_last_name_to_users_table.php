<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'first_name')) {
                $table->string('first_name', 120)->default('')->after('name');
            }
            if (! Schema::hasColumn('users', 'last_name')) {
                $table->string('last_name', 120)->default('')->after('first_name');
            }
        });

        DB::table('users')
            ->where('first_name', '')
            ->orderBy('id')
            ->each(function ($user) {
                $parts = preg_split('/\s+/', trim($user->name), 2);
                DB::table('users')->where('id', $user->id)->update([
                    'first_name' => $parts[0] ?? '',
                    'last_name' => $parts[1] ?? '',
                ]);
            });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'first_name')) {
                $table->dropColumn('first_name');
            }
            if (Schema::hasColumn('users', 'last_name')) {
                $table->dropColumn('last_name');
            }
        });
    }
};
