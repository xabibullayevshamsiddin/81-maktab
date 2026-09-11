<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IeltsTest extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'type', 'time_limit_minutes', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function sections()
    {
        return $this->hasMany(IeltsSection::class)->orderBy('order');
    }

    public function attempts()
    {
        return $this->hasMany(IeltsAttempt::class);
    }
}
