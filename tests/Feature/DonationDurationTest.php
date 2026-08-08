<?php

namespace Tests\Feature;

use App\Models\ActivationKey;
use App\Models\Donation;
use App\Models\User;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DonationDurationTest extends TestCase
{
    use RefreshDatabase;

    private function createUserWithRole(): User
    {
        $role = Role::firstOrCreate(['name' => 'user'], ['level' => 1]);
        return User::factory()->create(['role_id' => $role->id]);
    }

    /**
     * Test: 1 oylikdonat — 30 kun qo'shilishi kerak
     */
    public function test_one_month_donation_sets_correct_expiry(): void
    {
        $user = $this->createUserWithRole();
        $initialTime = now();

        $user->activateDonationRank(
            Donation::RANK_SUPPORTER,
            15000,
            'manual',
            null,
            30  // 1 oy = 30 kun
        );

        $user->refresh();
        $this->assertNotNull($user->donation_rank_expires_at);
        $this->assertEquals(Donation::RANK_SUPPORTER, $user->donation_rank);

        // Muddat ~30 kun bo'lishi kerak (1-2 kun farq bo'lishi mumkin)
        $daysDiff = $initialTime->diffInDays($user->donation_rank_expires_at);
        $this->assertEqualsWithDelta(30, $daysDiff, 2);
    }

    /**
     * Test: 1 yillik donat — 365 kun qo'shilishi kerak (30 kun EMAS)
     */
    public function test_one_year_donation_sets_correct_expiry(): void
    {
        $user = $this->createUserWithRole();
        $initialTime = now();

        $user->activateDonationRank(
            Donation::RANK_VIP,
            75000,
            'manual',
            null,
            365  // 1 yil = 365 kun
        );

        $user->refresh();
        $this->assertNotNull($user->donation_rank_expires_at);
        $this->assertEquals(Donation::RANK_VIP, $user->donation_rank);

        // Muddat ~365 kun bo'lishi kerak (1-2 kun farq bo'lishi mumkin)
        $daysDiff = $initialTime->diffInDays($user->donation_rank_expires_at);
        $this->assertEqualsWithDelta(365, $daysDiff, 2);
    }

    /**
     * Test: 3 oylik donat — 90 kun qo'shilishi kerak
     */
    public function test_three_month_donation_sets_correct_expiry(): void
    {
        $user = $this->createUserWithRole();
        $initialTime = now();

        $user->activateDonationRank(
            Donation::RANK_PREMIUM,
            35000,
            'manual',
            null,
            90  // 3 oy = 90 kun
        );

        $user->refresh();
        $this->assertNotNull($user->donation_rank_expires_at);

        $daysDiff = $initialTime->diffInDays($user->donation_rank_expires_at);
        $this->assertEqualsWithDelta(90, $daysDiff, 2);
    }

    /**
     * Test: Joriy obunasi tugamagan foydalanuvchi yana sotib olsa — muddat qo'shilishi kerak
     */
    public function test_remaining_time_is_preserved_when_extending(): void
    {
        $user = $this->createUserWithRole();

        // Birinchi marta 1 oylik sotib oldi
        $user->activateDonationRank(
            Donation::RANK_SUPPORTER,
            15000,
            'manual',
            null,
            30
        );
        $user->refresh();
        $firstExpiry = $user->donation_rank_expires_at->copy();

        // 15 kun o'tdi (hali 15 kun qoldi)
        $this->travel(15)->days();

        // Yana 1 oylik sotib oldi
        $user->activateDonationRank(
            Donation::RANK_SUPPORTER,
            15000,
            'manual',
            null,
            30
        );
        $user->refresh();

        // Yangi muddat: qolgan 15 kun + yangi 30 kun = ~45 kun
        $daysFromNow = now()->diffInDays($user->donation_rank_expires_at);
        $this->assertEqualsWithDelta(45, $daysFromNow, 2);
    }

    /**
     * Test: ActivationKey orqali to'g'ri muddat uzatilishi
     */
    public function test_activation_key_passes_correct_duration(): void
    {
        $user = $this->createUserWithRole();
        $initialTime = now();

        // 1 yillik kalit yaratish
        $key = ActivationKey::create([
            'code' => ActivationKey::generateCode(),
            'rank' => 'vip',
            'duration' => '1year',
            'duration_days' => 365,
            'generated_by' => $user->id,
            'expires_at' => now()->addDays(30),
        ]);

        $key->activate($user);
        $user->refresh();

        $this->assertEquals(Donation::RANK_VIP, $user->donation_rank);

        // Muddat ~365 kun bo'lishi kerak
        $daysDiff = $initialTime->diffInDays($user->donation_rank_expires_at);
        $this->assertEqualsWithDelta(365, $daysDiff, 2);
    }

    /**
     * Test: Default muddat 30 kun bo'lishi kerak (parameter berilmasa)
     */
    public function test_default_duration_is_30_days(): void
    {
        $user = $this->createUserWithRole();
        $initialTime = now();

        // durationDays parameterini bermaymiz — default 30 bo'lishi kerak
        $user->activateDonationRank(
            Donation::RANK_SUPPORTER,
            15000,
            'manual',
            null
            // 5-parametr berilmadi — default 30
        );

        $user->refresh();
        $daysDiff = $initialTime->diffInDays($user->donation_rank_expires_at);
        $this->assertEqualsWithDelta(30, $daysDiff, 2);
    }

    /**
     * Test: Muddati tugagan donor — effectiveTheme null qaytarishi kerak
     */
    public function test_expired_donor_effective_theme_returns_null(): void
    {
        $user = $this->createUserWithRole();

        $user->activateDonationRank(Donation::RANK_VIP, 75000, 'manual', null, 30);
        $user->refresh();
        $this->assertNotNull($user->effectiveTheme());

        // 31 kun o'tdi — muddat tugadi
        $this->travel(31)->days();

        // Yangi instance olish kerak (cache tozalanishi uchun)
        $expiredUser = User::find($user->id);
        $this->assertFalse($expiredUser->isDonor());
        $this->assertNull($expiredUser->effectiveTheme());
    }

    /**
     * Test: Muddati tugagan donor — donorBadgeHtml bo'sh qaytarishi kerak
     */
    public function test_expired_donor_badge_html_returns_empty(): void
    {
        $user = $this->createUserWithRole();

        $user->activateDonationRank(Donation::RANK_VIP, 75000, 'manual', null, 30);
        $user->refresh();
        $this->assertNotEmpty($user->donorBadgeHtml());

        // 31 kun o'tdi — muddat tugadi
        $this->travel(31)->days();

        $expiredUser = User::find($user->id);
        $this->assertEmpty($expiredUser->donorBadgeHtml());
    }

    /**
     * Test: Muddati tugagan donor — donorCommentColor null qaytarishi kerak
     */
    public function test_expired_donor_comment_color_returns_null(): void
    {
        $user = $this->createUserWithRole();

        $user->activateDonationRank(Donation::RANK_VIP, 75000, 'manual', null, 30);
        $user->refresh();
        $this->assertNotNull($user->donorCommentColor());

        // 31 kun o'tdi — muddat tugadi
        $this->travel(31)->days();

        $expiredUser = User::find($user->id);
        $this->assertNull($expiredUser->donorCommentColor());
    }

    /**
     * Test: Muddati tugagan donor — donorUsernameColor null qaytarishi kerak
     */
    public function test_expired_donor_username_color_returns_null(): void
    {
        $user = $this->createUserWithRole();

        $user->activateDonationRank(Donation::RANK_VIP, 75000, 'manual', null, 30);
        $user->refresh();
        $this->assertNotNull($user->donorUsernameColor());

        // 31 kun o'tdi — muddat tugadi
        $this->travel(31)->days();

        $expiredUser = User::find($user->id);
        $this->assertNull($expiredUser->donorUsernameColor());
    }

    /**
     * Test: Muddati tugagan donor — donorThemeClass bo'sh qaytarishi kerak
     */
    public function test_expired_donor_theme_class_returns_empty(): void
    {
        $user = $this->createUserWithRole();

        $user->activateDonationRank(Donation::RANK_VIP, 75000, 'manual', null, 30);
        $user->refresh();
        $this->assertNotEmpty($user->donorThemeClass());

        // 31 kun o'tdi — muddat tugadi
        $this->travel(31)->days();

        $expiredUser = User::find($user->id);
        $this->assertEmpty($expiredUser->donorThemeClass());
    }
}
