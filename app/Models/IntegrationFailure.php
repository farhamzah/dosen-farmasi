<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IntegrationFailure extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'integration_event_id',
        'error_code',
        'failure_category',
        'retryable',
        'error_message',
        'attempt_count',
        'safe_context',
        'resolved_at',
        'resolved_by_core_user_id',
        'resolution_note',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'safe_context' => 'array',
            'retryable' => 'boolean',
            'resolved_at' => 'datetime',
            'created_at' => 'datetime',
        ];
    }

    public function event()
    {
        return $this->belongsTo(IntegrationEvent::class, 'integration_event_id');
    }
}
