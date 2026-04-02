<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Course extends Model
{
    use HasFactory;

    public const STATUS_DRAFT = 'draft';
    public const STATUS_PENDING_VERIFICATION = 'pending_verification';
    public const STATUS_PUBLISHED = 'published';

    protected $fillable = [
        'teacher_id',
        'created_by',
        'title',
        'price',
        'duration',
        'description',
        'start_date',
        'status',
        'publish_code',
        'publish_code_expires_at',
    ];

    protected $casts = [
        'start_date' => 'date',
        'publish_code_expires_at' => 'datetime',
    ];

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}

