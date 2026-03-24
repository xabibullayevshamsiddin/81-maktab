<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@81maktab.uz'],
            [
                'name' => 'Super Admin',
                'phone' => '+998901234567',
                'password' => Hash::make('admin123'),
                'role' => 'super_admin',
                'is_active' => true,
            ]
        );

        User::updateOrCreate(
            ['email' => 'editor@81maktab.uz'],
            [
                'name' => 'Editor',
                'phone' => '+998901234568',
                'password' => Hash::make('editor123'),
                'role' => 'editor',
                'is_active' => true,
            ]
        );

        User::updateOrCreate(
            ['email' => 'moderator@81maktab.uz'],
            [
                'name' => 'Moderator',
                'phone' => '+998901234569',
                'password' => Hash::make('moderator123'),
                'role' => 'moderator',
                'is_active' => true,
            ]
        );
    }
}
