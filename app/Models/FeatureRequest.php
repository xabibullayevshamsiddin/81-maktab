<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FeatureRequest extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_PLANNED = 'planned';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_DONE = 'done';
    public const STATUS_REJECTED = 'rejected';

    public const ALL_STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_PLANNED,
        self::STATUS_IN_PROGRESS,
        self::STATUS_DONE,
        self::STATUS_REJECTED,
    ];

    public const VOTABLE_STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_PLANNED,
        self::STATUS_IN_PROGRESS,
    ];

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'is_active',
        'status',
        'admin_note',
        'announced_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'announced_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function votes(): HasMany
    {
        return $this->hasMany(FeatureRequestVote::class);
    }

    public function replies(): HasMany
    {
        return $this->hasMany(FeatureRequestReply::class);
    }
}
