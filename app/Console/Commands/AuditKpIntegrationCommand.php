<?php

namespace App\Console\Commands;

use App\Models\CalendarEvent;
use App\Models\InboxItem;
use App\Models\IntegrationEvent;
use App\Models\PortfolioActivity;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;

class AuditKpIntegrationCommand extends Command
{
    protected $signature = 'dosen:audit-kp-integration
        {--source-connection=kp_mysql : Read-only KP database connection}
        {--source-app=kp-farmasi : Source app code}
        {--pending-hours=24 : Age threshold for stale pending outbox rows}
        {--failed-hours=24 : Age threshold for old failed outbox rows}
        {--show-rows : Show up to 25 row references per finding}';

    protected $description = 'Read-only audit comparing KP integration outbox rows with dosen-farmasi consumer records.';

    public function handle(): int
    {
        $sourceConnection = (string) $this->option('source-connection');
        $sourceApp = (string) $this->option('source-app');
        $showRows = (bool) $this->option('show-rows');

        $this->line('KP to Dosen integration audit');
        $this->line('Source app: '.$sourceApp);
        $this->line('Source connection: '.$sourceConnection);
        $this->line('Mode: read-only');

        $sourceAvailable = $this->sourceOutboxAvailable($sourceConnection);
        $this->line('KP outbox table: '.($sourceAvailable ? 'available' : 'unavailable'));

        if ($sourceAvailable) {
            $this->auditSourceOutbox($sourceConnection, $sourceApp, $showRows);
        }

        $this->auditConsumerObjects($sourceApp, $showRows);
        $this->auditDuplicatePortfolioIdentities($sourceApp, $showRows);

        return self::SUCCESS;
    }

    private function sourceOutboxAvailable(string $connection): bool
    {
        try {
            DB::connection($connection)->getPdo();

            return Schema::connection($connection)->hasTable('integration_outbox_events');
        } catch (Throwable) {
            return false;
        }
    }

    private function auditSourceOutbox(string $connection, string $sourceApp, bool $showRows): void
    {
        $outbox = DB::connection($connection)->table('integration_outbox_events')
            ->where('source_app', $sourceApp);
        $consumerEventIds = IntegrationEvent::query()
            ->where('source_app', $sourceApp)
            ->pluck('event_id')
            ->all();

        $sentMissing = (clone $outbox)
            ->where('status', 'SENT')
            ->when($consumerEventIds !== [], fn ($query) => $query->whereNotIn('event_id', $consumerEventIds))
            ->orderBy('id');

        $stalePending = (clone $outbox)
            ->where('status', 'PENDING')
            ->where('created_at', '<=', now()->subHours(max(1, (int) $this->option('pending-hours'))))
            ->orderBy('id');

        $oldFailed = (clone $outbox)
            ->where('status', 'FAILED')
            ->where('updated_at', '<=', now()->subHours(max(1, (int) $this->option('failed-hours'))))
            ->orderBy('id');

        $this->finding('SENT without consumer event', (clone $sentMissing)->count());
        $this->finding('Stale PENDING outbox', (clone $stalePending)->count());
        $this->finding('Old FAILED outbox', (clone $oldFailed)->count());

        if ($showRows) {
            $this->showSourceRows('sent-missing', $sentMissing);
            $this->showSourceRows('stale-pending', $stalePending);
            $this->showSourceRows('old-failed', $oldFailed);
        }
    }

    private function auditConsumerObjects(string $sourceApp, bool $showRows): void
    {
        $processed = IntegrationEvent::query()
            ->where('source_app', $sourceApp)
            ->where('status', 'PROCESSED')
            ->orderBy('id')
            ->get(['id', 'event_id', 'event_type', 'source_record_id', 'lecturer_core_id', 'payload', 'related_records']);

        $missingInbox = $processed->filter(function (IntegrationEvent $event) use ($sourceApp): bool {
            $id = data_get($event->related_records, 'inbox_item_id');

            return $id && ! InboxItem::query()
                ->whereKey($id)
                ->where('source_app', $sourceApp)
                ->where('source_record_id', $event->source_record_id)
                ->exists();
        });

        $missingCalendar = $processed->filter(function (IntegrationEvent $event) use ($sourceApp): bool {
            $id = data_get($event->related_records, 'calendar_event_id');

            return $id && ! CalendarEvent::query()
                ->whereKey($id)
                ->where('source_app', $sourceApp)
                ->where('source_record_id', $event->source_record_id)
                ->exists();
        });

        $completedMissingPortfolio = $processed
            ->where('event_type', 'kp.exam.completed')
            ->filter(function (IntegrationEvent $event) use ($sourceApp): bool {
                $portfolioId = data_get($event->related_records, 'portfolio_activity_id');
                if ($portfolioId) {
                    return ! PortfolioActivity::query()
                        ->whereKey($portfolioId)
                        ->where('source_app', $sourceApp)
                        ->where('source_record_id', $event->source_record_id)
                        ->where('verification_status', 'SYSTEM_VERIFIED')
                        ->exists();
                }

                return ! PortfolioActivity::query()
                    ->where('source_app', $sourceApp)
                    ->where('source_record_id', $event->source_record_id)
                    ->where('lecturer_core_id', $event->lecturer_core_id ?: data_get($event->payload, 'lecturer_core_id'))
                    ->where('verification_status', 'SYSTEM_VERIFIED')
                    ->exists();
            });

        $this->finding('PROCESSED with missing inbox object', $missingInbox->count());
        $this->finding('PROCESSED with missing calendar object', $missingCalendar->count());
        $this->finding('Completed event missing SYSTEM_VERIFIED portfolio', $completedMissingPortfolio->count());

        if ($showRows) {
            $this->showConsumerRows('missing-inbox', $missingInbox);
            $this->showConsumerRows('missing-calendar', $missingCalendar);
            $this->showConsumerRows('missing-portfolio', $completedMissingPortfolio);
        }
    }

    private function auditDuplicatePortfolioIdentities(string $sourceApp, bool $showRows): void
    {
        $duplicates = PortfolioActivity::query()
            ->select('source_entity', 'source_record_id', 'lecturer_core_id', DB::raw('COUNT(*) as duplicate_count'))
            ->where('source_app', $sourceApp)
            ->whereNotNull('source_record_id')
            ->groupBy('source_entity', 'source_record_id', 'lecturer_core_id')
            ->havingRaw('COUNT(*) > 1')
            ->orderByDesc('duplicate_count');

        $this->finding('Duplicate portfolio source identity', (clone $duplicates)->count());

        if ($showRows) {
            (clone $duplicates)->limit(25)->get()->each(fn ($row) => $this->line(
                'duplicate-portfolio '.$row->source_entity.' '.$row->source_record_id.' lecturer='.$row->lecturer_core_id.' count='.$row->duplicate_count
            ));
        }
    }

    private function finding(string $label, int $count): void
    {
        $this->line($label.': '.$count);
    }

    private function showSourceRows(string $prefix, $query): void
    {
        (clone $query)->limit(25)->get(['event_id', 'event_type', 'source_record_id', 'status'])
            ->each(fn ($row) => $this->line($prefix.' '.$row->event_id.' '.$row->event_type.' '.$row->source_record_id.' '.$row->status));
    }

    private function showConsumerRows(string $prefix, $events): void
    {
        $events->take(25)->each(fn (IntegrationEvent $event) => $this->line(
            $prefix.' '.$event->event_id.' '.$event->event_type.' '.$event->source_record_id
        ));
    }
}
