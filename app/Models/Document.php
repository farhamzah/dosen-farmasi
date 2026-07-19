<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Document extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'lecturer_core_id',
        'document_type',
        'title',
        'document_number',
        'document_date',
        'issuer',
        'disk',
        'path',
        'original_filename',
        'stored_filename',
        'extension',
        'mime_type',
        'size_bytes',
        'sha256_checksum',
        'uploaded_by_app_user_id',
        'uploaded_by_core_user_id',
        'source_app',
        'source_record_id',
        'verification_status',
        'visibility',
        'delete_reason',
    ];

    protected function casts(): array
    {
        return [
            'document_date' => 'date',
            'deleted_at' => 'datetime',
        ];
    }

    public function activities()
    {
        return $this->belongsToMany(PortfolioActivity::class, 'activity_documents')->withTimestamps();
    }

    public function versions()
    {
        return $this->hasMany(DocumentVersion::class)->orderByDesc('version_number');
    }

    public function uploader()
    {
        return $this->belongsTo(AppUser::class, 'uploaded_by_app_user_id');
    }

    public function isOfficial(): bool
    {
        return filled($this->source_app) || in_array($this->verification_status, ['OFFICIAL', 'SYSTEM_VERIFIED'], true);
    }
}
