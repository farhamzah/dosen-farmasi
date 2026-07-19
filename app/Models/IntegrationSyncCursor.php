<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IntegrationSyncCursor extends Model
{
    protected $fillable = [
        'source_app',
        'cursor_key',
        'cursor_value',
        'last_source_id',
        'last_updated_at',
        'status',
        'last_successful_sync_at',
        'last_attempted_sync_at',
        'error_summary',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'last_successful_sync_at' => 'datetime',
            'last_attempted_sync_at' => 'datetime',
            'last_updated_at' => 'datetime',
            'metadata' => 'array',
        ];
    }
}
