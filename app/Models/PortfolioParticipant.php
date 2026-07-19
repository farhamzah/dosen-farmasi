<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class PortfolioParticipant extends Model
{
    protected $fillable = [
        'portfolio_activity_id',
        'participant_type',
        'core_dosen_id',
        'external_name',
        'student_identifier',
        'student_name',
        'institution_name',
        'role',
        'is_primary',
        'sort_order',
        'participant_fingerprint',
    ];

    protected function casts(): array
    {
        return [
            'is_primary' => 'boolean',
        ];
    }

    public static function fingerprint(array $data): string
    {
        $identity = [
            $data['participant_type'] ?? '',
            $data['core_dosen_id'] ?? '',
            $data['student_identifier'] ?? '',
            Str::lower(trim((string) ($data['external_name'] ?? ''))),
            Str::lower(trim((string) ($data['institution_name'] ?? ''))),
        ];

        return hash('sha256', implode('|', $identity));
    }

    public function activity()
    {
        return $this->belongsTo(PortfolioActivity::class, 'portfolio_activity_id');
    }
}
