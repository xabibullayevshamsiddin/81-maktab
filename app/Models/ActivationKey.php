<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class ActivationKey extends Model
{
    protected $fillable = [
        "code",
        "rank",
        "duration",
        "duration_days",
        "generated_by",
        "used_by",
        "used_at",
        "expires_at",
        "is_used",
    ];

    protected $casts = [
        "used_at" => "datetime",
        "expires_at" => "datetime",
        "is_used" => "boolean",
    ];

    public const DURATIONS = [
        "1month" => ["label" => "1 oy", "days" => 30],
        "3months" => ["label" => "3 oy", "days" => 90],
        "1year" => ["label" => "1 yil", "days" => 365],
    ];

    public const RANKS = [
        "supporter" => "Supporter",
        "premium" => "Premium",
        "vip" => "VIP",
    ];

    public function generator(): BelongsTo
    {
        return $this->belongsTo(User::class, "generated_by");
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, "used_by");
    }

    /**
     * Random kalit yaratish: 8 ta belgi (harf + raqam)
     */
    public static function generateCode(): string
    {
        $chars = "ABCDEFGHJKLMNPQRSTUVWXYZ23456789";
        $code = "";

        for ($i = 0; $i < 8; $i++) {
            $code .= $chars[random_int(0, strlen($chars) - 1)];
        }

        // Takrorlanmasligi uchun tekshirish
        while (self::query()->where("code", $code)->exists()) {
            $code = "";
            for ($i = 0; $i < 8; $i++) {
                $code .= $chars[random_int(0, strlen($chars) - 1)];
            }
        }

        return $code;
    }

    /**
     * Kalitni aktivlashtirish (bir martalik)
     */
    public function activate(User $user): bool
    {
        if ($this->is_used) {
            return false;
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        $this->update([
            "is_used" => true,
            "used_by" => $user->id,
            "used_at" => now(),
        ]);

        $user->activateDonationRank(
            $this->rank,
            0,
            "activation_key",
            $this->code
        );

        return true;
    }

    public function rankLabel(): string
    {
        return self::RANKS[$this->rank] ?? $this->rank;
    }

    public function durationLabel(): string
    {
        return self::DURATIONS[$this->duration]["label"] ?? $this->duration;
    }
}