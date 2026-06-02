<?php

namespace App\Models;

use App\Models\Concerns\HasNameValidation;
use App\Models\Concerns\HasPermissions;
use App\Models\Concerns\HasRoles;
use App\Models\Concerns\HasUserRelationships;
use App\Models\Concerns\ManagesCourses;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Schema;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use Notifiable;
    
    // Custom traits for better organization
    use HasRoles;
    use HasPermissions;
    use HasNameValidation;
    use ManagesCourses;
    use HasUserRelationships;

    protected static ?bool $legacyRoleColumnExists = null;

    public const UNIVERSAL_GRADE_LABEL = 'Barcha sinflar';

    protected $fillable = [
        'name',
        'first_name',
        'last_name',
        'email',
        'phone',
        'grade',
        'avatar',
        'google_id',
        'password',
        'role_id',
        'is_active',
        'is_parent',
        'course_open_approved',
        'course_open_request_pending',
        'course_open_requested_at',
        'course_open_approved_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_active' => 'boolean',
        'is_parent' => 'boolean',
        'course_open_approved' => 'boolean',
        'course_open_request_pending' => 'boolean',
        'course_open_requested_at' => 'datetime',
        'course_open_approved_at' => 'datetime',
    ];

    /**
     * Boot the model.
     */
    protected static function booted(): void
    {
        static::creating(function (User $user): void {
            if ($user->role_id) {
                return;
            }

            $defaultRoleId = Role::defaultUserRoleId();
            if ($defaultRoleId) {
                $user->role_id = $defaultRoleId;
            }
        });

        static::created(function (User $user): void {
            $user->syncRolePivot();
        });

        static::updated(function (User $user): void {
            if ($user->wasChanged('role_id')) {
                $user->syncRolePivot();
            }
        });
    }

    /**
     * Scopes
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Active Status
     */
    public function isActive(): bool
    {
        return (bool) ($this->is_active ?? true);
    }

    /**
     * Display Name
     */
    public function getDisplayNameAttribute(): string
    {
        if (! empty($this->name)) {
            return $this->name;
        }

        if (! empty($this->first_name) || ! empty($this->last_name)) {
            return $this->buildNameFromParts();
        }

        return $this->email ?? 'User';
    }

    /**
     * Role Name Resolution
     */
    private function resolvedRoleName(): string
    {
        // Try modern role_id first
        if ($this->roleRelation) {
            return $this->roleRelation->name;
        }

        // Fallback to legacy role column if it exists
        if ($this->legacyRoleColumnExists() && ! empty($this->getAttribute('role'))) {
            return $this->getAttribute('role');
        }

        return self::ROLE_USER;
    }

    /**
     * Legacy Role Column Support
     */
    private function legacyRoleColumnExists(): bool
    {
        if (self::$legacyRoleColumnExists === null) {
            self::$legacyRoleColumnExists = Schema::hasColumn('users', 'role');
        }

        return self::$legacyRoleColumnExists;
    }

    /**
     * Computed Attributes
     */
    public function getIsAdminAttribute(): bool
    {
        return $this->isAdmin();
    }

    public function getIsTeacherAttribute(): bool
    {
        return $this->isTeacher();
    }

    public function getRoleLabelAttribute(): string
    {
        $locale = app()->getLocale();
        $roleName = $this->resolvedRoleName();

        return self::ROLE_LABELS[$locale][$roleName] ?? self::ROLES[$roleName] ?? $roleName;
    }
}
