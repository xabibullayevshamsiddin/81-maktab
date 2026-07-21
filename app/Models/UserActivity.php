<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserActivity extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'description',
        'old_value',
        'new_value',
        'ip_address',
        'user_agent',
        'device_type',
        'occurred_at',
    ];

    protected $casts = [
        'old_value' => 'array',
        'new_value' => 'array',
        'occurred_at' => 'datetime',
    ];

    public const TYPE_LOGIN = 'login';
    public const TYPE_EMAIL_CHANGED = 'email_changed';
    public const TYPE_PASSWORD_CHANGED = 'password_changed';
    public const TYPE_AVATAR_CHANGED = 'avatar_changed';
    public const TYPE_GRADE_CHANGED = 'grade_changed';
    public const TYPE_DONATION_PURCHASED = 'donation_purchased';
    public const TYPE_PROFILE_UPDATED = 'profile_updated';
    public const TYPE_ROLE_CHANGED = 'role_changed';
    public const TYPE_COURSE_ENROLLED = 'course_enrolled';
    public const TYPE_COURSE_COMPLETED = 'course_completed';
    public const TYPE_EXAM_TAKEN = 'exam_taken';
    public const TYPE_TEACHER_ADDED = 'teacher_added';
    public const TYPE_COMMENT_POSTED = 'comment_posted';
    public const TYPE_POST_LIKED = 'post_liked';
    public const TYPE_ACTIVATION_KEY_USED = 'activation_key_used';

    public const TYPES = [
        self::TYPE_LOGIN => '🔐 Tizimga kirish',
        self::TYPE_EMAIL_CHANGED => '📧 Email ozgartirildi',
        self::TYPE_PASSWORD_CHANGED => '🔑 Parol ozgartirildi',
        self::TYPE_AVATAR_CHANGED => '🖼️ Profil rasmi',
        self::TYPE_GRADE_CHANGED => '🎓 Sinf ozgartirildi',
        self::TYPE_DONATION_PURCHASED => '💎 Donat sotib olindi',
        self::TYPE_PROFILE_UPDATED => '✏️ Profil yangilandi',
        self::TYPE_ROLE_CHANGED => '👑 Rol ozgartirildi',
        self::TYPE_COURSE_ENROLLED => '📚 Kursga yozildi',
        self::TYPE_COURSE_COMPLETED => '✅ Kurs tugatildi',
        self::TYPE_EXAM_TAKEN => '📝 Imtihon topshirildi',
        self::TYPE_TEACHER_ADDED => '👨‍🏫 Oqituvchi qoshildi',
        self::TYPE_COMMENT_POSTED => '💬 Izoh qoldirdi',
        self::TYPE_POST_LIKED => '❤️ Post yoqdi',
        self::TYPE_ACTIVATION_KEY_USED => '🎫 Aktivatsiya kaliti',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->type] ?? $this->type;
    }

    public function getIconAttribute(): string
    {
        return match ($this->type) {
            self::TYPE_LOGIN => 'fa-solid fa-right-to-bracket',
            self::TYPE_EMAIL_CHANGED => 'fa-solid fa-envelope',
            self::TYPE_PASSWORD_CHANGED => 'fa-solid fa-key',
            self::TYPE_AVATAR_CHANGED => 'fa-solid fa-image',
            self::TYPE_GRADE_CHANGED => 'fa-solid fa-user-graduate',
            self::TYPE_DONATION_PURCHASED => 'fa-solid fa-gem',
            self::TYPE_PROFILE_UPDATED => 'fa-solid fa-user-pen',
            self::TYPE_ROLE_CHANGED => 'fa-solid fa-shield-halved',
            self::TYPE_COURSE_ENROLLED => 'fa-solid fa-book-open',
            self::TYPE_COURSE_COMPLETED => 'fa-solid fa-check-circle',
            self::TYPE_EXAM_TAKEN => 'fa-solid fa-clipboard-check',
            self::TYPE_TEACHER_ADDED => 'fa-solid fa-chalkboard-teacher',
            self::TYPE_COMMENT_POSTED => 'fa-solid fa-comment',
            self::TYPE_POST_LIKED => 'fa-solid fa-heart',
            self::TYPE_ACTIVATION_KEY_USED => 'fa-solid fa-ticket',
            default => 'fa-solid fa-circle-info',
        };
    }

    public function getColorAttribute(): string
    {
        return match ($this->type) {
            self::TYPE_LOGIN => '#3b82f6',
            self::TYPE_EMAIL_CHANGED => '#8b5cf6',
            self::TYPE_PASSWORD_CHANGED => '#ef4444',
            self::TYPE_AVATAR_CHANGED => '#f59e0b',
            self::TYPE_GRADE_CHANGED => '#10b981',
            self::TYPE_DONATION_PURCHASED => '#f59e0b',
            self::TYPE_PROFILE_UPDATED => '#6366f1',
            self::TYPE_ROLE_CHANGED => '#8b5cf6',
            self::TYPE_COURSE_ENROLLED => '#3b82f6',
            self::TYPE_COURSE_COMPLETED => '#22c55e',
            self::TYPE_EXAM_TAKEN => '#f59e0b',
            self::TYPE_TEACHER_ADDED => '#10b981',
            self::TYPE_COMMENT_POSTED => '#6366f1',
            self::TYPE_POST_LIKED => '#ef4444',
            self::TYPE_ACTIVATION_KEY_USED => '#f59e0b',
            default => '#6b7280',
        };
    }
}
