<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * ADMIN USERS - MANUAL CREATION ONLY
 * 
 * This seeder is intentionally empty for security reasons.
 * Admin users must be created manually through the application
 * or via tinker with secure passwords.
 * 
 * DO NOT add default admin users with hardcoded passwords!
 * 
 * To create an admin user manually:
 * 
 * Option 1: Via Tinker
 * -----------------------
 * php artisan tinker
 * 
 * $user = App\Models\User::create([
 *     'first_name' => 'Your',
 *     'last_name' => 'Name',
 *     'email' => 'your-email@example.com',
 *     'phone' => '+998901234567',
 *     'password' => Hash::make('YourSecurePassword123!'),
 *     'role_id' => App\Models\Role::idByName('super_admin'),
 *     'is_active' => true,
 *     'email_verified_at' => now(),
 * ]);
 * 
 * $user->name = $user->buildNameFromParts();
 * $user->save();
 * 
 * Option 2: Via Register + Database Update
 * -----------------------------------------
 * 1. Register normally through the website
 * 2. Update role in database:
 *    UPDATE users SET role_id = (SELECT id FROM roles WHERE name = 'super_admin') WHERE email = 'your-email@example.com';
 */
class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->warn('═══════════════════════════════════════════════════');
        $this->command->warn('  AdminUserSeeder: NO DEFAULT USERS CREATED');
        $this->command->warn('═══════════════════════════════════════════════════');
        $this->command->info('');
        $this->command->info('For security reasons, admin users are NOT created automatically.');
        $this->command->info('Please create admin users manually:');
        $this->command->info('');
        $this->command->line('1. Register through the website');
        $this->command->line('2. Use tinker to update role:');
        $this->command->line('   php artisan tinker');
        $this->command->line('   $user = User::where(\'email\', \'your-email\')->first();');
        $this->command->line('   $user->role_id = Role::idByName(\'super_admin\');');
        $this->command->line('   $user->email_verified_at = now();');
        $this->command->line('   $user->save();');
        $this->command->info('');
    }
}
