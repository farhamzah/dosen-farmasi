<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentVersion extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'document_id',
        'version_number',
        'disk',
        'path',
        'original_filename',
        'mime_type',
        'size_bytes',
        'sha256_checksum',
        'uploaded_by_app_user_id',
        'source_app',
        'source_record_id',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    public function document()
    {
        return $this->belongsTo(Document::class);
    }
}
