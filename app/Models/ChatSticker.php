<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ChatSticker extends Model
{
    protected $fillable = [
        'code',
        'image_path',
        'category',
        'is_donor_only',
        'sort_order',
    ];

    protected $casts = [
        'is_donor_only' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function messages(): HasMany
    {
        return $this->hasMany(ChatMessage::class, 'chat_sticker_id');
    }

    /**
     * Stiker rasm URL'ini qaytaradi.
     */
    public function imageUrl(): string
    {
        return app_storage_asset($this->image_path);
    }
}
