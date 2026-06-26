<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Donation extends Model
{
    public const RANK_SUPPORTER = "supporter";
    public const RANK_PREMIUM = "premium";
    public const RANK_VIP = "vip";

    public const ALL_RANKS = [
        self::RANK_SUPPORTER,
        self::RANK_PREMIUM,
        self::RANK_VIP,
    ];

    public const STATUS_PENDING = "pending";
    public const STATUS_COMPLETED = "completed";
    public const STATUS_FAILED = "failed";
    public const STATUS_REFUNDED = "refunded";

    protected $fillable = [
        "user_id",
        "rank",
        "amount",
        "payment_system",
        "payment_id",
        "status",
        "paid_at",
        "expires_at",
        "meta",
    ];

    protected $casts = [
        "paid_at" => "datetime",
        "expires_at" => "datetime",
        "meta" => "json",
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }    public function scopeCompleted($query)
    {
        return $query->where("status", self::STATUS_COMPLETED);
    }

    /**
     * Barcha ranklarning konfiguratsiyasi
     */
    public static function RANK_CONFIG(): array
    {
        return [
            self::RANK_SUPPORTER => [
                "label" => "Supporter",
                "badge_color" => "#3b82f6",
                "badge_icon" => "fa-solid fa-star",
                "comment_color" => "#60a5fa",
                "price" => (int) \App\Models\SiteSetting::get("donation_supporter_price", "15000"),
                "max_avatar_size_kb" => 10240,
                "ai_chat_limit" => 100,
                "priority" => 1,
            ],
            self::RANK_PREMIUM => [
                "label" => "Premium",
                "badge_color" => "#8b5cf6",
                "badge_icon" => "fa-solid fa-gem",
                "comment_color" => "#a78bfa",
                "price" => (int) \App\Models\SiteSetting::get("donation_premium_price", "35000"),
                "max_avatar_size_kb" => 25600,
                "ai_chat_limit" => 300,
                "priority" => 2,
            ],
            self::RANK_VIP => [
                "label" => "VIP",
                "badge_color" => "#f59e0b",
                "badge_icon" => "fa-solid fa-crown",
                "comment_color" => "#fbbf24",
                "price" => (int) \App\Models\SiteSetting::get("donation_vip_price", "75000"),
                "max_avatar_size_kb" => 51200,
                "ai_chat_limit" => -1,
                "priority" => 3,
            ],
        ];
    }

    /**
     * Barcha muddatlar va chegirmalar
     */
    public static function DURATIONS(): array
    {
        return [
            "1month" => ["label" => "1 oy", "days" => 30, "discount" => 0],
            "3months" => ["label" => "3 oy", "days" => 90, "discount" => 0],
            "1year" => ["label" => "1 yil", "days" => 365, "discount" => 0],
        ];
    }

    /**
     * Berilgan rank va muddat uchun chegirma foizini SiteSetting dan oqish
     */
    public static function rankDiscount(string $rank, string $duration): int
    {
        if ($duration === "1month") {
            return 0;
        }

        $key = "donation_{$rank}_discount_{$duration}";
        return (int) \App\Models\SiteSetting::get($key, "0");
    }

    /**
     * Rank narxini muddatga qarab hisoblash
     */
    public static function priceForDuration(string $rank, string $duration): int
    {
        $config = self::configForRank($rank);
        $basePrice = $config["price"] ?? 0;
        $durations = self::DURATIONS();
        $cfg = $durations[$duration] ?? $durations["1month"];

        $months = $cfg["days"] / 30;
        $discountPercent = self::rankDiscount($rank, $duration);

        $total = (int) round($basePrice * $months);
        $total = (int) round($total * (100 - $discountPercent) / 100);

        return $total;
    }

    /**
     * Chegirma foizini qaytarish
     */
    public static function discountForDuration(string $rank, string $duration): int
    {
        return self::rankDiscount($rank, $duration);
    }

    public static function configForRank(?string $rank): ?array
    {
        if ($rank === null || $rank === "") {
            return null;
        }

        $config = self::RANK_CONFIG();

        return $config[$rank] ?? null;
    }

    /**
     * Yagona tema ro'yxati — Oddiy (plain) + donor temalari + super admin temalari.
     * Har tema uchun: type (plain|donor|admin), label, badge_color, badge_icon.
     * - plain: barchaga ochiq, effektsiz
     * - admin: faqat super admin
     */
    public static function THEMES(): array
    {
        $donorConfig = self::RANK_CONFIG();
        $themes = [];

        // "Oddiy" — effektsiz, barchaga ochiq
        $themes["plain"] = [
            "key" => "plain",
            "type" => "plain",
            "label" => "Oddiy",
            "badge_color" => "#64748b",
            "badge_icon" => "fa-solid fa-user",
            "requires_admin" => false,
        ];

        // Donor temalari
        foreach (self::ALL_RANKS as $rank) {
            if (!isset($donorConfig[$rank])) {
                continue;
            }
            $cfg = $donorConfig[$rank];
            $themes[$rank] = [
                "key" => $rank,
                "type" => "donor",
                "label" => $cfg["label"],
                "badge_color" => $cfg["badge_color"],
                "badge_icon" => $cfg["badge_icon"],
                "requires_admin" => false,
            ];
        }

        // Super admin temalari (maxsus)
        $themes["admin-gold"] = [
            "key" => "admin-gold",
            "type" => "admin",
            "label" => "Gold",
            "badge_color" => "#eab308",
            "badge_icon" => "fa-solid fa-medal",
            "requires_admin" => true,
        ];
        $themes["admin-royal"] = [
            "key" => "admin-royal",
            "type" => "admin",
            "label" => "Royal",
            "badge_color" => "#dc2626",
            "badge_icon" => "fa-solid fa-chess-king",
            "requires_admin" => true,
        ];
        $themes["admin-phoenix"] = [
            "key" => "admin-phoenix",
            "type" => "admin",
            "label" => "Phoenix",
            "badge_color" => "#ea580c",
            "badge_icon" => "fa-solid fa-fire",
            "requires_admin" => true,
        ];

        return $themes;
    }

    /**
     * Ruxsat etilgan barcha temalar (ruxsat mantiqiga ko'ra).
     */
    public static function themesForUser($user): array
    {
        $allowed = [];
        foreach (self::THEMES() as $key => $cfg) {
            if (self::themeAllowedForUser($key, $user)) {
                $allowed[$key] = $cfg;
            }
        }
        return $allowed;
    }

    /**
     * Tema kaliti mavjudligini tekshirish.
     */
    public static function themeExists(?string $theme): bool
    {
        if ($theme === null || $theme === "") {
            return false;
        }
        return array_key_exists($theme, self::THEMES());
    }

    /**
     * Tema ma'lumotini olish.
     */
    public static function themeConfig(?string $theme): ?array
    {
        if ($theme === null || $theme === "") {
            return null;
        }
        $themes = self::THEMES();
        return $themes[$theme] ?? null;
    }

    /**
     * Berilgan foydalanuvchi tema tanlay oladimi?
     * Ruxsat qoidasi:
     *  - Admin temalari: faqat super admin uchun.
     *  - Donor temalari: foydalanuvchi donor bo'lsa, o'z ranki va undan past.
     *    Super admin donor bo'lmasa ham donor temalaridan foydalanishi mumkin.
     */
    public static function themeAllowedForUser(string $theme, $user): bool
    {
        $cfg = self::themeConfig($theme);
        if (!$cfg || !$user) {
            return false;
        }

        // Admin temalari — faqat super admin
        if (!empty($cfg["requires_admin"])) {
            return method_exists($user, "isSuperAdmin") && $user->isSuperAdmin();
        }

        // Donor temalari — ierarxiya bo'yicha
        $rankOrder = array_flip(self::ALL_RANKS); // supporter=0, premium=1, vip=2
        $userRank = $user->donation_rank ?? null;

        // Super admin donor emas bo'lsa ham donor temalaridan foydalanadi
        if (method_exists($user, "isSuperAdmin") && $user->isSuperAdmin()) {
            return true;
        }

        // Foydalanuvchi donor emas — hech qanday donor temasi
        if ($userRank === null || !method_exists($user, "isDonor") || !$user->isDonor()) {
            return false;
        }

        $userLevel = $rankOrder[$userRank] ?? -1;
        $themeLevel = $rankOrder[$theme] ?? -1;

        // Tema foydalanuvchi ranki darajasida yoki undan past bo'lishi kerak
        return $themeLevel >= 0 && $userLevel >= $themeLevel;
    }

    public static function priceLabel(?string $rank): string
    {
        $config = self::configForRank($rank);
        if (!$config) {
            return "";
        }

        $price = $config["price"];

        if ($price >= 1000) {
            return number_format($price, 0, ".", " ") . " som/oy";
        }

        return $price . " som/oy";
    }
}