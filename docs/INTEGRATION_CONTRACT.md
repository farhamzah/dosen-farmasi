# Integration Contract

## Strategy

Primary path: push event API owned by `dosen-farmasi`.

Transition path: pull adapter read-only per source app when push event is not available.

Source applications must not write directly to `dosen-farmasi` business tables.

## Canonical Endpoint

```http
POST /api/internal/v1/events
Authorization: Bearer <service-token>
Content-Type: application/json
```

Token storage must be hashed. Plain token is shown only once on creation.

## Envelope

```json
{
  "event_id": "uuid",
  "event_type": "ta.exam.scheduled",
  "event_version": 1,
  "source_app": "ta-farmasi",
  "source_record_id": "SIDANG-2026-00123",
  "source_revision": 1,
  "correlation_id": null,
  "occurred_at": "2026-07-16T10:00:00+07:00",
  "payload": {
    "lecturer_core_id": "1"
  }
}
```

## Idempotency

- `event_id` unique.
- Replayed event with same payload returns duplicate/accepted without side effects.
- Same `event_id` with different payload hash is rejected with validation error.
- Domain uniqueness uses source app, source entity/type, source record ID, lecturer Core ID, and event semantics.
- Reschedule/completion/cancellation update the same aggregate when applicable.
- `source_revision` protects aggregates from stale events.

## Implemented Event Types

- `tu.letter.assigned`
- `tu.letter.published`
- `tu.letter.cancelled`
- `ta.exam.scheduled`
- `ta.exam.rescheduled`
- `ta.exam.completed`
- `ta.exam.cancelled`
- `ta.supervisor.assigned`
- `ta.supervisor.changed`
- `ta.examiner.assigned`
- `ta.examiner.changed`
- `kp.supervisor.assigned`
- `kp.supervisor.changed`
- `kp.examiner.assigned`
- `kp.examiner.changed`
- `kp.exam.scheduled`
- `kp.exam.rescheduled`
- `kp.exam.completed`
- `kp.exam.cancelled`
- `kpspa.supervisor.assigned`
- `kpspa.preceptor.assigned`
- `kpspa.examiner.assigned`
- `kpspa.activity.scheduled`
- `kpspa.activity.completed`
- `kpspa.activity.cancelled`
- `kpspa.assessment.finalized`
- `lab.lecturer.assigned`
- `lab.schedule.created`
- `lab.schedule.rescheduled`
- `lab.activity.completed`
- `lab.activity.cancelled`

Detail payload M5 ada di `docs/M5_EVENT_CATALOGUE.md`.

## Authentication and Authorization

- Token is sent as `Authorization: Bearer <service-token>`.
- Token hash is stored with SHA-256; plaintext is shown only once on rotation.
- Client must be active, not revoked, not expired, and must have the required ability.
- `source_app` must match the authenticated client `app_code`; mismatch returns forbidden.

## Failure Handling

Events store processing status, attempt count, processed time, safe error code/message, next retry time, result summary, related records, and failure rows. Admin can retry failed events or ignore failed/queued events. Error logs must not include tokens, passwords, file contents, or sensitive payload fields.

Failure categories:

- `VALIDATION`
- `LECTURER_NOT_FOUND`
- `LECTURER_AMBIGUOUS`
- `DOCUMENT_NOT_FOUND`
- `DATABASE_SOURCE_UNAVAILABLE`
- `CONTRACT_VIOLATION`
- `UNSUPPORTED_EVENT`
- `PROCESSING_ERROR`

`integration_failures.retryable` menentukan apakah event layak retry otomatis/manual.

## Action URL

Action URLs must be internal and allowlisted per integration client. Do not trust arbitrary URL from event payload.

## Pull Adapter

Pull adapter membaca outbox canonical source app secara read-only. Command:

```bash
php artisan dosen:sync-integrations kp-farmasi --dry-run --from=2026-01-01 --to=2026-12-31 --limit=100
```

Cursor hanya maju setelah row berhasil diingest.

## KP Producer Pilot

`kp-farmasi` M6 memakai push delivery dari outbox lokal `integration_outbox_events`. Consumer tetap menerima envelope yang sama melalui endpoint canonical. Token dibuat dengan:

```bash
php artisan dosen:integration-client-token kp-farmasi
```

Plain token hanya tampil sekali dan tidak boleh dicatat ke repository.

## TA Producer Pilot

`ta-farmasi` M7 memakai push delivery dari outbox lokal `integration_outbox_events`. Consumer tetap menerima envelope yang sama melalui endpoint canonical dan authenticated client `ta-farmasi`.

Event producer TA yang dipakai:

- `ta.supervisor.assigned`
- `ta.supervisor.changed`
- `ta.examiner.assigned`
- `ta.examiner.changed`
- `ta.exam.scheduled`
- `ta.exam.rescheduled`
- `ta.exam.completed`
- `ta.exam.cancelled`

Token dibuat dengan:

```bash
php artisan dosen:integration-client-token ta-farmasi
```

Plain token hanya tampil sekali dan tidak boleh dicatat ke repository.
