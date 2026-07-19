<?php

namespace App\Http\Controllers;

use App\Models\IntegrationEvent;
use App\Services\AuditLogger;
use App\Services\IntegrationEventProcessor;
use Illuminate\Http\Request;

class AdminIntegrationEventController extends Controller
{
    public function retry(Request $request, IntegrationEvent $integrationEvent, IntegrationEventProcessor $processor, AuditLogger $audit)
    {
        abort_unless($integrationEvent->status === 'FAILED', 422, 'Hanya event FAILED yang dapat dicoba ulang.');

        $processed = $processor->process($integrationEvent);
        $audit->record('integration.event.retry', $request->user(), $processed, [
            'event_id' => $processed->event_id,
            'status' => $processed->status,
        ], $request);

        return back();
    }

    public function ignore(Request $request, IntegrationEvent $integrationEvent, AuditLogger $audit)
    {
        abort_unless(in_array($integrationEvent->status, ['FAILED', 'QUEUED'], true), 422, 'Event ini tidak dapat diabaikan.');

        $integrationEvent->update([
            'status' => 'IGNORED',
            'processed_at' => now(),
            'result_summary' => $request->string('reason')->toString() ?: 'Diabaikan oleh admin.',
        ]);

        $audit->record('integration.event.ignore', $request->user(), $integrationEvent, [
            'event_id' => $integrationEvent->event_id,
            'reason' => $integrationEvent->result_summary,
        ], $request);

        return back();
    }
}
