<?php

namespace App\Models\Concerns;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Trait HasRoles
 * 
 * Handles all role-related functionality for User model.
 * Includes role checks, hierarchy, and role relationships.
 */
trait HasRoles
{
    /**
     * Role constants
     */
    public const ROLE_SUPER_ADMIN = Role::NAME_SUPER_ADMIN;
    public const ROLE_ADMIN = Role::NAME_ADMIN;
    public const ROLE_EDITOR = Role::NAME_EDITOR;
    public const ROLE_MODERATOR = Role::NAME_MODERATOR;
    public const ROLE_TEACHER = Role::NAME_TEACHER;
    public const ROLE_USER = Role::NAME_USER;

    /**
     * Role labels
     */
    public const ROLES = [
        self::ROLE_SUPER_ADMIN => 'Super Admin',
        self::ROLE_ADMIN => 'Admin',
        self::ROLE_EDITOR => 'Editor',
        self::ROLE_MODERATOR => 'Moderator',
        self::ROLE_TEACHER => 'O\'qituvchi',
        self::ROLE_USER => 'Foydalanuvchi',
    ];

    public const ROLE_LABELS = [
        'uz' => [
            self::ROLE_SUPER_ADMIN => 'Super Admin',
            self::ROLE_ADMIN => 'Admin',
            self::ROLE_EDITOR => 'Editor',
            self::ROLE_MODERATOR => 'Moderator',
            self::ROLE_TEACHER => 'O\'qituvchi',
            self::ROLE_USER => 'Foydalanuvchi',
        ],
        'en' => [
            self::ROLE_SUPER_ADMIN => 'Super Admin',
            self::ROLE_ADMIN => 'Admin',
            self::ROLE_EDITOR => 'Editor',
            self::ROLE_MODERATOR => 'Moderator',
            self::ROLE_TEACHER => 'Teacher',
            self::ROLE_USER => 'User',
        ],
    ];

    public const ROLE_HIERARCHY = [
        self::ROLE_SUPER_ADMIN => 5,
        self::ROLE_ADMIN => 4,
        self::ROLE_EDITOR => 3,
        self::ROLE_MODERATOR => 2,
        self::ROLE_TEACHER => 2,
        self::ROLE_USER => 1,
    ];

    /**
     * Relationships
     */
    public function roleRelation(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'roles_user')
            ->withTimestamps();
    }

    /**
     * Scopes
     */
    public function scopeByRole(Builder $query, string $role): Builder
    {
        return $query->whereHas('roleRelation', function (Builder $builder) use ($role) {
            $builder->where('name', $role);
        });
    }

    public function scopeAdmins(Builder $query): Builder
    {
        return $query->whereHas('roleRelation', function (Builder $builder) {
            $builder->where('level', '>=', self::ROLE_HIERARCHY[self::ROLE_ADMIN]);
        });
    }

    /**
     * Role checks
     */
    public function roleLevel(): int
    {
        return $this->roleRelation?->level ?? 0;
    }

    public function hasRole(array|string $roleNames): bool
    {
        if (is_string($roleNames)) {
            $roleNames = [$roleNames];
        }

        $userRoleName = $this->roleRelation?->name;

        return in_array($userRoleName, $roleNames, true);
    }

    public function hasAnyRole(array|string $roleNames): bool
    {
        return $this->hasRole($roleNames);
    }

    public function isSuperAdmin(): bool
    {
        return $this->hasRole(self::ROLE_SUPER_ADMIN);
    }

    public function isAdmin(): bool
    {
        return $this->roleLevel() >= self::ROLE_HIERARCHY[self::ROLE_ADMIN];
    }

    public function isEditor(): bool
    {
        return $this->roleLevel() >= self::ROLE_HIERARCHY[self::ROLE_EDITOR];
    }

    public function isModerator(): bool
    {
        return $this->hasAnyRole([self::ROLE_MODERATOR, self::ROLE_ADMIN, self::ROLE_SUPER_ADMIN]);
    }

    public function isOnlyModerator(): bool
    {
        return $this->hasRole(self::ROLE_MODERATOR)
            && ! $this->isAdmin();
    }

    public function isTeacher(): bool
    {
        return $this->hasRole(self::ROLE_TEACHER);
    }

    /**
     * Sync role pivot table (legacy compatibility)
     */
    public function syncRolePivot(): void
    {
        if (! $this->role_id) {
            $this->roles()->detach();
            return;
        }

        $this->roles()->sync([$this->role_id]);
    }
}
