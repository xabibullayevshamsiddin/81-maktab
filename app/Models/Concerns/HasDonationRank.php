<?php

namespace App\Models\Concerns;

use App\Models\Donation;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    public function donorBadgeHtml(bool $locked = false): string
    {
        $config = Donation::configForRank($this->donation_rank);
        if (!$config) {
            return "";
        }

        $rank = $this->donation_rank;
        $label = $config["label"];
        $icon = $config["badge_icon"];

        // Badge stili (profile appearance sozlamasi): default | pill | icon
        $badgeStyle = $this->badge_style ?? "default";
        $styleClass = "donor-badge--" . $badgeStyle;

        // Qulf holati (donor emas yoki majburan qulflangan)
        if ($locked || !$this->isDonor()) {
            $title = e("Sotib olish uchun Donat boling!");
            return "<span class=\"donor-badge donor-badge--locked {$styleClass}\" title=\"{$title}\">"
                . "<i class=\"fa-solid fa-lock\"></i>"
                . ($badgeStyle !== "icon" ? " {$label}</span>" : "</span>");
        }

        // Qolgan kun (show_expiry_badge sozlamasi 0 bo'lsa, ko'rsatilmaydi)
        $showExpiry = ($this->show_expiry_badge ?? "1") === "1";
        $daysLeft = 0;
        if ($showExpiry && $this->donation_rank_expires_at) {
            $diff = (int) $this->donation_rank_expires_at->diffInDays(now(), false);
            $daysLeft = $diff > 0 ? $diff : 0;
        }
        $expiryTitle = $daysLeft > 0
            ? " title=\"" . e($daysLeft . " kun qoldi") . "\""
            : "";

        $expirySuffix = $daysLeft > 0 && $badgeStyle !== "icon"
            ? " <span class=\"donor-badge-days\">{$daysLeft}k</span>"
            : "";

        return "<span class=\"donor-badge donor-badge--{$rank} {$styleClass}\"{$expiryTitle}>"
            . "<i class=\"{$icon}\"></i>"
            . ($badgeStyle !== "icon" ? " {$label}" : "")
            . $expirySuffix
            . "</span>";
    }

    public function donorCommentColor(): ?string
    {
        if (!$this->isDonor()) {
            return null;
        }
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
            return 20;
        }
        $limit = Donation::configForRank($this->donation_rank)["ai_chat_limit"] ?? 20;
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
        if (!$this->isDonor()) {
            return null;
        }
        // Doim to'yingan badge rangini ishlatamiz — ism matni och fonda ham,
        // qorong'i fonda ham yaxshi ko'rinishi uchun (kontrast muhim).
        return Donation::configForRank($this->donation_rank)["badge_color"] ?? null;
    }

    public function donorThemeClass(): string
    {
        // profile_theme saqlangan qiymatdan foydalanamiz (donor yoki admin temasi).
        // Agar bo'lmasa, donor rankiga tushamiz.
        $theme = $this->profile_theme ?: $this->donation_rank;
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

        return $donation;
    }
}