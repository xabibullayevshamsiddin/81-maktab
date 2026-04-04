<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CalendarEvent extends Model
{
    protected $fillable = [
        'title',
        'body',
        'event_date',
        'time_note',
        'sort_order',
    ];

    protected $casts = [
        'event_date' => 'date',
        'sort_order' => 'integer',
    ];
}
