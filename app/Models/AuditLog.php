<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'actor_core_user_id',
        'actor_role',
        'action',
        'subject_type',
        'subject_id',
        'description',
        'safe_metadata',
        'ip_address',
        'user_agent',
        'created_at',
    ];

    protected function casts(): array
    {
        return ['safe_metadata' => 'array', 'created_at' => 'datetime'];
    }
}
