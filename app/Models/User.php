<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    public const ROLE_SUPER_ADMIN = 'super_admin';

    public const ROLE_ADMIN = 'admin';

    public const ROLE_EDITOR = 'editor';

    public const ROLE_MODERATOR = 'moderator';

    public const ROLE_USER = 'user';

    public const ROLES = [
        self::ROLE_SUPER_ADMIN => 'Super Admin',
        self::ROLE_ADMIN => 'Admin',
        self::ROLE_EDITOR => 'Editor',
        self::ROLE_MODERATOR => 'Moderator',
        self::ROLE_USER => 'Foydalanuvchi',
    ];

    public const ROLE_HIERARCHY = [
        self::ROLE_SUPER_ADMIN => 5,
        self::ROLE_ADMIN => 4,
        self::ROLE_EDITOR => 3,
        self::ROLE_MODERATOR => 2,
        self::ROLE_USER => 1,
    ];

    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'role',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_active' => 'boolean',
    ];

    public function scopeByRole(Builder $query, string $role): Builder
    {
        return $query->where('role', $role);
    }

    public function scopeAdmins(Builder $query): Builder
    {
        return $query->whereIn('role', [self::ROLE_SUPER_ADMIN, self::ROLE_ADMIN]);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function likes(): HasMany
    {
        return $this->hasMany(PostLike::class);
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === self::ROLE_SUPER_ADMIN;
    }

    public function isAdmin(): bool
    {
        return in_array($this->role, [self::ROLE_ADMIN, self::ROLE_SUPER_ADMIN]);
    }

    public function isEditor(): bool
    {
        return in_array($this->role, [self::ROLE_EDITOR, self::ROLE_ADMIN, self::ROLE_SUPER_ADMIN]);
    }

    public function isModerator(): bool
    {
        return in_array($this->role, [self::ROLE_MODERATOR, self::ROLE_EDITOR, self::ROLE_ADMIN, self::ROLE_SUPER_ADMIN]);
    }

    public function isActive(): bool
    {
        return (bool) $this->is_active;
    }

    public function canManage(User $user): bool
    {
        $currentLevel = self::ROLE_HIERARCHY[$this->role] ?? 0;
        $targetLevel = self::ROLE_HIERARCHY[$user->role] ?? 0;

        return $currentLevel > $targetLevel;
    }

    public function getRoleLabelAttribute(): string
    {
        return self::ROLES[$this->role] ?? $this->role;
    }
}
