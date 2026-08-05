<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TelegramVerification extends Model
{
    use HasFactory;

    public const PURPOSE_LOGIN = 'login';
    public const PURPOSE_REGISTER = 'register';
    public const PURPOSE_PASSWORD_RESET = 'password_reset';

    public const STATUS_PENDING = 'pending';
    public const STATUS_VERIFIED = 'verified';
    public const STATUS_EXPIRED = 'expired';
    public const STATUS_COMPLETED = 'completed';

    protected $fillable = [
        'token',
        'purpose',
        'user_id',
        'email',
        'phone',
        'session_payload',
        'telegram_chat_id',
        'status',
        'expires_at',
        'verified_at',
    ];

    protected $casts = [
        'session_payload' => 'array',
        'user_id' => 'integer',
        'telegram_chat_id' => 'integer',
        'expires_at' => 'datetime',
        'verified_at' => 'datetime',
    ];

    public function isExpired(): bool
    {
        return $this->expires_at && now()->greaterThan($this->expires_at);
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING && ! $this->isExpired();
    }

    public function isVerified(): bool
    {
        return $this->status === self::STATUS_VERIFIED;
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING)
            ->where('expires_at', '>', now());
    }
}
