<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LecturerExpertiseArea extends Model
{
    use SoftDeletes;

    protected $table = 'lecturer_expertise_areas';

    protected $fillable = [
        'lecturer_core_id',
        'knowledge_family',
        'knowledge_tree',
        'knowledge_group',
        'knowledge_branch',
        'knowledge_leaf',
        'primary_expertise',
        'specializations',
        'research_topics',
        'practical_skills',
        'collaboration_interests',
        'is_primary',
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
            'specializations' => 'array',
            'research_topics' => 'array',
            'practical_skills' => 'array',
            'collaboration_interests' => 'array',
            'is_primary' => 'boolean',
            'synced_at' => 'datetime',
        ];
    }
}
