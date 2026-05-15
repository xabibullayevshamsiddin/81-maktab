<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TelegramRegistrationVerification extends Model
{
    protected $fillable = [
        'token',
        'email',
        'phone',
        'payload',
        'telegram_user_id',
        'telegram_chat_id',
        'telegram_username',
        'telegram_phone',
        'started_at',
        'verified_at',
        'completed_at',
        'expires_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'started_at' => 'datetime',
        'verified_at' => 'datetime',
        'completed_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function isExpired(): bool
    {
        return $this->expires_at === null || $this->expires_at->isPast();
    }

    public function isVerified(): bool
    {
        return $this->verified_at !== null && ! $this->isExpired();
    }
}
