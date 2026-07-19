<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationPreference extends Model
{
    protected $fillable = [
        'app_user_id',
        'email_enabled',
        'email_for_assignments',
        'email_for_schedule_changes',
        'email_for_documents',
        'digest_preference',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'email_enabled' => 'boolean',
            'email_for_assignments' => 'boolean',
            'email_for_schedule_changes' => 'boolean',
            'email_for_documents' => 'boolean',
            'metadata' => 'array',
        ];
    }
}
