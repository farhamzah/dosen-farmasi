# M6 Acceptance Report

Date: 2026-07-17

Status: M6 technical integration complete; failure drills and broad browser QA passed; strict final acceptance remains pending because one required admin browser page was blocked by the in-app browser URL policy.

M7 TA producer work was not started.

No password, plaintext token, database credential, SMTP credential, session cookie, or authorization header was written to code or docs.

## Gate M6

Decision: `ACCEPTANCE PENDING`.

Passed:

- KP -> Dosen main HTTP path on nonproduction MySQL.
- Consumer down, invalid token, timeout/connection failure, `5xx`, concurrency, and orphan recovery drills.
- `429` retryability through automated delivery coverage.
- Dosen Core login, role selection, Dosen role pages, Admin role dashboard/pages, KP admin outbox pages, responsive checks, and console/network smoke checks except the blocked page noted below.
- MySQL regression, builds, route lists, changed-file Pint, retention dry-run, and cross-app audit.

Not fully closed:

- Browser access to `/admin/integration-clients` was blocked by the Codex in-app browser URL policy (`ERR_NETWORK_IO_SUSPENDED`). Other admin pages loaded after publishing Filament assets.
- `dosen:integration-health` still reports KP PSPA and Lab source connections failed. Those sources are outside the M6 KP producer scope, but they remain visible operational warnings.

## Baseline Before Failure Drills

Dosen health:

- local DB: OK.
- Core: OK.
- KP source: OK.
- KP PSPA source: failed, outside M6 KP producer scope.
- Lab source: failed, outside M6 KP producer scope.
- storage: OK.
- queue: database.
- failed/pending integration work: 0.

KP health:

- integration enabled: yes.
- base URL configured: yes.
- token configured: yes.
- pending outbox: 0.
- failed outbox: 0.
- queue: sync.

Cross-app audit:

- `SENT` without consumer event: 0.
- stale `PENDING`: 0.
- old `FAILED`: 0.
- processed event missing inbox: 0.
- processed event missing calendar: 0.
- completed event missing `SYSTEM_VERIFIED` portfolio: 0.
- duplicate portfolio source identity: 0.

Main path baseline:

- KP outbox: 20 `SENT`, 0 `PENDING`, 0 `PROCESSING`, 0 `FAILED`.
- Dosen integration events: 20 `PROCESSED`, 0 `FAILED`, 0 `DUPLICATE`.
- Dosen domain state: 5 inbox rows, 6 agenda rows, 3 `SYSTEM_VERIFIED` portfolio rows.

## Failure Drills

### Consumer Down

Setup: Dosen server on port 3002 was stopped, then a real KP supervisor assignment business action was executed.

Expected:

- KP business transaction commits.
- Outbox remains retryable.
- Error storage does not expose bearer token or auth header.
- After Dosen returns, retry sends one effective consumer event.

Actual:

- Outbox event `a4e09c39-c216-4b4b-9dae-0d475c1d442c` created and business assignment committed.
- First delivery: `PENDING`, attempt count 1, no HTTP status, error `CONNECTION_FAILURE`.
- Stored error contained no bearer token.
- Immediate retry respected backoff and did not tight-loop.
- After Dosen restart and due-time reset, official retry command delivered the event.
- Final status: `SENT`; Dosen `PROCESSED`; cross-app audit all 0.

Result: PASS.

### Invalid Token

Setup: runtime configuration override used an invalid local token for one delivery attempt; `.env` was not mutated and no token value was printed.

Expected:

- Business action commits.
- Unauthorized delivery is classified permanent/configuration failure.
- Error storage does not expose the provided token.
- Restoring valid runtime configuration allows explicit operator retry.

Actual:

- Outbox event `86b73163-6da7-4614-ae6e-55f4c44c0f4f` created and business assignment committed.
- First delivery: `FAILED`, attempt count 1, HTTP `401`, error `AUTHORIZATION_FAILED`.
- Stored error contained no bearer token.
- Explicit retry after valid config restored delivered the event.
- Final status: `SENT`; Dosen `PROCESSED`; audit all 0.

