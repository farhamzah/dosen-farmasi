<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CalendarEvent extends Model
{
    protected $fillable = [
        'lecturer_core_id',
        'group_id',
        'title',
        'description',
        'event_type',
        'status',
        'starts_at',
        'ends_at',
        'is_all_day',
        'location',
        'source_type',
        'meeting_url',
        'inbox_item_id',
        'document_id',
        'source_app',
        'source_record_id',
        'source_revision',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'is_all_day' => 'boolean',
            'metadata' => 'array',
        ];
    }

    public function overlaps(): bool
    {
        if (! $this->starts_at || $this->status === 'CANCELLED') {
            return false;
        }

        $end = $this->ends_at ?? $this->starts_at->copy()->addHour();

        return self::query()
            ->where('lecturer_core_id', $this->lecturer_core_id)
            ->where('id', '!=', $this->id)
            ->where('status', '!=', 'CANCELLED')
            ->where('starts_at', '<', $end)
            ->where(function ($query): void {
                $query->whereNull('ends_at')->orWhere('ends_at', '>', $this->starts_at);
            })
            ->exists();
    }

    public function attendees()
    {
        return $this->hasMany(CalendarEventAttendee::class);
    }

    public function inboxItem()
    {
        return $this->belongsTo(InboxItem::class);
    }
}
