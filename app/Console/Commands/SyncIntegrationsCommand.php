<?php

namespace App\Console\Commands;

use App\Models\IntegrationSyncCursor;
use App\Services\Integration\PullAdapterRegistry;
use Illuminate\Console\Command;

class SyncIntegrationsCommand extends Command
{
    protected $signature = 'dosen:sync-integrations {source? : kp-farmasi|kp-pspa|lab-farmasi} {--dry-run} {--from=} {--to=} {--limit=50}';

    protected $description = 'Pull source integration events safely through read-only adapters.';

    public function handle(PullAdapterRegistry $registry): int
    {
        $sources = $this->argument('source') ? [$this->argument('source')] : $registry->names();
        $total = 0;

        foreach ($sources as $source) {
            $adapter = $registry->get($source);
            if (! $adapter) {
                $this->error("Adapter {$source} tidak dikenal.");

                return self::FAILURE;
            }

            $cursor = IntegrationSyncCursor::query()->firstOrCreate(
                ['source_app' => $source, 'cursor_key' => 'outbox.updated_at.id'],
                ['status' => 'IDLE'],
            );

            $count = $adapter->pull($cursor, [
                'dry_run' => (bool) $this->option('dry-run'),
                'from' => $this->option('from'),
                'to' => $this->option('to'),
                'limit' => (int) $this->option('limit'),
            ]);
            $total += $count;
            $this->line("{$source}: {$count} kandidat".($this->option('dry-run') ? ' (dry-run)' : ' diproses'));
        }

        $this->info("Total: {$total}");

        return self::SUCCESS;
    }
}
