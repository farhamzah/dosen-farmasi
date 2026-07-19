# M7 Acceptance Report

Date: 2026-07-18

Status: `M7 technical integration complete; strict acceptance pending`.

## Baseline

Initial MySQL baseline before live events:

- TA outbox `PENDING`: 0
- TA outbox `PROCESSING`: 0
- TA outbox `SENT`: 0
- TA outbox `FAILED`: 0
- TA outbox `CANCELLED`: 0
- Dosen TA integration events: 0
- Dosen TA inbox: 0
- Dosen TA agenda: 0
- Dosen TA `SYSTEM_VERIFIED` portfolio: 0

TA health initially reported integration disabled/unconfigured. Dosen health reported local DB/Core/KP source OK, KP PSPA/Lab source warnings outside M7 TA scope, shared storage OK, queue `database`, failed events 0, pending events 0.

## MySQL

Both apps used actual `mysql` driver.

TA Farmasi:

- `php artisan migrate:fresh --seed --force`: PASS.
- `php artisan migrate:status`: PASS, all migrations ran.
- `php artisan test`: PASS, 517 tests, 2735 assertions.
- `php artisan test --filter=DosenFarmasiIntegrationOutboxTest`: PASS, 7 tests, 30 assertions.
- `php artisan route:list --except-vendor`: PASS, 212 routes.
- `php artisan ta:integration-health`: PASS command execution.
- `php artisan ta:audit-dosen-integration --show-rows`: PASS.
- `php artisan ta:prune-integration-outbox --show-rows`: PASS dry-run.
- changed-file Pint: PASS.
- `npm.cmd run build`: PASS.

Dosen Farmasi:

- `php artisan migrate:fresh --seed --force`: PASS.
- `php artisan migrate:status`: PASS, all migrations ran.
- `php artisan test`: PASS, 55 tests, 239 assertions.
- `php artisan route:list --except-vendor`: PASS, 60 routes.
- `php artisan dosen:storage-check`: PASS.
- `php artisan dosen:integration-health`: PASS command execution with KP PSPA/Lab source warnings outside M7.
- `php artisan dosen:audit-ta-integration --source-database=ta_farmasi_ubp --show-rows`: PASS, all findings 0.
- changed-file Pint for new TA audit command: PASS.
- `npm.cmd run build`: PASS.

## Integration Client

`ta-farmasi` client was configured locally for acceptance with:

- abilities `events:push` and `integration:health`;
- active client;
- SHA-256 token hash length 64;
- no plaintext token column;
- plaintext token not written to docs, output, screenshots, or repository files.

## Live HTTP Success Path

Live HTTP delivery used TA business services and a local Dosen HTTP server. Manual JSON payloads were not used for the main path.

Observed final TA outbox:

- `PENDING`: 0
- `PROCESSING`: 0
- `SENT`: 43
- `FAILED`: 0
- `CANCELLED`: 0

Observed final Dosen consumer:

- TA `PROCESSED` integration events: 43
- TA `FAILED`: 0
- TA `DUPLICATE`: 0
- TA inbox: 25
- TA agenda: 9
- TA `SYSTEM_VERIFIED` portfolio: 4
- database notifications: 73
- queue pending: 0
- failed jobs: 0

Lifecycle coverage:

- supervisor assigned: PASS.
- supervisor changed: PASS; audit reported old lecturer not left active.
- examiner assigned: PASS through schedule approval.
- examiner changed: PASS through reschedule approval.
- scheduled: PASS.
- rescheduled: PASS; source identity stayed stable and audit duplicate findings 0.
- completed: PASS; completed events created `SYSTEM_VERIFIED` portfolio.
- cancelled: PASS; cancellation used a separate record and did not create completed portfolio.

## Failure Drills

- consumer down: PASS. Business committed, delivery became retryable `CONNECTION_FAILURE`, explicit retry after recovery delivered without pending/failed residue.
- invalid token: PASS. Unauthorized delivery became `FAILED` with `AUTHORIZATION_FAILED`; valid token retry delivered and failed count returned to 0.
- connection failure / timeout style failure: PASS via consumer-down drill.
- HTTP 500: PASS. Classified `TEMPORARY_HTTP_500`, retryable, recovered.
- HTTP 429: PASS. Classified `TEMPORARY_HTTP_429`, retryable, recovered.
- concurrency two workers: PASS. Final state pending 0, processing 0, failed 0; latest effective attempt count 1.
- orphan `PROCESSING`: PASS. Dry-run detected 1 stale lock and changed nothing; explicit recovery restored to `PENDING`; retry delivered; `SENT` rows were not pruned.

## Audit

`dosen:audit-ta-integration --source-database=ta_farmasi_ubp --show-rows`:

- SENT without consumer event: 0
- Stale PENDING outbox: 0
- Old FAILED outbox: 0
- PROCESSED with missing inbox object: 0
- PROCESSED with missing calendar object: 0
- Completed event missing SYSTEM_VERIFIED portfolio: 0
- Duplicate inbox source identity: 0
- Duplicate calendar source identity: 0
- Duplicate portfolio source identity: 0
- Changed assignment left old lecturer active: 0

## Document Reference

Official TA document references were not available in the current TA producer payload. The producer sends an empty `document_references` array for completed events. Existing Dosen tests still cover unsafe source document references and path traversal rejection.

Limitation: surat tugas, undangan, and berita acara official document references cannot be marked accepted until TA has a compatible official document reference source contract.

## Email And Queue

Database notification queue was processed with `php artisan queue:work --stop-when-empty --tries=1`.

- queue pending: 0
- failed jobs: 0
- database notifications: 73

No real email was sent. Email delivery remains dependent on development/log mailer or configured mailer plus lecturer preferences.

## Browser Functional QA

Not completed in this pass. Browser QA requires Chrome/Edge manual verification for real Core nonproduction users. Current evidence is automated/server-side and live HTTP. Visual premium UI remains explicitly out of M7 and belongs to M8.

## Gate Decision

M7 is not marked complete because browser functional QA and official document reference acceptance are still pending.

Decision: `M7 technical integration complete; strict acceptance pending`.

M8 implementation is not started.