Result: PASS.

### Timeout / Connection Failure

Setup: runtime base URL override pointed to an unavailable local port with short timeouts.

Expected:

- Business action commits.
- Delivery is retryable.
- Backoff is applied.
- Recovery does not duplicate Dosen side effects.

Actual:

- Outbox event `38d3c650-3465-40ba-8e35-0fe35e384ae0` created and business assignment committed.
- First delivery: `PENDING`, attempt count 1, no HTTP status, error `CONNECTION_FAILURE`.
- Event was later retried with official command after due-time reset.
- Final status: `SENT`; Dosen `PROCESSED`; audit all 0.

Result: PASS.

### HTTP 5xx

Setup: a temporary local endpoint returned HTTP `500`.

Expected:

- HTTP `5xx` is retryable.
- Outbox does not move to permanent `FAILED`.
- Recovery sends once after endpoint restoration.

Actual:

- Outbox event `b41e9377-c84e-459f-ac60-548f32ee653b` created and business assignment committed.
- First delivery: `PENDING`, attempt count 1, HTTP `500`, error `TEMPORARY_HTTP_500`.
- After endpoint restoration and due-time reset, official retry delivered the event.
- Final status: `SENT`; audit all 0.

Result: PASS.

### HTTP 429

Setup: automated delivery coverage was extended to include an HTTP `429` response.

Expected:

- `429` is retryable and remains `PENDING`.
- Error classification is explicit.

Actual:

- `DosenFarmasiIntegrationOutboxTest` asserts HTTP `429` becomes `PENDING` with `TEMPORARY_HTTP_429`.
- Focused KP outbox suite passed: 7 tests, 41 assertions.

Result: PASS by automated coverage.

### Two-Worker Concurrency

Setup: one pending KP outbox event was created with integration disabled, then two official delivery commands were run in parallel for the same event.

Expected:

- MySQL row locking permits one effective delivery.
- No stuck `PROCESSING`.
- Dosen idempotency prevents duplicate side effects.

Actual:

- Outbox event `72bded8f-66f4-424a-85a4-f3e22d9d3d4c` created as `PENDING`.
- Both command layers found the target eligible, but the delivery job lock produced one effective send.
- Final outbox state: `SENT`, attempt count 1, HTTP `202`, no `locked_at`, no error.
- Audit remained all 0.

Result: PASS.

### Orphan PROCESSING Recovery

Setup: one pending event was marked as stale `PROCESSING` with an old lock timestamp.

Expected:

- Dry-run reports the orphan without mutation.
- Recovery requires explicit `--recover-orphans --execute --confirm-execute`.
- Recovered row returns to `PENDING`, then can be retried normally.

Actual:

- Outbox event `840849d4-527e-4f96-9daf-783c98a996a3` marked stale `PROCESSING`.
- Dry-run reported 1 stale `PROCESSING` row and made no changes.
- Explicit recovery changed the row to `PENDING`, cleared `locked_at`, and set error `ORPHAN_RECOVERED`.
- Official retry delivered the event.
- Final audit all 0.

Result: PASS.

## Browser QA

Dosen Core login:

- Login with the operator-provided Core account succeeded.
- Role selection appeared before dashboard entry.
- Dosen role and Admin role could both be selected.

Dosen role pages checked:

- `/dosen/dashboard`
- `/dosen/inbox`
- `/dosen/agenda`
- `/dosen/notifikasi`
- `/dosen/portofolio`

Observed:

- Dosen dashboard loaded.
- Inbox showed a KP assignment for the logged-in lecturer after a live KP assignment event was produced for that lecturer identity.
- Mark-read action changed inbox status to `READ`.
- Agenda, notification, and portfolio pages loaded empty/valid for that user.
- Console errors: 0 after checks.
- Horizontal overflow: 0.

Dosen admin pages checked:

- `/admin/dashboard`
- `/admin/portfolio-activities`
- `/admin/integration-events`
- `/admin/integration-failures`

