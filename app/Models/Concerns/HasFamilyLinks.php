<?php

namespace App\Models\Concerns;

use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

trait HasFamilyLinks
{
    /**
     * Ulangan farzandlar (ota-ona nuqtai nazaridan)
     */
    public function linkedStudents(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'family_links', 'parent_user_id', 'student_user_id')
            ->withPivot('linked_at');
    }

    /**
     * Ulangan ota-onalar (o'quvchi nuqtai nazaridan)
     */
    public function linkedParents(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'family_links', 'student_user_id', 'parent_user_id')
            ->withPivot('linked_at');
    }

    /**
     * Ota-ona nechta farzand bog'lay olishi: bazaviy 2 + donor darajasi (supporter+1, premium+2, vip+3)
     */
    public function familyLinkLimit(): int
    {
        $base = 2;
        if (! $this->isDonor()) {
            return $base;
        }
        $priority = \App\Models\Donation::configForRank($this->donation_rank)['priority'] ?? 0;
        return $base + $priority;
    }

    /**
     * Ulangan farzandga kelgan har qanday Telegram xabarini ota-onalarga ham ko'chirish
     */
    public function notifyLinkedParents(string $text): void
    {
        $telegram = app(\App\Services\TelegramService::class);
        foreach ($this->linkedParents as $parent) {
            if ($parent->telegram_chat_id) {
                $telegram->sendMessage(
                    (int) $parent->telegram_chat_id,
                    "👨‍👩‍👧 <b>Farzandingiz ({$this->name}) akkauntida:</b>\n\n" . $text
                );
            }
        }
    }
}
