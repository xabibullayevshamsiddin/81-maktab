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
            // Admin level = 4
            $builder->where('level', '>=', 4);
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
        return $this->hasRole(Role::NAME_SUPER_ADMIN);
    }

    public function isAdmin(): bool
    {
        return $this->roleLevel() >= 4; // Admin level
    }

    public function isEditor(): bool
    {
        return $this->roleLevel() >= 3; // Editor level
    }

    public function isModerator(): bool
    {
        return $this->hasAnyRole([Role::NAME_MODERATOR, Role::NAME_ADMIN, Role::NAME_SUPER_ADMIN]);
    }

    public function isOnlyModerator(): bool
    {
        return $this->hasRole(Role::NAME_MODERATOR)
            && ! $this->isAdmin();
    }

    public function isTeacher(): bool
    {
        return $this->hasRole(Role::NAME_TEACHER);
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
