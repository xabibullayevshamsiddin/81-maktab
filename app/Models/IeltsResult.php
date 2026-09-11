<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IeltsResult extends Model
{
    use HasFactory;

    protected $fillable = ['ielts_attempt_id', 'section_bands', 'overall_band', 'level_label'];

    protected $casts = [
        'section_bands' => 'array',
        'overall_band' => 'float',
    ];

    public function attempt()
    {
        return $this->belongsTo(IeltsAttempt::class, 'ielts_attempt_id');
    }
}
