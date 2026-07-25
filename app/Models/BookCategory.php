<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class BookCategory extends Model
{
    protected $fillable = ['name', 'name_en', 'slug', 'sort_order'];

    protected static function booted(): void
    {
        static::creating(function (self $cat) {
            if (empty($cat->slug)) {
                $cat->slug = Str::slug($cat->name) ?: 'category-' . time();
            }
        });
    }

    public function books(): HasMany
    {
        return $this->hasMany(Book::class);
    }

    public function getLocalizedNameAttribute(): string
    {
        $locale = app()->getLocale();
        if ($locale !== 'uz' && filled($this->name_en)) {
            return $this->name_en;
        }
        return $this->name;
    }
}
