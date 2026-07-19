<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CalendarEventAttendee extends Model
{
    protected $fillable = [
        'calendar_event_id',
        'lecturer_core_id',
        'app_user_id',
        'status',
        'responded_at',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'responded_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function event()
    {
        return $this->belongsTo(CalendarEvent::class, 'calendar_event_id');
    }
}
