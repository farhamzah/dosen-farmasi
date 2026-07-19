<?php

namespace App\Services\Integration\PullAdapters;

class KpFarmasiPullAdapter extends AbstractOutboxPullAdapter
{
    public function sourceApp(): string
    {
        return 'kp-farmasi';
    }

    protected function connectionName(): string
    {
        return (string) config('dosen_farmasi.integration.source_connections.kp-farmasi', 'kp_mysql');
    }
}