Observed:

- Admin dashboard loaded and showed nonzero integration/domain counts before the final regression reset.
- Filament asset errors were found, then fixed by publishing Filament assets.
- Portfolio Activity, Integration Event, and Integration Failure pages then loaded with console errors 0 and no horizontal overflow.
- `/admin/integration-clients` was blocked by in-app browser URL policy and remains the only browser QA gap.

KP admin pages checked:

- `/admin/dashboard`
- `/management/integration/dosen-farmasi-outbox`
- `/management/integration/dosen-farmasi-outbox/27`

Observed:

- KP admin login and dashboard loaded.
- Outbox list loaded with expected status filters.
- Outbox detail loaded for a `SENT` event and did not expose the token.
- A 500 on the outbox pages was fixed by changing the views to extend `layouts.app` instead of using an unavailable `app-layout` component.
- Console errors: 0.
- Horizontal overflow: 0.

Responsive sweep:

- Viewports: 1440x900, 768x1024, 390x844.
- Pages checked: 9.
- Total checks: 27.
- Horizontal overflow: 0.
- Console errors: 0.
- 500 pages: 0.

## Email Queue

No real email was sent during acceptance.

Existing Dosen tests cover:

- queued notification preferences;
- mail channel assertion through fakes;
- duplicate guards for lifecycle events.

The Dosen full test suite passed with this coverage.

## MySQL Regression

Dosen Farmasi:

- `php artisan optimize:clear`: PASS.
- `php artisan migrate:fresh --seed --force`: PASS on MySQL.
- `php artisan test`: PASS, 51 tests, 229 assertions.
- `php artisan route:list --except-vendor`: PASS, 60 routes.
- `php artisan dosen:storage-check`: PASS.
- `php artisan dosen:integration-health`: PASS command; KP PSPA/Lab warnings remain outside M6 scope.
- `php artisan dosen:audit-kp-integration --show-rows`: PASS after both app databases were reset; all findings 0.
- `npm.cmd run build`: PASS.

KP Farmasi:

- `php artisan optimize:clear`: PASS.
- `php artisan migrate:fresh --seed --force`: PASS on MySQL.
- `php artisan test`: PASS with local `APP_URL=http://localhost` override, 249 tests, 1555 assertions.
- `php artisan route:list --except-vendor`: PASS, 255 routes.
- `php artisan kp:integration-health`: PASS command; pending 0, failed 0.
- `php artisan kp:prune-integration-outbox --days=90 --show-rows`: PASS dry-run, no changes.
- `npm.cmd run build`: PASS.

Changed-file Pint:

- Dosen acceptance/test files: PASS.
- KP outbox test and outbox Blade pages: PASS.

## Audit

After final MySQL regression reset of both apps:

- `SENT` without consumer event: 0.
- stale `PENDING`: 0.
- old `FAILED`: 0.
- processed event missing inbox: 0.
- processed event missing calendar: 0.
- completed event missing `SYSTEM_VERIFIED` portfolio: 0.
- duplicate portfolio source identity: 0.

## Fixes Applied During Acceptance

- Extended KP outbox automated coverage for HTTP `429` retry classification.
- Published Dosen Filament assets required by admin pages.
- Fixed KP outbox list/detail views to use the existing `layouts.app` layout.

## Remaining Risks

- The final `migrate:fresh` regression reset local acceptance data. The acceptance observations above are from the live run before the reset; final databases are clean.
- If live HTTP testing is repeated after `migrate:fresh`, reconfigure/rotate the integration client token in runtime secrets.
- The in-app browser blocked `/admin/integration-clients`; this page still needs a manual/browser-tool rerun in an environment that permits it.
- KP PSPA and Lab source health warnings remain outside M6 but should be handled before broader source acceptance.

## Final Decision

M6 is `technical integration complete / acceptance pending`.

M7 remains not started and should stay blocked until the integration-client browser page is verified or the gate owner accepts the documented browser-tool limitation.
