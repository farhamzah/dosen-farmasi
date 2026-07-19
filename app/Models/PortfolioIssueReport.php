<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PortfolioIssueReport extends Model
{
    protected $fillable = [
        'portfolio_activity_id',
        'reporter_app_user_id',
        'issue_type',
        'description',
        'expected_value',
        'supporting_document_id',
        'status',
        'admin_response',
        'resolved_by_app_user_id',
        'resolved_at',
    ];

    protected function casts(): array
    {
        return [
            'resolved_at' => 'datetime',
        ];
    }

    public function activity()
    {
        return $this->belongsTo(PortfolioActivity::class, 'portfolio_activity_id');
    }

    public function reporter()
    {
        return $this->belongsTo(AppUser::class, 'reporter_app_user_id');
    }

    public function resolver()
    {
        return $this->belongsTo(AppUser::class, 'resolved_by_app_user_id');
    }
}
