<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LecturerSnapshot extends Model
{
    protected $fillable = [
        'core_user_id',
        'core_lecturer_id',
        'lecturer_number',
        'name',
        'email',
        'photo_url',
        'nip',
        'nidn',
        'sister_id_sdm',
        'study_program_id',
        'study_program_name',
        'is_active',
        'synced_at',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'synced_at' => 'datetime',
        ];
    }
}
