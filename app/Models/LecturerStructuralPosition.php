<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LecturerStructuralPosition extends Model
{
    use SoftDeletes;

    protected $table = 'lecturer_structural_positions';

    protected $fillable = [
        'lecturer_core_id',
        'position_name',
        'unit',
        'start_date',
        'end_date',
        'decree_number',
        'decree_date',
        'document_id',
        'is_active',
        'source_type',
        'source_app',
        'source_record_id',
        'verification_status',
        'visibility',
        'synced_at',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'decree_date' => 'date',
            'is_active' => 'boolean',
            'synced_at' => 'datetime',
        ];
    }
}
