<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CalendarEvent extends Model
{
    protected $fillable = [
        'title',
        'title_en',
        'body',
        'body_en',
        'event_date',
        'time_note',
        'time_note_en',
        'sort_order',
    ];

    protected $casts = [
        'event_date' => 'date',
        'sort_order' => 'integer',
    ];
}
