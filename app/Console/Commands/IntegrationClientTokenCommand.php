<?php

namespace App\Console\Commands;

use App\Models\IntegrationClient;
use App\Services\IntegrationTokenService;
use Illuminate\Console\Command;

class IntegrationClientTokenCommand extends Command
{
    protected $signature = 'dosen:integration-client-token {app_code} {--inactive}';

    protected $description = 'Create or rotate an integration client token. Plaintext is shown once.';

    public function handle(IntegrationTokenService $tokens): int
    {
        $appCode = (string) $this->argument('app_code');
        $client = IntegrationClient::query()->updateOrCreate(
            ['app_code' => $appCode],
            [
                'code' => $appCode,
                'name' => $appCode,
                'allowed_abilities' => ['events:push', 'integration:health'],
                'abilities' => ['events:push', 'integration:health'],
                'is_active' => ! $this->option('inactive'),
            ],
        );

        $plain = $tokens->rotate($client);

        $this->warn('Plain token ini hanya ditampilkan sekali. Simpan ke environment source app, jangan ke repository.');
        $this->line($plain);

        return self::SUCCESS;
    }
}
