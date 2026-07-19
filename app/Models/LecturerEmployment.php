<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LecturerEmployment extends Model
{
    use SoftDeletes;

    protected $table = 'lecturer_employments';

    protected $fillable = [
        'lecturer_core_id',
        'institution_name',
        'position_name',
        'employment_type',
        'start_date',
        'end_date',
        'description',
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
            'synced_at' => 'datetime',
        ];
    }
}
