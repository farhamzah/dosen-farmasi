<?php

namespace App\Services\Integration\PullAdapters;

use App\Contracts\IntegrationPullAdapter;
use App\Exceptions\IntegrationProcessingException;
use App\Models\IntegrationClient;
use App\Models\IntegrationSyncCursor;
use App\Services\IntegrationEventIngestionService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

abstract class AbstractOutboxPullAdapter implements IntegrationPullAdapter
{
    abstract public function sourceApp(): string;

    abstract protected function connectionName(): string;

    public function pull(IntegrationSyncCursor $cursor, array $options = []): int
    {
        $cursor->update(['status' => 'RUNNING', 'last_attempted_sync_at' => now(), 'error_summary' => null]);

        try {
            $rows = $this->candidateRows($cursor, $options);
            if ($options['dry_run'] ?? false) {
                $cursor->update(['status' => 'DRY_RUN', 'metadata' => ['candidate_count' => $rows->count(), 'dry_run_at' => now()->toIso8601String()]]);

                return $rows->count();
            }

            $processed = 0;
            $client = $this->client();
            foreach ($rows as $row) {
                app(IntegrationEventIngestionService::class)->ingest($this->envelope((array) $row), $client);
                $cursor->forceFill([
                    'last_source_id' => (string) $row->id,
                    'last_updated_at' => $row->updated_at ? Carbon::parse($row->updated_at) : now(),
                    'cursor_value' => (string) $row->id,
                ])->save();
                $processed++;
            }

            $cursor->update(['status' => 'IDLE', 'last_successful_sync_at' => now()]);

            return $processed;
        } catch (Throwable $exception) {
            $cursor->update([
                'status' => 'FAILED',
                'error_summary' => substr($exception->getMessage(), 0, 1000),
            ]);

            throw new IntegrationProcessingException('Source pull gagal untuk '.$this->sourceApp().'.', 'DATABASE_SOURCE_UNAVAILABLE', true);
        }
    }

    protected function candidateRows(IntegrationSyncCursor $cursor, array $options)
    {
        $connection = DB::connection($this->connectionName());
        $table = config('dosen_farmasi.integration.outbox_table', 'dosen_integration_outbox');

        if (! $connection->getSchemaBuilder()->hasTable($table)) {
            return collect();
        }

        $lastUpdatedAt = $options['from'] ?? $cursor->last_updated_at?->toIso8601String();
        $query = $connection->table($table)
            ->where('source_app', $this->sourceApp())
            ->orderBy('updated_at')
            ->orderBy('id');

        if ($lastUpdatedAt) {
            $query->where('updated_at', '>=', $lastUpdatedAt);
        }

        if ($options['to'] ?? null) {
            $query->where('updated_at', '<=', $options['to']);
        }

        if ($cursor->last_source_id && ! ($options['from'] ?? null)) {
            $query->where('id', '>', $cursor->last_source_id);
        }

        return $query->limit((int) ($options['limit'] ?? config('dosen_farmasi.integration.pull_batch_size', 50)))->get();
    }

    protected function envelope(array $row): array
    {
        $payload = $row['payload'] ?? [];
        if (is_string($payload)) {
            $payload = json_decode($payload, true) ?: [];
        }

        return [
            'event_id' => (string) ($row['event_id'] ?? Str::uuid()),
            'event_type' => (string) $row['event_type'],
            'event_version' => (int) ($row['event_version'] ?? 1),
            'source_app' => $this->sourceApp(),
            'source_record_id' => (string) $row['source_record_id'],
            'source_revision' => (int) ($row['source_revision'] ?? 1),
            'correlation_id' => $row['correlation_id'] ?? null,
            'occurred_at' => $row['occurred_at'] ?? $row['updated_at'] ?? now()->toIso8601String(),
            'payload' => $payload,
        ];
    }

    protected function client(): IntegrationClient
    {
        return IntegrationClient::query()->firstOrCreate(
            ['app_code' => $this->sourceApp()],
            [
                'code' => $this->sourceApp(),
                'name' => $this->sourceApp().' pull adapter',
                'allowed_abilities' => ['events:push'],
                'abilities' => ['events:push'],
                'is_active' => true,
            ],
        );
    }
}
