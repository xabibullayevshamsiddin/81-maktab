<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContactMessage extends Model
{
    protected $fillable = [
        'name',
        'email',
        'phone',
        'note',
        'message',
        'read_at',
        'read_by_user_id',
        'is_blocked',
        'blocked_at',
        'blocked_by_user_id',
    ];

    protected $casts = [
        'read_at' => 'datetime',
        'blocked_at' => 'datetime',
        'is_blocked' => 'boolean',
    ];



    public function readBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'read_by_user_id');
    }

    public function blockedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'blocked_by_user_id');
    }

    /**
     * Finds the registered user by email if they exist.
     */
    public function senderUser()
    {
        return $this->belongsTo(User::class, 'email', 'email');
    }

    public function isRead(): bool
    {
        return $this->read_at !== null;
    }

    public function markAsReadBy(User $user): void
    {
        if ($this->read_at !== null) {
            return;
        }

        $this->forceFill([
            'read_at' => now(),
            'read_by_user_id' => $user->id,
        ])->save();
    }
}
