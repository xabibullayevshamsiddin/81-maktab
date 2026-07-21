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
        return $this->donation_rank !== null && !$this->isDonationExpired();
    }

    public function isDonationExpired(): bool
    {
        if ($this->donation_rank === null) {
            return true;
        }
        return $this->donation_rank_expires_at !== null && $this->donation_rank_expires_at->isPast();
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
        // 1. Foydalanuvchi tanlagan tema
        $theme = $this->profile_theme ?: $this->donation_rank;
        if ($theme && Donation::themeAllowedForUser($theme, $this)) {
            return $theme;
        }

        // 2. Donor ranki (eski profile_theme noto'g'ri bo'lsa)
        $rank = $this->donation_rank;
        if ($rank && Donation::themeAllowedForUser($rank, $this)) {
            return $rank;
        }

        // 3. Oddiy foydalanuvchi yoki hech qanday huquq yo'q
        return null;
    }

    public function donorBadgeHtml(bool $locked = false): string
    {
        // Joriy haqiqiy tema (profile_theme yoki donor ranki, ruxsat tekshiruvi bilan).
        $theme = $this->effectiveTheme() ?? $this->donation_rank;
        $config = Donation::themeConfig($theme) ?? Donation::configForRank($this->donation_rank);
        if (!$config) {
            return "";
        }

        $label = $config["label"];
        $icon = $config["badge_icon"];

        // Badge stili (profile appearance sozlamasi): default | pill | icon
        $badgeStyle = $this->badge_style ?? "default";
        $styleClass = "donor-badge--" . $badgeStyle;

        // Joriy tema foydalanuvchiga ruxsat etilganmi?
        $themeAllowed = $theme ? Donation::themeAllowedForUser($theme, $this) : false;

        // Qulf holati (majburan qulflangan yoki hech qanday huquq yo'q)
        if ($locked || !$themeAllowed) {
            $title = e("Sotib olish uchun Donat boling!");
            return "<span class=\"donor-badge donor-badge--locked {$styleClass}\" title=\"{$title}\">"
                . "<i class=\"fa-solid fa-lock\"></i>"
                . ($badgeStyle !== "icon" ? " {$label}</span>" : "</span>");
        }

        // Qolgan kun (show_expiry_badge sozlamasi 0 bo'lsa, ko'rsatilmaydi).
        // Faqat donor temalari uchun kun ko'rsatiladi (admin temalari muddatsiz).
        $themeType = $config["type"] ?? "donor";
        $showExpiry = ($this->show_expiry_badge ?? "1") === "1";
        $daysLeft = 0;
        if ($showExpiry && $themeType === "donor" && $this->donation_rank_expires_at) {
            $diff = (int) $this->donation_rank_expires_at->diffInDays(now(), false);
            $daysLeft = $diff > 0 ? $diff : 0;
        }
        $expiryTitle = $daysLeft > 0
            ? " title=\"" . e($daysLeft . " kun qoldi") . "\""
            : "";

        $expirySuffix = $daysLeft > 0 && $badgeStyle !== "icon"
            ? " <span class=\"donor-badge-days\">{$daysLeft}k</span>"
            : "";

        // Badge klassi tema kalitidan (admin-gold, premium, plain, va h.k.)
        $badgeKey = $theme ?? $this->donation_rank;

        return "<span class=\"donor-badge donor-badge--{$badgeKey} {$styleClass}\"{$expiryTitle}>"
            . "<i class=\"{$icon}\"></i>"
            . ($badgeStyle !== "icon" ? " {$label}" : "")
            . $expirySuffix
            . "</span>";
    }

    public function donorCommentColor(): ?string
    {
        // Joriy haqiqiy tema rangi.
        $theme = $this->effectiveTheme();
        if (!$theme) {
            return null;
        }
        $cfg = Donation::themeConfig($theme);
        if ($cfg) {
            return $cfg["badge_color"] ?? null;
        }
        // Donor temalari uchun comment_color (yumshoqroq rang)
        return Donation::configForRank($this->donation_rank)["comment_color"] ?? null;
    }

    public function donorMaxAvatarSize(): int
    {
        if (!$this->isDonor()) {
            return 4096; // 4 MB — oddiy foydalanuvchilar uchun
        }
        return Donation::configForRank($this->donation_rank)["max_avatar_size_kb"] ?? 4096;
    }

    public function donorAiChatLimit(): int
    {
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
        // Ism rangi joriy haqiqiy tema'dan — Gold tema = Gold (sariq) ism rangi.
        $theme = $this->effectiveTheme();
        if (!$theme) {
            return null;
        }
        $cfg = Donation::themeConfig($theme) ?? Donation::configForRank($theme);
        return $cfg["badge_color"] ?? null;
    }

    public function donorThemeClass(): string
    {
        // Joriy haqiqiy tema klassi (profile-theme-{key}).
        $theme = $this->effectiveTheme();
        if (!$theme) {
            return "";
        }
        return "profile-theme-" . $theme;
    }

    public function donorCanExport(): bool
    {
        return $this->isDonor() && in_array($this->donation_rank, [Donation::RANK_VIP], true);
    }

    public function donorCanEmoji(): bool
    {
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

        $this->update([
            "donation_rank" => $rank,
            "donation_rank_expires_at" => $expiresAt,
            "total_donated" => ($this->total_donated ?? 0) + $amount,
            "username_color" => $config["badge_color"],
            "profile_theme" => $rank,
        ]);

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
