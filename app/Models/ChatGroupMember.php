<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatGroupMember extends Model
{
    public const ROLE_MEMBER = 'member';
    public const ROLE_ADMIN = 'admin';

    protected $fillable = [
        'chat_group_id',
        'user_id',
        'role',
    ];

    public function group(): BelongsTo
    {
        return $this->belongsTo(ChatGroup::class, 'chat_group_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
