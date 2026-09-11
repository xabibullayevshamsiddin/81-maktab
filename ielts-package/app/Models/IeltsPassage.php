<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IeltsPassage extends Model
{
    use HasFactory;

    protected $fillable = ['ielts_section_id', 'title', 'content', 'audio_url'];

    public function section()
    {
        return $this->belongsTo(IeltsSection::class, 'ielts_section_id');
    }

    public function questions()
    {
        return $this->hasMany(IeltsQuestion::class)->orderBy('order');
    }
}
