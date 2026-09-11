<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IeltsAttempt extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'ielts_test_id', 'question_order', 'started_at',
        'submitted_at', 'status', 'rule_violation_count',
    ];

    protected $casts = [
        'question_order' => 'array',
        'started_at' => 'datetime',
        'submitted_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function test()
    {
        return $this->belongsTo(IeltsTest::class, 'ielts_test_id');
    }

    public function answers()
    {
        return $this->hasMany(IeltsAnswer::class);
    }

    public function result()
    {
        return $this->hasOne(IeltsResult::class);
    }
}
