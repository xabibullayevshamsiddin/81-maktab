<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    use HasFactory;

    public const NAME_SUPER_ADMIN = 'super_admin';

    public const NAME_ADMIN = 'admin';

    public const NAME_EDITOR = 'editor';

    public const NAME_MODERATOR = 'moderator';

    public const NAME_TEACHER = 'teacher';

    public const NAME_USER = 'user';

    public const DEFAULT_ROLES = [
        ['name' => self::NAME_SUPER_ADMIN, 'label' => 'Super Admin', 'level' => 5],
        ['name' => self::NAME_ADMIN, 'label' => 'Admin', 'level' => 4],
        ['name' => self::NAME_EDITOR, 'label' => 'Editor', 'level' => 3],
        ['name' => self::NAME_MODERATOR, 'label' => 'Moderator', 'level' => 2],
        ['name' => self::NAME_TEACHER, 'label' => 'O\'qituvchi', 'level' => 2],
        ['name' => self::NAME_USER, 'label' => 'Foydalanuvchi', 'level' => 1],
    ];

    protected $fillable = [
        'name',
        'label',
        'level',
        'is_system',
    ];

    protected $casts = [
        'level' => 'integer',
        'is_system' => 'boolean',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function usersByPivot(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'roles_user')
            ->withTimestamps();
    }

    public static function defaultRoles(): array
    {
        return self::DEFAULT_ROLES;
    }

    public static function idByName(string $name): ?int
    {
        return static::query()
            ->where('name', $name)
            ->value('id');
    }

    public static function defaultUserRoleId(): ?int
    {
        return static::idByName(self::NAME_USER);
    }
}
