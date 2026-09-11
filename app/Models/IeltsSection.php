<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IeltsSection extends Model
{
    use HasFactory;

    protected $fillable = ['ielts_test_id', 'skill', 'order', 'time_limit_minutes'];

    public function test()
    {
        return $this->belongsTo(IeltsTest::class, 'ielts_test_id');
    }

    public function passages()
    {
        return $this->hasMany(IeltsPassage::class);
    }
}
