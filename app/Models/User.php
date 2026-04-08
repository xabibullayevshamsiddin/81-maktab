<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected static ?bool $legacyRoleColumnExists = null;

    public const ROLE_SUPER_ADMIN = Role::NAME_SUPER_ADMIN;

    public const ROLE_ADMIN = Role::NAME_ADMIN;

    public const ROLE_EDITOR = Role::NAME_EDITOR;

    public const ROLE_MODERATOR = Role::NAME_MODERATOR;

    public const ROLE_TEACHER = Role::NAME_TEACHER;

    public const ROLE_USER = Role::NAME_USER;

    public const UNIVERSAL_GRADE_LABEL = 'Barcha sinflar';

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

    protected $fillable = [
        'name',
        'first_name',
        'last_name',
        'email',
        'phone',
        'grade',
        'avatar',
        'password',
        'role_id',
        'is_active',
        'is_parent',
    ];

    public static function nameValidationRules(bool $required = true): array
    {
        $base = ['string', 'max:120', 'regex:/^[\p{L}\s\'\-\.]+$/u'];

        return $required ? array_merge(['required'], $base) : array_merge(['nullable'], $base);
    }

    public static function nameValidationMessage(): string
    {
        return 'Faqat harflar, probel, apostrof va defis ishlatilishi mumkin.';
    }

    public function buildNameFromParts(): string
    {
        return trim(($this->first_name ?? '') . ' ' . ($this->last_name ?? ''));
    }

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_active' => 'boolean',
        'is_parent' => 'boolean',
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

    public function teacherProfile(): HasOne
    {
        return $this->hasOne(Teacher::class);
    }

    public function createdCourses(): HasMany
    {
        return $this->hasMany(Course::class, 'created_by');
    }

    public function courseEnrollments(): HasMany
    {
        return $this->hasMany(CourseEnrollment::class);
    }

    public function roleLevel(): int
    {
        return (int) (self::ROLE_HIERARCHY[$this->resolvedRoleName()] ?? self::ROLE_HIERARCHY[self::ROLE_USER]);
    }

    public function hasRole(array|string $roleNames): bool
    {
        $currentRoleName = $this->resolvedRoleName();

        $roleNames = array_values(array_filter(array_map(
            static fn ($roleName) => trim((string) $roleName),
            is_array($roleNames) ? $roleNames : [$roleNames]
        )));

        return in_array($currentRoleName, $roleNames, true);
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
        return $this->isSuperAdmin() || $this->hasRole(self::ROLE_ADMIN);
    }

    public function isEditor(): bool
    {
        return $this->isAdmin() || $this->hasRole(self::ROLE_EDITOR);
    }

    public function isModerator(): bool
    {
        return $this->isEditor() || $this->hasRole(self::ROLE_MODERATOR);
    }

    /**
     * Faqat moderator roli (admin/editor emas) — cheklangan admin menyusi uchun.
     */
    public function isOnlyModerator(): bool
    {
        return $this->hasRole(self::ROLE_MODERATOR) && ! $this->isAdmin() && ! $this->hasRole(self::ROLE_EDITOR);
    }

    /**
     * Faqat moderator uchun: super_admin/admin yozgan izohlarni boshqarish mumkin emas.
     */
    public function canModerateCommentAuthor(?User $author): bool
    {
        if (! $this->isOnlyModerator()) {
            return true;
        }

        if (! $author) {
            return true;
        }

        return ! $author->isAdmin();
    }

    /**
     * Post yoki ustozlar sahifasidagi izohni tahrirlash/o‘chirish (controller va view bilan bir xil).
     */
    public function canManageCommentAsStaff(?User $commentAuthor, int|string|null $commentUserId): bool
    {
        if ((int) ($commentUserId ?? 0) === (int) $this->id) {
            return true;
        }

        if ($this->isAdmin()) {
            return true;
        }

        if (! $this->hasRole(self::ROLE_MODERATOR)) {
            return false;
        }

        return $this->canModerateCommentAuthor($commentAuthor);
    }

    public function isTeacher(): bool
    {
        return $this->hasRole(self::ROLE_TEACHER);
    }

    public function canAccessDashboard(): bool
    {
        return $this->hasAnyRole([
            self::ROLE_SUPER_ADMIN,
            self::ROLE_ADMIN,
            self::ROLE_EDITOR,
            self::ROLE_MODERATOR,
        ]);
    }

    public function canManageContent(): bool
    {
        return $this->hasAnyRole([
            self::ROLE_SUPER_ADMIN,
            self::ROLE_ADMIN,
            self::ROLE_EDITOR,
        ]);
    }

    public function canManageInbox(): bool
    {
        return $this->hasAnyRole([
            self::ROLE_SUPER_ADMIN,
            self::ROLE_ADMIN,
        ]) || $this->isOnlyModerator();
    }

    public function canManageEducation(): bool
    {
        return $this->hasAnyRole([
            self::ROLE_SUPER_ADMIN,
            self::ROLE_ADMIN,
            self::ROLE_TEACHER,
        ]);
    }

    public function hasLinkedActiveTeacherProfile(): bool
    {
        if (array_key_exists('active_teacher_profile_count', $this->attributes)) {
            return (int) $this->attributes['active_teacher_profile_count'] > 0;
        }

        return $this->teacherProfile()
            ->where('is_active', true)
            ->exists();
    }

    public function hasCreatedCourse(): bool
    {
        if (array_key_exists('created_courses_count', $this->attributes)) {
            return (int) $this->attributes['created_courses_count'] > 0;
        }

        return $this->createdCourses()->exists();
    }



    public function canManageExams(): bool
    {
        return $this->isAdmin() || ($this->isTeacher() && $this->hasLinkedActiveTeacherProfile());
    }

    public function canManageTeachers(): bool
    {
        return $this->hasAnyRole([
            self::ROLE_SUPER_ADMIN,
            self::ROLE_ADMIN,
            self::ROLE_EDITOR,
        ]);
    }

    public function canManageSystem(): bool
    {
        return $this->hasAnyRole([
            self::ROLE_SUPER_ADMIN,
            self::ROLE_ADMIN,
        ]);
    }

    /**
     * Kursni ushbu foydalanuvchi yaratganmi (o‘qituvchi paneli uchun).
     */
    public function ownsCourse(Course $course): bool
    {
        return (int) $course->created_by === (int) $this->id;
    }

    public function isActive(): bool
    {
        return (bool) $this->is_active;
    }

    public function canManage(User $user): bool
    {
        return $this->roleLevel() > $user->roleLevel();
    }

    public function canAssignRole(Role $role): bool
    {
        if ($role->name === self::ROLE_SUPER_ADMIN) {
            return $this->isSuperAdmin();
        }

        return $this->roleLevel() > (int) $role->level;
    }

    public function getRoleAttribute(): string
    {
        return $this->resolvedRoleName();
    }

    public function getRoleLabelAttribute(): string
    {
        $resolvedRoleName = $this->resolvedRoleName();

        return $this->localizedRoleLabel($resolvedRoleName);
    }

    public function getAdminRoleBadgeClassAttribute(): string
    {
        return match ($this->role) {
            self::ROLE_SUPER_ADMIN => 'bg-dark',
            self::ROLE_ADMIN => 'bg-danger',
            self::ROLE_EDITOR => 'bg-warning',
            self::ROLE_MODERATOR => 'bg-info',
            default => 'bg-secondary',
        };
    }

    public function isParent(): bool
    {
        return (bool) $this->is_parent;
    }

    public function canTakeExams(): bool
    {
        return ! $this->is_parent;
    }

    public function hasUniversalGrade(): bool
    {
        if ($this->is_parent) {
            return true;
        }

        return $this->role !== self::ROLE_USER;
    }

    public function displayGrade(string $emptyLabel = 'Kiritilmagan'): string
    {
        if ($this->is_parent) {
            return 'Ota-ona';
        }

        if ($this->hasUniversalGrade()) {
            return self::UNIVERSAL_GRADE_LABEL;
        }

        $grade = trim((string) ($this->grade ?? ''));

        return $grade !== '' ? $grade : $emptyLabel;
    }

    public function getGradeLabelAttribute(): string
    {
        return $this->displayGrade();
    }

    public function avatarUrl(): ?string
    {
        if (empty($this->avatar)) {
            return null;
        }

        return app_storage_asset($this->avatar);
    }

    public function getAvatarUrlAttribute(): ?string
    {
        return $this->avatarUrl();
    }

    public function getAvatarInitialAttribute(): string
    {
        $name = trim((string) ($this->name ?? 'U'));

        return Str::upper(Str::substr($name !== '' ? $name : 'U', 0, 1));
    }

    public function localizedRoleLabel(?string $roleName = null, ?string $locale = null): string
    {
        $resolvedRoleName = $roleName ?: $this->resolvedRoleName();
        $locale = trim((string) ($locale ?: app()->getLocale() ?: 'uz'));

        if (isset(self::ROLE_LABELS[$locale][$resolvedRoleName])) {
            return self::ROLE_LABELS[$locale][$resolvedRoleName];
        }

        if (isset(self::ROLE_LABELS['uz'][$resolvedRoleName])) {
            return self::ROLE_LABELS['uz'][$resolvedRoleName];
        }

        return self::ROLES[$resolvedRoleName] ?? $resolvedRoleName;
    }

    protected static function hasLegacyRoleColumn(): bool
    {
        if (self::$legacyRoleColumnExists !== null) {
            return self::$legacyRoleColumnExists;
        }

        self::$legacyRoleColumnExists = Schema::hasTable('users') && Schema::hasColumn('users', 'role');

        return self::$legacyRoleColumnExists;
    }

    protected function legacyRoleName(): string
    {
        if (! self::hasLegacyRoleColumn()) {
            return '';
        }

        $legacyRoleName = trim((string) ($this->getRawOriginal('role') ?? $this->attributes['role'] ?? ''));

        return array_key_exists($legacyRoleName, self::ROLE_HIERARCHY) ? $legacyRoleName : '';
    }

    protected function relationRoleName(): string
    {
        $this->loadMissing('roleRelation');

        $relationRoleName = trim((string) ($this->roleRelation?->name ?? ''));

        return array_key_exists($relationRoleName, self::ROLE_HIERARCHY) ? $relationRoleName : '';
    }

    protected function resolvedRoleName(): string
    {
        $relationRoleName = $this->relationRoleName();
        $legacyRoleName = $this->legacyRoleName();

        if ($relationRoleName === '') {
            return $legacyRoleName !== '' ? $legacyRoleName : self::ROLE_USER;
        }

        if ($legacyRoleName === '') {
            return $relationRoleName;
        }

        return self::ROLE_HIERARCHY[$legacyRoleName] > self::ROLE_HIERARCHY[$relationRoleName]
            ? $legacyRoleName
            : $relationRoleName;
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
