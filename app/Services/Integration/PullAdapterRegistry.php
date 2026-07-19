<?php

namespace App\Services\Integration;

use App\Contracts\IntegrationPullAdapter;
use App\Services\Integration\PullAdapters\KpFarmasiPullAdapter;
use App\Services\Integration\PullAdapters\KpPspaPullAdapter;
use App\Services\Integration\PullAdapters\LabFarmasiPullAdapter;

class PullAdapterRegistry
{
    public function adapters(): array
    {
        return [
            'kp-farmasi' => KpFarmasiPullAdapter::class,
            'kp-pspa' => KpPspaPullAdapter::class,
            'lab-farmasi' => LabFarmasiPullAdapter::class,
        ];
    }

    public function names(): array
    {
        return array_keys($this->adapters());
    }

    public function get(string $sourceApp): ?IntegrationPullAdapter
    {
        $class = $this->adapters()[$sourceApp] ?? null;

        return $class ? app($class) : null;
    }
}
