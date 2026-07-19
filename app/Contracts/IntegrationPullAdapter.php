<?php

namespace App\Contracts;

use App\Models\IntegrationSyncCursor;

interface IntegrationPullAdapter
{
    public function sourceApp(): string;

    public function pull(IntegrationSyncCursor $cursor, array $options = []): int;
}
