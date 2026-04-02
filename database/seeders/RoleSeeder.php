<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        foreach (Role::defaultRoles() as $role) {
            Role::updateOrCreate(
                ['name' => $role['name']],
                [
                    'label' => $role['label'],
                    'level' => $role['level'],
                    'is_system' => true,
                ]
            );
        }
    }
}
