<?php

namespace App\Services;

use App\Jobs\ProcessIntegrationEventJob;
use App\Models\IntegrationClient;
use App\Models\IntegrationEvent;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;

class IntegrationEventIngestionService
{
    public function __construct(private readonly IntegrationEventRegistry $registry) {}

    public function ingest(array $payload, IntegrationClient $client): array
    {
        $this->validateEnvelope($payload, $client);

        $payloadHash = hash('sha256', json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        $existing = IntegrationEvent::query()->where('event_id', $payload['event_id'])->first();

        if ($existing) {
            if ($existing->payload_hash !== $payloadHash) {
                throw ValidationException::withMessages(['event_id' => 'Event ID sudah digunakan dengan payload berbeda.']);
            }

            return ['status' => 'duplicate', 'event_id' => $existing->event_id, 'processing_status' => $existing->status];
        }

        $event = DB::transaction(fn () => IntegrationEvent::query()->create([
            'event_id' => $payload['event_id'],
            'integration_client_id' => $client->id,
            'event_type' => $payload['event_type'],
            'event_version' => $payload['event_version'],
            'source_app' => $payload['source_app'],
            'source_record_id' => $payload['source_record_id'],
            'source_revision' => $payload['source_revision'] ?? 1,
            'correlation_id' => $payload['correlation_id'] ?? null,
            'occurred_at' => $payload['occurred_at'],
            'received_at' => now(),
            'lecturer_core_id' => $payload['lecturer_core_id'] ?? ($payload['payload']['lecturer_core_id'] ?? null),
            'payload' => $payload['payload'],
            'payload_hash' => $payloadHash,
            'status' => 'QUEUED',
        ]));

        if (config('dosen_farmasi.integration.sync_processing', true)) {
            app(IntegrationEventProcessor::class)->process($event);
        } else {
            ProcessIntegrationEventJob::dispatch($event->id);
        }

        return ['status' => 'accepted', 'event_id' => $event->event_id, 'processing_status' => $event->fresh()->status];
    }

    private function validateEnvelope(array $payload, IntegrationClient $client): void
    {
        if (strlen(json_encode($payload)) > config('dosen_farmasi.integration.max_payload_bytes')) {
            throw ValidationException::withMessages(['payload' => 'Payload terlalu besar.']);
        }

        if (array_key_exists('source_app', $payload) && $payload['source_app'] !== $client->app_code) {
            throw new HttpException(403, 'source_app tidak cocok dengan client.');
        }

        $validator = Validator::make($payload, [
            'event_id' => ['required', 'uuid'],
            'event_type' => ['required', 'string', 'max:100'],
            'event_version' => ['required', 'integer', 'in:'.config('dosen_farmasi.integration.supported_event_version')],
            'source_app' => ['required', 'string', 'max:100'],
            'source_record_id' => ['required', 'string', 'max:255'],
            'source_revision' => ['nullable', 'integer', 'min:1'],
            'correlation_id' => ['nullable', 'uuid'],
            'occurred_at' => ['required', 'date'],
            'lecturer_core_id' => ['nullable', 'string', 'max:100'],
            'payload' => ['required', 'array'],
        ]);

        $validator->after(function ($validator) use ($payload): void {
            if (! $this->registry->isKnown((string) ($payload['event_type'] ?? ''))) {
                $validator->errors()->add('event_type', 'event_type tidak dikenal.');
            }
        });

        $validator->validate();
    }
}
