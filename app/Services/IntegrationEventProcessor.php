<?php

namespace App\Services;

use App\Exceptions\IntegrationProcessingException;
use App\Models\IntegrationEvent;
use App\Models\IntegrationFailure;
use Throwable;

class IntegrationEventProcessor
{
    public function __construct(private readonly IntegrationEventRegistry $registry) {}

    public function process(IntegrationEvent $event): IntegrationEvent
    {
        $event->increment('attempt_count');
        $event->update(['status' => 'PROCESSING']);

        try {
            $handler = $this->registry->handlerFor($event->event_type);

            if (! $handler) {
                $event->update(['status' => 'IGNORED', 'result_summary' => 'No handler registered.']);

                return $event->fresh();
            }

            $summary = $handler->handle($event);
            $event->update([
                'status' => $summary['status'] ?? 'PROCESSED',
                'processed_at' => now(),
                'result_summary' => $summary['summary'] ?? null,
                'related_records' => $summary['related_records'] ?? null,
            ]);
        } catch (Throwable $exception) {
            $safeMessage = substr($exception->getMessage(), 0, 1000);
            $category = $exception instanceof IntegrationProcessingException ? $exception->category : 'HANDLER_EXCEPTION';
            $retryable = $exception instanceof IntegrationProcessingException ? $exception->retryable : true;
            $event->update([
                'status' => 'FAILED',
                'last_error_code' => $category,
                'last_error_message' => $safeMessage,
                'error_code' => $category,
                'error_message' => $safeMessage,
                'next_retry_at' => $retryable ? now()->addMinutes(5) : null,
            ]);

            IntegrationFailure::query()->create([
                'integration_event_id' => $event->id,
                'error_code' => class_basename($exception::class),
                'failure_category' => $category,
                'retryable' => $retryable,
                'error_message' => $safeMessage,
                'attempt_count' => $event->attempt_count,
                'safe_context' => ['event_type' => $event->event_type, 'source_app' => $event->source_app],
                'created_at' => now(),
            ]);
        }

        return $event->fresh();
    }
}
