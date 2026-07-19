<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IntegrationClient extends Model
{
    protected $fillable = [
        'code',
        'name',
        'app_code',
        'token_hash',
        'abilities',
        'allowed_abilities',
        'allowed_ip_ranges',
        'is_active',
        'last_used_at',
        'token_rotated_at',
        'token_revoked_at',
        'expires_at',
        'metadata',
    ];

    protected $hidden = ['token_hash'];

    protected function casts(): array
    {
        return [
            'abilities' => 'array',
            'allowed_abilities' => 'array',
            'allowed_ip_ranges' => 'array',
            'metadata' => 'array',
            'is_active' => 'boolean',
            'last_used_at' => 'datetime',
            'token_rotated_at' => 'datetime',
            'token_revoked_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    public function getCodeAttribute(?string $value): string
    {
        return $value ?: (string) $this->attributes['app_code'];
    }

    public function hasAbility(string $ability): bool
    {
        return in_array($ability, $this->allowed_abilities ?: $this->abilities ?: [], true);
    }

    public function events()
    {
        return $this->hasMany(IntegrationEvent::class);
    }
}
