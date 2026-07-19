<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PortfolioVerificationHistory extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'portfolio_activity_id',
        'from_status',
        'to_status',
        'actor_app_user_id',
        'actor_core_user_id',
        'actor_role',
        'reason',
        'notes',
        'metadata',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function activity()
    {
        return $this->belongsTo(PortfolioActivity::class, 'portfolio_activity_id');
    }

    public function actor()
    {
        return $this->belongsTo(AppUser::class, 'actor_app_user_id');
    }
}
