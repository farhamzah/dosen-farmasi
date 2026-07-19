<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LecturerExternalIdentifier extends Model
{
    use SoftDeletes;

    protected $table = 'lecturer_external_identifiers';

    public const TYPES = ['SINTA', 'ORCID', 'Scopus', 'Web of Science', 'Google Scholar', 'Garuda', 'RAMA', 'ResearchGate', 'Website Pribadi'];

    protected $fillable = [
        'lecturer_core_id',
        'identifier_type',
        'identifier_value',
        'profile_url',
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
            'synced_at' => 'datetime',
        ];
    }
}
