<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LecturerFunctionalPosition extends Model
{
    use SoftDeletes;

    protected $table = 'lecturer_functional_positions';

    protected $fillable = [
        'lecturer_core_id',
        'position_name',
        'effective_date',
        'credit_score',
        'decree_number',
        'decree_date',
        'signing_official',
        'unit',
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
            'effective_date' => 'date',
            'decree_date' => 'date',
            'credit_score' => 'decimal:2',
            'is_active' => 'boolean',
            'synced_at' => 'datetime',
        ];
    }
}
