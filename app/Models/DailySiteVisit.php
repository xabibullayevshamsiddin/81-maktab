<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class DailySiteVisit extends Model
{
    use HasFactory;

    protected $table = 'daily_site_visits';

    protected $fillable = [
        'date',
        'page_views',
        'unique_visitors',
        'auth_visits',
    ];

    protected $casts = [
        'date' => 'date:Y-m-d',
        'page_views' => 'integer',
        'unique_visitors' => 'integer',
        'auth_visits' => 'integer',
    ];

    /**
     * Bugungi yoki berilgan sana uchun yozuvni olish yoki yaratish
     */
    public static function forDate(?string $date = null): self
    {
        $targetDate = $date ?: now()->toDateString();

        return static::firstOrCreate(
            ['date' => $targetDate],
            [
                'page_views' => 0,
                'unique_visitors' => 0,
                'auth_visits' => 0,
            ]
        );
    }
}