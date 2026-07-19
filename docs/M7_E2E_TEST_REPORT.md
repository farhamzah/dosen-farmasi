# M7 E2E Test Report

Date: 2026-07-18

Status: TA producer implementation, MySQL regression, live HTTP main path, failure drills, queue processing, and cross-app audit passed. Strict M7 acceptance remains pending for browser functional QA and official TA document reference acceptance.

## Automated Fixture Coverage

TA producer:

- supervisor assignment creates outbox in the same business transaction;
- business rollback removes assignment and outbox;
- exam schedule payload uses canonical envelope;
- exam completion creates canonical event;
- `202` accepted becomes `SENT`;
- `401` becomes permanent `FAILED`;
- `429` and `500` remain retryable `PENDING`;
- connection failure remains retryable;
- dry-run delivery does not mutate rows;
- audit command reports findings without mutation.

## Local Command Regression

Executed in `apps/ta-farmasi`:

- `php artisan migrate:fresh --seed --force`: PASS on MySQL.
- `php artisan migrate:status`: PASS.
- `php artisan test`: PASS, 517 tests, 2735 assertions.
- `php artisan test --filter=DosenFarmasiIntegrationOutboxTest`: PASS, 7 tests, 30 assertions.
- `php artisan route:list --except-vendor`: PASS, 212 routes.
- `php artisan ta:integration-health`: PASS command execution; integration disabled/unconfigured locally.
- `php artisan ta:audit-dosen-integration --show-rows`: PASS, all findings 0 on clean local state.
- `php artisan ta:prune-integration-outbox --show-rows`: PASS dry-run.
- changed-file Pint: PASS.
- `npm.cmd run build`: PASS.

Executed in `apps/dosen-farmasi`:

- `php artisan migrate:fresh --seed --force`: PASS on MySQL.
- `php artisan migrate:status`: PASS.
- `php artisan test`: PASS, 55 tests, 239 assertions.
- `php artisan test --filter=IntegrationKernelM4Test`: PASS, 5 tests, 39 assertions.
- `php artisan test --filter=IntegrationM5SourceLifecycleTest`: PASS, 8 tests, 44 assertions.
- `php artisan route:list --except-vendor`: PASS, 60 routes.
- `php artisan dosen:storage-check`: PASS.
- `php artisan dosen:integration-health`: PASS command execution.
- `php artisan dosen:audit-ta-integration --source-database=ta_farmasi_ubp --show-rows`: PASS, all findings 0.
- `npm.cmd run build`: PASS.

## Live HTTP Main Path

Business actions were executed through TA services:

- supervisor assigned;
- supervisor changed;
- examiner assigned;
- examiner changed;
- exam scheduled;
- exam rescheduled;
- exam completed;
- exam cancelled.

Final observed state:

- TA outbox `SENT`: 43.
- TA outbox `PENDING`/`PROCESSING`/`FAILED`: 0.
- Dosen TA integration events `PROCESSED`: 43.
- Dosen TA inbox: 25.
- Dosen TA agenda: 9.
- Dosen TA `SYSTEM_VERIFIED` portfolio: 4.
- Dosen database notifications: 73 after queue worker.
- Queue pending: 0.
- Failed jobs: 0.

## Failure Drills

- consumer down: PASS.
- invalid token: PASS.
- connection failure / timeout style failure: PASS.
- HTTP `500`: PASS.
- HTTP `429`: PASS.
- two-worker concurrency: PASS.
- orphan `PROCESSING` dry-run and explicit recovery: PASS.

## Pending Acceptance

Not executed in this pass:

- browser QA for TA outbox monitoring and Dosen portfolio/inbox/agenda side effects;
- official TA document reference acceptance for surat tugas, undangan, and berita acara.

## Final State

M7 remains `technical integration complete; strict acceptance pending`.
