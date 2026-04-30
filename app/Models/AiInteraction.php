<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiInteraction extends Model
{
    protected $fillable = [
        'user_id',
        'contact_message_id',
        'question',
        'normalized_question',
        'response_text',
        'response_source',
        'response_type',
        'user_role',
        'suggested_route',
        'suggested_url',
        'is_unanswered',
        'clarification_requested',
        'support_converted',
        'is_helpful',
        'meta',
    ];

    protected $casts = [
        'is_unanswered' => 'boolean',
        'clarification_requested' => 'boolean',
        'support_converted' => 'boolean',
        'is_helpful' => 'boolean',
        'meta' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function contactMessage(): BelongsTo
    {
        return $this->belongsTo(ContactMessage::class);
    }
}
