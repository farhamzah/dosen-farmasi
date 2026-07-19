<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LecturerCertification extends Model
{
    use SoftDeletes;

    protected $table = 'lecturer_certifications';

    protected $fillable = [
        'lecturer_core_id',
        'category',
        'name',
        'issuer',
        'certificate_number',
        'issued_at',
        'expires_at',
        'document_id',
        'status',
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
            'issued_at' => 'date',
            'expires_at' => 'date',
            'synced_at' => 'datetime',
        ];
    }
}
