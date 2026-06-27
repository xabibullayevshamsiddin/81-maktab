<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AcademicYearPromotion extends Model
{
    use HasFactory;

    protected $fillable = [
        'from_year',
        'to_year',
        'promoted_count',
        'graduated_count',
        'selection_required_count',
        'skipped_count',
        'executed_by',
        'executed_at',
    ];

    protected $casts = [
        'from_year' => 'integer',
        'to_year' => 'integer',
        'promoted_count' => 'integer',
        'graduated_count' => 'integer',
        'selection_required_count' => 'integer',
        'skipped_count' => 'integer',
        'executed_at' => 'datetime',
    ];
}
