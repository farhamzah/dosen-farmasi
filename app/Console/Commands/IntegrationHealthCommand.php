<?php

namespace App\Console\Commands;

use App\Models\IntegrationEvent;
use App\Models\IntegrationSyncCursor;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Throwable;

class IntegrationHealthCommand extends Command
{
    protected $signature = 'dosen:integration-health';

    protected $description = 'Show safe integration health summary without secrets.';

    public function handle(): int
    {
        $this->line('Local database: '.$this->checkDb(config('database.default')));
        $this->line('Core connection: '.$this->checkDb(config('dosen_farmasi.core.connection', 'core_mysql')));
        foreach (config('dosen_farmasi.integration.source_connections', []) as $source => $connection) {
            $this->line("Source {$source}: ".$this->checkDb($connection));
        }

        try {
            Storage::disk(config('dosen_farmasi.documents.disk'))->exists('.health');
            $this->line('Shared storage: ok');
        } catch (Throwable) {
            $this->line('Shared storage: failed');
        }

        $this->line('Queue connection: '.config('queue.default'));
        $this->line('Failed events: '.IntegrationEvent::query()->where('status', 'FAILED')->count());
        $this->line('Pending events: '.IntegrationEvent::query()->whereIn('status', ['QUEUED', 'PROCESSING'])->count());
        $this->line('Last sync: '.(IntegrationSyncCursor::query()->latest('last_successful_sync_at')->value('last_successful_sync_at') ?: '-'));

        return self::SUCCESS;
    }

    private function checkDb(string $connection): string
    {
        try {
            DB::connection($connection)->getPdo();

            return 'ok';
        } catch (Throwable) {
            return 'failed';
        }
    }
}
