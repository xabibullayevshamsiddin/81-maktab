<?php

namespace App\Models\Concerns;

use App\Models\Donation;
use App\Services\UserActivityLogger;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Trait HasDonationRank
 *
 * @property-read string|null $donation_rank
 * @property-read string|null $donation_rank_expires_at
 * @property-read string|null $banner_image
 * @property-read string|null $profile_theme
 * @property-read string|null $badge_style
 * @property-read string|null $comment_style
 * @property-read string|null $chat_style
 * @property-read string|null $show_expiry_badge
 * @property-read string|null $name_font_weight
 * @property-read string|null $username_color
 * @property-read int $total_donated
 */
trait HasDonationRank
{
    /** @var array<string,mixed> instance-level memoize cache */
    private array $_donorCache = [];

    public function donations(): HasMany
    {
        return $this->hasMany(Donation::class);
    }

    public function completedDonations(): HasMany
    {
        return $this->hasMany(Donation::class)->where("status", Donation::STATUS_COMPLETED);
    }

    public function isDonor(): bool
    {
        if (!array_key_exists('isDonor', $this->_donorCache)) {
            $this->_donorCache['isDonor'] = $this->donation_rank !== null && !$this->isDonationExpired();
        }
        return $this->_donorCache['isDonor'];
    }

    public function isDonationExpired(): bool
    {
        if (!array_key_exists('isDonationExpired', $this->_donorCache)) {
            if ($this->donation_rank === null) {
                $this->_donorCache['isDonationExpired'] = true;
            } else {
                $this->_donorCache['isDonationExpired'] = $this->donation_rank_expires_at !== null
                    && $this->donation_rank_expires_at->isPast();
            }
        }
        return $this->_donorCache['isDonationExpired'];
    }

    public function donorRankLabel(): ?string
    {
        if (!$this->isDonor()) {
            return null;
        }
        $config = Donation::configForRank($this->donation_rank);
        return $config["label"] ?? $this->donation_rank;
    }

    /**
     * Foydalanuvchining joriy haqiqiy temasini qaytaradi.
     *
     * 1. Avval profile_theme (foydalanuvchi tanlagan) ni tekshiradi.
     * 2. Agar u ruxsat etilmasa (masalan, VIP donor eski 'premium' qiymatida qolgan) —
     *    donor rankiga qaytadi.
     * 3. Hech narsa ruxsat etilmasa — null (faqat plain ko'rinishi mumkin).
     *
     * Bu yagona manba — badge, ism rangi, comment rangi, theme klassi hammasi shu yerdan oladi.
     * Natijada "faqat o'z ranki" qoidasi saqlanadi, lekin eski/inconsist ma'lumotlar ham mos tushadi.
     */
    public function effectiveTheme(): ?string
    {
        if (!array_key_exists('effectiveTheme', $this->_donorCache)) {
            $theme = $this->profile_theme ?: $this->donation_rank;
            if ($theme && Donation::themeAllowedForUser($theme, $this)) {
                $this->_donorCache['effectiveTheme'] = $theme;
            } else {
                $rank = $this->donation_rank;
                if ($rank && Donation::themeAllowedForUser($rank, $this)) {
                    $this->_donorCache['effectiveTheme'] = $rank;
                } else {
                    $this->_donorCache['effectiveTheme'] = null;
                }
            }
        }
        return $this->_donorCache['effectiveTheme'];
    }

    public function donorBadgeHtml(bool $locked = false): string
    {
        $cacheKey = 'donorBadgeHtml_' . ($locked ? '1' : '0');
        if (array_key_exists($cacheKey, $this->_donorCache)) {
            return $this->_donorCache[$cacheKey];
        }

        $theme = $this->effectiveTheme() ?? $this->donation_rank;
        $config = Donation::themeConfig($theme) ?? Donation::configForRank($this->donation_rank);
        if (!$config) {
            return $this->_donorCache[$cacheKey] = "";
        }

        $label = $config["label"];
        $icon = $config["badge_icon"];
        $badgeStyle = $this->badge_style ?? "default";
        $styleClass = "donor-badge--" . $badgeStyle;
        $themeAllowed = $theme ? Donation::themeAllowedForUser($theme, $this) : false;

        if ($locked || !$themeAllowed) {
            $title = e("Sotib olish uchun Donat boling!");
            return $this->_donorCache[$cacheKey] = "<span class=\"donor-badge donor-badge--locked {$styleClass}\" title=\"{$title}\">"
                . "<i class=\"fa-solid fa-lock\"></i>"
                . ($badgeStyle !== "icon" ? " {$label}</span>" : "</span>");
        }

        $themeType = $config["type"] ?? "donor";
        $showExpiry = ($this->show_expiry_badge ?? "1") === "1";
        $daysLeft = 0;
        if ($showExpiry && $themeType === "donor" && $this->donation_rank_expires_at) {
            $diff = (int) $this->donation_rank_expires_at->diffInDays(now(), false);
            $daysLeft = $diff > 0 ? $diff : 0;
        }
        $expiryTitle = $daysLeft > 0 ? " title=\"" . e($daysLeft . " kun qoldi") . "\"" : "";
        $expirySuffix = $daysLeft > 0 && $badgeStyle !== "icon"
            ? " <span class=\"donor-badge-days\">{$daysLeft}k</span>" : "";
        $badgeKey = $theme ?? $this->donation_rank;

        return $this->_donorCache[$cacheKey] = "<span class=\"donor-badge donor-badge--{$badgeKey} {$styleClass}\"{$expiryTitle}>"
            . "<i class=\"{$icon}\"></i>"
            . ($badgeStyle !== "icon" ? " {$label}" : "")
            . $expirySuffix
            . "</span>";
    }

