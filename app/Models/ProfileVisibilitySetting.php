<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProfileVisibilitySetting extends Model
{
    protected $fillable = [
        'lecturer_core_id',
        'section_visibility',
        'field_visibility',
        'public_profile_enabled',
    ];

    protected function casts(): array
    {
        return [
            'section_visibility' => 'array',
            'field_visibility' => 'array',
            'public_profile_enabled' => 'boolean',
        ];
    }

    public function sectionVisibility(string $section): string
    {
        return $this->section_visibility[$section] ?? 'INTERNAL';
    }
}
