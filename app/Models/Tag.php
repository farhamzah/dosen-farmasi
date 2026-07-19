<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Tag extends Model
{
    protected $fillable = [
        'name',
        'normalized_name',
        'color',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public static function normalize(string $name): string
    {
        return Str::of($name)->trim()->lower()->squish()->toString();
    }

    public function activities()
    {
        return $this->belongsToMany(PortfolioActivity::class, 'portfolio_activity_tag')->withTimestamps();
    }
}
