<?php

namespace App\Services\Integration\PullAdapters;

class KpPspaPullAdapter extends AbstractOutboxPullAdapter
{
    public function sourceApp(): string
    {
        return 'kp-pspa';
    }

    protected function connectionName(): string
    {
        return (string) config('dosen_farmasi.integration.source_connections.kp-pspa', 'kp_pspa_mysql');
    }
}
