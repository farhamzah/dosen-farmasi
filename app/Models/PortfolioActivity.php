<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PortfolioActivity extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'lecturer_core_id',
        'category_id',
        'activity_type',
        'title',
        'description',
        'personal_notes',
        'lecturer_role',
        'academic_year',
        'semester',
        'start_date',
        'end_date',
        'institution_name',
        'location',
        'verification_status',
        'revision_reason',
        'rejection_reason',
        'verified_at',
        'verified_by_app_user_id',
        'archived_at',
        'source_type',
        'source_app',
        'source_entity',
        'source_record_id',
        'source_url',
        'visibility',
        'created_by_core_user_id',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'verified_at' => 'datetime',
            'archived_at' => 'datetime',
        ];
    }

    public function category()
    {
        return $this->belongsTo(PortfolioCategory::class, 'category_id');
    }

    public function documents()
    {
        return $this->belongsToMany(Document::class, 'activity_documents')->withTimestamps();
    }

    public function histories()
    {
        return $this->hasMany(PortfolioVerificationHistory::class)->latest('created_at');
    }

    public function issueReports()
    {
        return $this->hasMany(PortfolioIssueReport::class);
    }

    public function participants()
    {
        return $this->hasMany(PortfolioParticipant::class)->orderBy('sort_order')->orderBy('id');
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'portfolio_activity_tag')->withTimestamps();
    }

    public function isSystemVerified(): bool
    {
        return $this->verification_status === 'SYSTEM_VERIFIED';
    }

    public function isEditableByDosen(): bool
    {
        return $this->source_type === 'MANUAL'
            && in_array($this->verification_status, ['DRAFT', 'REVISION_REQUIRED'], true);
    }
}
