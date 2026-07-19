<?php

namespace App\Services\Integration\PullAdapters;

class LabFarmasiPullAdapter extends AbstractOutboxPullAdapter
{
    public function sourceApp(): string
    {
        return 'lab-farmasi';
    }

    protected function connectionName(): string
    {
        return (string) config('dosen_farmasi.integration.source_connections.lab-farmasi', 'lab_mysql');
    }
}
