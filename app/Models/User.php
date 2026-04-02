<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Schema;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    public const ROLE_SUPER_ADMIN = Role::NAME_SUPER_ADMIN;

    public const ROLE_ADMIN = Role::NAME_ADMIN;

    public const ROLE_EDITOR = Role::NAME_EDITOR;

    public const ROLE_MODERATOR = Role::NAME_MODERATOR;

    public const ROLE_TEACHER = Role::NAME_TEACHER;

    public const ROLE_USER = Role::NAME_USER;

    public const ROLES = [
        self::ROLE_SUPER_ADMIN => 'Super Admin',
        self::ROLE_ADMIN => 'Admin',
        self::ROLE_EDITOR => 'Editor',
        self::ROLE_MODERATOR => 'Moderator',
        self::ROLE_TEACHER => 'O\'qituvchi',
        self::ROLE_USER => 'Foydalanuvchi',
    ];

    public const ROLE_HIERARCHY = [
        self::ROLE_SUPER_ADMIN => 5,
        self::ROLE_ADMIN => 4,
        self::ROLE_EDITOR => 3,
        self::ROLE_MODERATOR => 2,
        self::ROLE_TEACHER => 2,
        self::ROLE_USER => 1,
    ];

    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'role_id',
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

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function roleRelation(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'roles_user')
            ->withTimestamps();
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function likes(): HasMany
    {
        return $this->hasMany(PostLike::class);
    }

    public function teacherLikes(): HasMany
    {
        return $this->hasMany(TeacherLike::class);
    }

    public function createdCourses(): HasMany
    {
        return $this->hasMany(Course::class, 'created_by');
    }

    public function roleLevel(): int
    {
        return (int) ($this->roleRelation?->level ?? self::ROLE_HIERARCHY[self::ROLE_USER]);
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === self::ROLE_SUPER_ADMIN;
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
        return $this->roleLevel() >= self::ROLE_HIERARCHY[self::ROLE_MODERATOR];
    }

    public function isTeacher(): bool
    {
        return $this->role === self::ROLE_TEACHER;
    }

    public function isActive(): bool
    {
        return (bool) $this->is_active;
    }

    public function canManage(User $user): bool
    {
        return $this->roleLevel() > $user->roleLevel();
    }

    public function getRoleAttribute(): string
    {
        return $this->roleRelation?->name ?? self::ROLE_USER;
    }

    public function getRoleLabelAttribute(): string
    {
        if ($this->roleRelation?->label) {
            return $this->roleRelation->label;
        }

        return self::ROLES[$this->role] ?? $this->role;
    }

    public function syncRolePivot(): void
    {
        if (! Schema::hasTable('roles_user')) {
            return;
        }

        if (! $this->role_id) {
            $this->roles()->detach();

            return;
        }

        $this->roles()->sync([$this->role_id]);
    }
}
