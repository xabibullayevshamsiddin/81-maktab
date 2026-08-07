<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BlockedUserAutoExpireTest extends TestCase
{
    use RefreshDatabase;

    private function createUserWithRole(): User
    {
        $role = Role::firstOrCreate(['name' => 'user'], ['level' => 1]);
        return User::factory()->create(['role_id' => $role->id]);
    }

    /**
     * Test: Foydalanuvchi bloklanganda va blocked_until o'tganda — blok ochilishi kerak
     */
    public function test_blocked_user_is_unblocked_when_blocked_until_is_past(): void
    {
        $user = $this->createUserWithRole();

        // Foydalanuvchini 1 soat oldin blokla (ya'ni blok allaqachon tugagan)
        $user->update([
            'is_blocked' => true,
            'blocked_until' => now()->subHour(),
            'blocked_reason' => 'Test blok',
        ]);

        // isCurrentlyBlocked() chaqirilganda blok ochilishi kerak
        $this->assertFalse($user->isCurrentlyBlocked());

        // Bazadan qayta o'qiymiz
        $user->refresh();
        $this->assertFalse($user->is_blocked);
        $this->assertNull($user->blocked_until);
        $this->assertNull($user->blocked_reason);
    }

    /**
     * Test: Hali blok muddati tugamagan bo'lsa — blok qolishi kerak
     */
    public function test_blocked_user_remains_blocked_when_blocked_until_is_future(): void
    {
        $user = $this->createUserWithRole();

        // Foydalanuvchini 1 soatga blokla (hali tugamagan)
        $user->update([
            'is_blocked' => true,
            'blocked_until' => now()->addHour(),
            'blocked_reason' => 'Test blok',
        ]);

        // isCurrentlyBlocked() true qaytarishi kerak
        $this->assertTrue($user->isCurrentlyBlocked());

        // Bazadan qayta o'qiymiz — blok hali joyida
        $user->refresh();
        $this->assertTrue($user->is_blocked);
        $this->assertNotNull($user->blocked_until);
    }

    /**
     * Test: Doimiy bloklangan foydalanuvchi (blocked_until = null) — doimiy bloklangan
     */
    public function test_permanently_blocked_user_remains_blocked(): void
    {
        $user = $this->createUserWithRole();

        // Doimiy blok (blocked_until yo'q)
        $user->update([
            'is_blocked' => true,
            'blocked_until' => null,
            'blocked_reason' => 'Doimiy blok',
        ]);

        // isCurrentlyBlocked() true qaytarishi kerak
        $this->assertTrue($user->isCurrentlyBlocked());

        // Blok hali joyida
        $user->refresh();
        $this->assertTrue($user->is_blocked);
    }

    /**
     * Test: Bloklanmagan foydalanuvchi — false qaytarishi kerak
     */
    public function test_non_blocked_user_returns_false(): void
    {
        $user = $this->createUserWithRole();

        $this->assertFalse($user->isCurrentlyBlocked());
    }

    /**
     * Test: Bir nechta bloklangan foydalanuvchilarni tozalash
     */
    public function test_unblock_command_handles_multiple_users(): void
    {
        // Muddati tugagan bloklangan foydalanuvchilar
        $expiredUsers = collect();
        for ($i = 0; $i < 3; $i++) {
            $user = $this->createUserWithRole();
            $user->update([
                'is_blocked' => true,
                'blocked_until' => now()->subHours($i + 1),
                'blocked_reason' => "Tugagan blok {$i}",
            ]);
            $expiredUsers->push($user);
        }

        // Hali blok muddati tugamagan foydalanuvchi
        $activeUser = $this->createUserWithRole();
        $activeUser->update([
            'is_blocked' => true,
            'blocked_until' => now()->addHour(),
            'blocked_reason' => 'Faol blok',
        ]);

        // Command'ni ishga tushirish
        $this->artisan('users:unblock-expired')
            ->assertExitCode(0);

        // Muddati tugagan foydalanuvchilar ochilgan
        foreach ($expiredUsers as $user) {
            $user->refresh();
            $this->assertFalse($user->is_blocked);
            $this->assertNull($user->blocked_until);
        }

        // Hali blok muddati tugamagan foydalanuvchi — bloklangan
        $activeUser->refresh();
        $this->assertTrue($activeUser->is_blocked);
        $this->assertNotNull($activeUser->blocked_until);
    }

    /**
     * Test: isCurrentlyBlocked() to'g'ri ishlashi — bir nechta holat
     */
    public function test_isCurrentlyBlocked_various_scenarios(): void
    {
        $user = $this->createUserWithRole();

        // 1. Bloklanmagan
        $this->assertFalse($user->isCurrentlyBlocked());

        // 2. Doimiy bloklangan
        $user->update(['is_blocked' => true, 'blocked_until' => null]);
        $this->assertTrue($user->isCurrentlyBlocked());

        // 3. Vaqtincha bloklangan (hali tugamagan)
        $user->update(['is_blocked' => true, 'blocked_until' => now()->addHour()]);
        $this->assertTrue($user->isCurrentlyBlocked());

        // 4. Blok muddati tugagan
        $user->update(['is_blocked' => true, 'blocked_until' => now()->subHour()]);
        $this->assertFalse($user->isCurrentlyBlocked());

        // 5. Bazadan tekshirish — avtomatik tozalangan
        $user->refresh();
        $this->assertFalse($user->is_blocked);
        $this->assertNull($user->blocked_until);
    }

    /**
     * Test: UnblockExpiredUsers command to'g'ri ishlashi
     */
    public function test_unblock_expired_command_works(): void
    {
        // Muddati tugagan bloklangan foydalanuvchi
        $expiredUser = $this->createUserWithRole();
        $expiredUser->update([
            'is_blocked' => true,
            'blocked_until' => now()->subHour(),
            'blocked_reason' => 'Tugagan blok',
        ]);

        // Hali blok muddati tugamagan foydalanuvchi
        $activeUser = $this->createUserWithRole();
        $activeUser->update([
            'is_blocked' => true,
            'blocked_until' => now()->addHour(),
            'blocked_reason' => 'Faol blok',
        ]);

        // Command'ni ishga tushirish
        $this->artisan('users:unblock-expired')
            ->assertExitCode(0);

        // Muddati tugagan foydalanuvchi ochilgan
        $expiredUser->refresh();
        $this->assertFalse($expiredUser->is_blocked);
        $this->assertNull($expiredUser->blocked_until);

        // Hali blok muddati tugamagan foydalanuvchi — bloklangan
        $activeUser->refresh();
        $this->assertTrue($activeUser->is_blocked);
        $this->assertNotNull($activeUser->blocked_until);
    }
}
