<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LecturerEducation extends Model
{
    use SoftDeletes;

    protected $table = 'lecturer_educations';

    public const BASIC_LEVELS = ['SD', 'SMP', 'SMA', 'SMK'];

    public const LEVELS = [
        'SD',
        'SMP',
        'SMA',
        'SMK',
        'D1',
        'D2',
        'D3',
        'D4',
        'S1',
        'Profesi',
        'Spesialis',
        'S2',
        'S3',
        'Postdoctoral',
        'Fellowship',
        'Lainnya',
    ];

    protected $fillable = [
        'lecturer_core_id',
        'level',
        'institution_name',
        'study_program',
        'city',
        'country',
        'start_year',
        'end_year',
        'graduation_status',
        'degree',
        'certificate_number',
        'certificate_date',
        'thesis_title',
        'supervisors',
        'document_id',
        'source_type',
        'source_app',
        'source_record_id',
        'verification_status',
        'visibility',
        'sort_order',
        'synced_at',
    ];

    protected function casts(): array
    {
        return [
            'certificate_date' => 'date',
            'supervisors' => 'array',
            'synced_at' => 'datetime',
        ];
    }

    public static function defaultVisibilityForLevel(string $level): string
    {
        return in_array($level, self::BASIC_LEVELS, true) ? 'PRIVATE' : 'INTERNAL';
    }

    public function document()
    {
        return $this->belongsTo(Document::class);
    }
}
