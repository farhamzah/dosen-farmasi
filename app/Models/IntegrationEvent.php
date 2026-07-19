<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IntegrationEvent extends Model
{
    protected $fillable = [
        'event_id',
        'integration_client_id',
        'event_type',
        'event_version',
        'source_app',
        'source_record_id',
        'source_revision',
        'correlation_id',
        'occurred_at',
        'received_at',
        'lecturer_core_id',
        'payload',
        'payload_hash',
        'status',
        'attempt_count',
        'processed_at',
        'result_summary',
        'error_code',
        'error_message',
        'last_error_code',
        'last_error_message',
        'related_records',
        'next_retry_at',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'occurred_at' => 'datetime',
            'received_at' => 'datetime',
            'processed_at' => 'datetime',
            'related_records' => 'array',
            'next_retry_at' => 'datetime',
        ];
    }

    public function client()
    {
        return $this->belongsTo(IntegrationClient::class, 'integration_client_id');
    }

    public function failures()
    {
        return $this->hasMany(IntegrationFailure::class);
    }
}
