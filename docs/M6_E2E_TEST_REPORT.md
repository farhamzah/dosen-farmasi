# M6 E2E Test Report

Date: 2026-07-17

Status: KP -> Dosen main path, failure drills, browser QA sweep, MySQL regression, and audit passed; strict M6 acceptance remains pending for one browser-tool-blocked admin page.

## Fixture Coverage

KP producer:

- outbox is created in the same business transaction;
- business rollback cancels outbox creation;
- supervisor/examiner assignment, schedule, reschedule, completion, and cancellation create outbox rows;
- `202` accepted and duplicate `200` responses become `SENT`;
- `401` becomes `FAILED`;
- `429` and `5xx` remain retryable `PENDING`;
- connection failure/timeout remain retryable;
- dry-run delivery does not mutate rows;
- prune defaults to dry-run;
- orphan `PROCESSING` recovery requires explicit execution and confirmation.

Dosen consumer:

- KP assignment/schedule/reschedule/completed/cancelled events are processed;
- duplicate and stale events do not duplicate side effects;
- completed events create `SYSTEM_VERIFIED` portfolio activity;
- changed supervisor event closes the old lecturer inbox and creates/updates the new lecturer inbox;
- unsafe document references fail safely.

## Live HTTP Main Path

Business actions were executed through KP services, not manual JSON:

- supervisor assigned;
- examiner assigned;
- exam scheduled;
- exam rescheduled;
- exam completed;
- exam cancelled.

Observed baseline before failure drills:

- KP outbox rows: 20.
- KP outbox `SENT`: 20.
- HTTP `202` responses: 20.
- Dosen integration events `PROCESSED`: 20.
- Dosen agenda rows: 6.
- Dosen portfolio `SYSTEM_VERIFIED`: 3.
- Cross-app audit findings: all 0.

## Failure And Recovery

Live drills:

- consumer down: PASS, retryable `CONNECTION_FAILURE`, recovered to `SENT`;
- invalid token: PASS, `401` classified as `AUTHORIZATION_FAILED`, no token leaked, recovered by explicit retry after valid config;
- timeout/connection failure: PASS, retryable `PENDING`, recovered to `SENT`;
- HTTP `500`: PASS, `TEMPORARY_HTTP_500`, recovered to `SENT`;
- two-worker concurrency: PASS, one effective send, attempt count 1, no stuck `PROCESSING`;
- orphan recovery: PASS, dry-run detected stale lock, explicit recovery returned row to `PENDING`, retry delivered.

Automated drill:

- HTTP `429`: PASS in `DosenFarmasiIntegrationOutboxTest`, classified as `TEMPORARY_HTTP_429` and left `PENDING`.

Post-drill audit:

- all findings 0.
- no pending, failed, or processing outbox rows remained after recovery.

## Browser QA

Dosen:

- Core login succeeded with the operator-provided account.
- Role selection appeared with Dosen/Admin options.
- Dosen dashboard, inbox, agenda, notifications, and portfolio pages loaded.
- A live KP assignment was produced for the logged-in lecturer identity and appeared in inbox.
- Mark-read worked.
- Console errors: 0.
- Horizontal overflow: 0.

Dosen Admin:

- Admin dashboard loaded.
- Portfolio Activity, Integration Event, and Integration Failure pages loaded after Filament assets were published.
- Console errors: 0 on verified pages.
- Horizontal overflow: 0.
- `/admin/integration-clients` was blocked by in-app browser URL policy, so strict browser acceptance remains pending.

KP Admin:

- Admin login and dashboard loaded.
- Outbox list and detail pages loaded.
- Outbox detail for a `SENT` row did not expose token material.
- A missing layout component error was fixed by switching the outbox views to `layouts.app`.
- Console errors: 0.
- Horizontal overflow: 0.

Responsive:

- 9 pages x 3 viewports = 27 checks.
- Viewports: desktop 1440x900, tablet 768x1024, mobile 390x844.
- Console errors: 0.
- Horizontal overflow: 0.
- 500 pages: 0.

## Regression

KP Farmasi:

- `php artisan optimize:clear`: PASS.
- `php artisan migrate:fresh --seed --force`: PASS on MySQL.
- Focused outbox suite after `429` coverage: PASS, 7 tests, 41 assertions.
- Full MySQL suite: PASS with local `APP_URL=http://localhost` override, 249 tests, 1555 assertions.
- `php artisan route:list --except-vendor`: PASS, 255 routes.
- `php artisan kp:integration-health`: PASS command, pending 0, failed 0.
- `php artisan kp:prune-integration-outbox --days=90 --show-rows`: PASS dry-run.
- `npm.cmd run build`: PASS.
- Changed-file Pint: PASS.

Dosen Farmasi:

- `php artisan optimize:clear`: PASS.
- `php artisan migrate:fresh --seed --force`: PASS on MySQL.
- `php artisan test`: PASS, 51 tests, 229 assertions.
- `php artisan route:list --except-vendor`: PASS, 60 routes.
- `php artisan dosen:storage-check`: PASS.
- `php artisan dosen:integration-health`: PASS command; KP PSPA/Lab source warnings remain outside M6.
- `php artisan dosen:audit-kp-integration --show-rows`: PASS after both final database resets, all findings 0.
- `npm.cmd run build`: PASS.
- Changed-file Pint: PASS.

## Email Queue

No real email was sent. The Dosen suite uses notification fakes to validate mail-channel behavior, preferences, queue expectations, and duplicate guards.

## Final State

The final regression used `migrate:fresh`, so local acceptance rows were reset after the evidence was recorded. The final audit on the clean reset state is also 0.

M6 remains `technical integration complete / acceptance pending`.

M7 was not started.
