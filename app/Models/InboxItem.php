<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InboxItem extends Model
{
    protected $fillable = [
        'lecturer_core_id',
        'group_id',
        'type',
        'title',
        'summary',
        'priority',
        'status',
        'read_at',
        'due_at',
        'occurred_at',
        'action_url',
        'safe_action_url',
        'document_id',
        'metadata',
        'source_app',
        'source_record_id',
    ];

    protected function casts(): array
    {
        return [
            'read_at' => 'datetime',
            'due_at' => 'datetime',
            'occurred_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function recipients()
    {
        return $this->hasMany(InboxRecipient::class);
    }

    public function document()
    {
        return $this->belongsTo(Document::class);
    }
}
