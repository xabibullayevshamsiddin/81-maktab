<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SiteAnnouncement extends Model
{
    use HasFactory;

    protected $fillable = [
        'admin_id',
        'message',
        'link_url',
        'link_label',
        'style',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    /**
     * Deactivate all active announcements
     */
    public static function deactivateAll(): void
    {
        static::where('is_active', true)->update(['is_active' => false]);
    }

    /**
     * Get the active announcement
     */
    public static function getActive(): ?self
    {
        return static::where('is_active', true)->latest()->first();
    }
}
