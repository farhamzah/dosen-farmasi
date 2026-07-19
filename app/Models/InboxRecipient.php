<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InboxRecipient extends Model
{
    protected $fillable = [
        'inbox_item_id',
        'lecturer_core_id',
        'app_user_id',
        'status',
        'read_at',
        'responded_at',
        'archived_at',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'read_at' => 'datetime',
            'responded_at' => 'datetime',
            'archived_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function item()
    {
        return $this->belongsTo(InboxItem::class, 'inbox_item_id');
    }
}