    public function donorCommentColor(): ?string
    {
        if (!array_key_exists('donorCommentColor', $this->_donorCache)) {
            $theme = $this->effectiveTheme();
            if (!$theme) {
                $this->_donorCache['donorCommentColor'] = null;
            } else {
                $cfg = Donation::themeConfig($theme);
                $this->_donorCache['donorCommentColor'] = $cfg
                    ? ($cfg["badge_color"] ?? null)
                    : (Donation::configForRank($this->donation_rank)["comment_color"] ?? null);
            }
        }
        return $this->_donorCache['donorCommentColor'];
    }

    public function donorMaxAvatarSize(): int
    {
        if ($this->isAdmin()) {
            return 51200; // 50 MB — adminlar uchun (VIP darajasida)
        }
        if (!$this->isDonor()) {
            return 4096; // 4 MB — oddiy foydalanuvchilar uchun
        }
        return Donation::configForRank($this->donation_rank)["max_avatar_size_kb"] ?? 4096;
    }

    public function donorAiChatLimit(): int
    {
        if ($this->isAdmin()) {
            return PHP_INT_MAX; // Cheksiz — adminlar uchun (VIP darajasida)
        }
        if (!$this->isDonor()) {
            return 30; // Kunlik
        }
        $limit = Donation::configForRank($this->donation_rank)["ai_chat_limit"] ?? 30;
        return $limit === -1 ? PHP_INT_MAX : $limit;
    }

    public function donorPriority(): int
    {
        if (!$this->isDonor()) {
            return 0;
        }
        return Donation::configForRank($this->donation_rank)["priority"] ?? 0;
    }

    public function donorBannerUrl(): ?string
    {
        if (!$this->isDonor()) {
            return null;
        }
        return $this->banner_image ? app_storage_asset($this->banner_image) : null;
    }

    public function donorUsernameColor(): ?string
    {
        if (!array_key_exists('donorUsernameColor', $this->_donorCache)) {
            $theme = $this->effectiveTheme();
            if (!$theme) {
                $this->_donorCache['donorUsernameColor'] = null;
            } else {
                $cfg = Donation::themeConfig($theme) ?? Donation::configForRank($theme);
                $this->_donorCache['donorUsernameColor'] = $cfg["badge_color"] ?? null;
            }
        }
        return $this->_donorCache['donorUsernameColor'];
    }

    public function donorThemeClass(): string
    {
        if (!array_key_exists('donorThemeClass', $this->_donorCache)) {
            $theme = $this->effectiveTheme();
            $this->_donorCache['donorThemeClass'] = $theme ? "profile-theme-" . $theme : "";
        }
        return $this->_donorCache['donorThemeClass'];
    }

    public function donorCanExport(): bool
    {
        if ($this->isAdmin()) {
            return true;
        }
        return $this->isDonor();
    }

    public function donorCanEmoji(): bool
    {
        if ($this->isAdmin()) {
            return true;
        }
        return $this->isDonor() && in_array($this->donation_rank, [Donation::RANK_VIP], true);
    }

    public function activateDonationRank(string $rank, int $amount = 0, string $paymentSystem = "manual", ?string $paymentId = null): Donation
    {
        $config = Donation::configForRank($rank);
        $expiresAt = now()->addDays($config["duration_days"] ?? 30);

        $donation = $this->donations()->create([
            "rank" => $rank,
            "amount" => $amount,
            "payment_system" => $paymentSystem,
            "payment_id" => $paymentId,
            "status" => Donation::STATUS_COMPLETED,
            "paid_at" => now(),
            "expires_at" => $expiresAt,
        ]);

        // Joriy rankni saqlab qolish logikasi:
        // Agar foydalanuvchining joriy ranki yangi rankdan yuqori bo'lsa, uni saqlab qolamiz
        $currentRank = $this->donation_rank;
        $currentRankConfig = $currentRank ? Donation::configForRank($currentRank) : null;
        $newRankConfig = Donation::configForRank($rank);
        
        $shouldUpgrade = true;
        if ($currentRank && $currentRankConfig && $newRankConfig) {
            // Agar joriy rank yangi rankdan yuqori priorityga ega bo'lsa, upgrade qilmaymiz
            if (($currentRankConfig["priority"] ?? 0) > ($newRankConfig["priority"] ?? 0)) {
                $shouldUpgrade = false;
                // Faqat expiration ni uzaytiramiz (agar yangi muddat uzunroq bo'lsa)
                if ($this->donation_rank_expires_at && $expiresAt->isAfter($this->donation_rank_expires_at)) {
                    $this->update([
                        "donation_rank_expires_at" => $expiresAt,
                        "total_donated" => ($this->total_donated ?? 0) + $amount,
                    ]);
                } else {
                    $this->update([
                        "total_donated" => ($this->total_donated ?? 0) + $amount,
                    ]);
                }
            }
        }
        
        if ($shouldUpgrade) {
            $this->update([
                "donation_rank" => $rank,
                "donation_rank_expires_at" => $expiresAt,
                "total_donated" => ($this->total_donated ?? 0) + $amount,
                "username_color" => $config["badge_color"],
                "profile_theme" => $rank,
            ]);
        }

        UserActivityLogger::log(
            $this,
            \App\Models\UserActivity::TYPE_DONATION_PURCHASED,
            'Donat sotib olindi: ' . $config["label"] . ' (' . number_format($amount) . ' so\'m)',
            ["rank" => $rank, "amount" => 0],
            ["rank" => $rank, "amount" => $amount, "expires_at" => $expiresAt->toDateTimeString()]
        );

        return $donation;
    }
}
