# Status

## 2026-07-17 - M0 Discovery

Status: In progress.

### Read

- `C:\Users\farha\.codex\attachments\783df111-888f-48c6-af90-51fb278ab4d0\pasted-text.txt`
- `apps/dosen-farmasi/docs/Dosen-Farmasi-Planning-dan-Kontrak-Codex.md`
- root `README.md`
- selected composer/package files from Core, TA, TU, Lab, KP
- selected Core migrations for users, lecturers, user app accesses, auth/identity fields
- selected auth/adapter docs and services from TA, TU, Lab, Core

### Commands

- `rg --files apps\dosen-farmasi\docs` - PASS; found one planning/contract document.
- `git -C E:\Aplikasi\farmasi-ubp-workspace status --short` - FAIL; `fatal: not a git repository`.
- `git rev-parse --show-toplevel` - FAIL; `fatal: not a git repository`.
- `rg --files -g AGENTS.md -g AGENTS.override.md -g README.md -g composer.json -g package.json -g artisan -g .env.example` - PASS.
- `Get-ChildItem -Force` at root and `apps` - PASS.
- Several `Get-Content`/`rg` reads for Core/TA/TU/Lab discovery - PASS.

### Findings

- `dosen-farmasi` has no Laravel scaffold yet.
- Workspace siblings use Laravel 12/PHP 8.2; Core/TA use Filament 5.6.
- Core owns identity and has `users`, `lecturers`, and `user_app_accesses`.
- TA/Lab authenticate against Core read-only with `Hash::check`, active/must-change checks, and app access roles.
- TU and TA also have disabled-by-default HTTP Core adapters.
- Private document policy pattern exists in TU.
- Git CLI cannot read this workspace as a repository even though `.git` directory exists.

### Files Added

- `AGENTS.md`
- `README.md`
- `docs/CONTRACT.md`
- `docs/DISCOVERY.md`
- `docs/ARCHITECTURE.md`
- `docs/EXECUTION_PLAN.md`
- `docs/INTEGRATION_CONTRACT.md`
- `docs/DEPLOYMENT.md`
- `docs/IMPLEMENT.md`
- `docs/STATUS.md`
- `docs/adr/0001-workspace-compatibility.md`

### Decisions

- Use Laravel 12/PHP 8.2 and follow Core/TA stack.
- Prefer Filament 5.6 for admin/backoffice.
- Use Core DB read-only bridge for first login implementation, with HTTP adapter default disabled as extension point.
- Local app users must not contain password.
- Use `core_user_id` and `core_lecturer_id` as primary references.

### Git Diagnosis Detail

- `Get-Item .git -Force | Format-List *` showed `.git` is a directory.
- `Test-Path .git\HEAD` returned `False`.
- `Test-Path .git\config` returned `False`.
- `Get-ChildItem -Force .git` returned no entries.
- Fallback environment enumeration for `GIT*` returned no abnormal variables.
- Cause: workspace `.git` directory is present but empty/incomplete. Not fixed because destructive/speculative Git repair is forbidden.

## 2026-07-17 - M1 Foundation

Status: Done for foundation review.

### Implemented

- Laravel 12 scaffold merged into `apps/dosen-farmasi`.
- Composer dependencies installed; Filament `v5.6.5` installed under semver constraint `^5.6`.
- Tailwind/Vite frontend dependencies installed and production build generated.
- Core DB read-only connection `core_mysql` configured.
- `AppUser` local auth model created without password/hash fields.
- Core bridge auth implemented via `CoreBridgeAuthService`.
- Role model constrained to `admin` and `dosen`.
- Middleware `dosen.role` added.
- Foundation migrations added for local users, lecturer snapshots, portfolio, documents, inbox, calendar, integration, audit, settings, and notifications.
- Idempotent category seeder added.
- Admin Filament panel and resource foundations added.
- Dosen/admin dashboard routes and views added.
- Private `shared_private` disk and `dosen:storage-check` diagnostic added.
- Policies for portfolio activity and document access added.
- Audit logger foundation added.
- Tests added for auth bridge, object authorization, boot/dashboard, and idempotent seeding.

### Validation

- `composer create-project laravel/laravel dosen-farmasi-scaffold "12.*"` - TIMED OUT after scaffold files were created; target merge completed manually and safely.
- `composer install` - first attempt TIMED OUT; second attempt PASS.
- `composer require filament/filament:5.6.5` - PASS, used cached package data after Packagist timeout warning.
- `composer validate` - PASS.
- `composer update --lock` - first attempt failed due Packagist/network/cache access; escalated retry timed out. Follow-up `composer validate` PASS, and lock remains usable.
- `php artisan about` - PASS; Laravel 12.64.0, PHP 8.2.12, Filament v5.6.5, timezone Asia/Jakarta.
- `php artisan migrate:fresh --seed --env=testing` - PASS.
- `php artisan dosen:storage-check` - PASS after fixing empty env fallback for `DOSEN_SHARED_PRIVATE_ROOT`.
- `vendor\bin\pint.bat` - PASS; fixed style.
- `php artisan test` - PASS: 11 tests, 24 assertions.
- `npm.cmd install` - PASS; `npm install` via PowerShell failed due execution policy, then `npm.cmd` succeeded.
- `npm.cmd run build` - PASS; Vite built production assets.

### Known Issues / Follow-ups

- Workspace Git metadata remains invalid; no commit/diff from Git is available.
- Real Core credential and app access for `dosen-farmasi` are not available; tests use fake Core database.
- Runtime upload UI and portfolio CRUD workflow are M2 scope.
- Internal event endpoint is documented but not implemented until integration kernel milestone.

### Next

Start M2: portfolio CRUD, document upload/download runtime, draft-submit-verify workflow, system-verified protection, verification history, and richer authorization tests.

## 2026-07-17 - M2 Portfolio/Documents Start

Status: Started; first usable slice passed validation.

### Implemented

- Portfolio manual draft index/create/store/show/delete routes and Blade views.
- Portfolio submit action from `DRAFT`/`REVISION_REQUIRED` to `SUBMITTED`.
- Ownership policy enforcement for portfolio show/update/delete.
- Document index/create/store routes and Blade views.
- Private document upload to `shared_private` with UUID server filename.
- Document checksum, MIME, extension, size, original filename, uploader, and owner metadata persistence.
- Document extension allow-list from `config/dosen_farmasi.php`.
- Dedicated `sessions` table migration restored after replacing Laravel's default users migration.
- Feature tests for portfolio draft submit and document upload/rejection.

### Validation

- `vendor\bin\pint.bat` - PASS.
- `php artisan route:list --except-vendor` - PASS; 29 application routes.
- `php artisan migrate:fresh --seed --env=testing` - PASS.
- `php artisan dosen:storage-check` - PASS.
- `composer validate` - PASS.
- `php artisan test` - PASS: 14 tests, 37 assertions.
- `npm.cmd run build` - PASS.
- `php artisan key:generate --force` - PASS for local `.env`; generated value not logged in docs.
- Local `php artisan serve --host=127.0.0.1 --port=8012` - running; `GET /login` returns HTTP 200.

### Known Issues / Follow-ups

- M2 verification workflow is not complete: verify, revision, reject, verification history, issue report, participants, and tags remain open.
- System-verified protection rules are not fully exercised yet.
- Real Core DB credentials and real `dosen-farmasi` app access records still need environment setup outside this app.

## 2026-07-17 - M2 Completion and M3 Foundation

Status: M2 complete for local application scope; M3 foundation started.

### Implemented

- Centralized portfolio transition service with allowed matrix:
  - `DRAFT -> SUBMITTED`
  - `REVISION_REQUIRED -> SUBMITTED`
  - `SUBMITTED -> ADMIN_VERIFIED|REVISION_REQUIRED|REJECTED`
  - `ADMIN_VERIFIED -> ARCHIVED`
  - `SYSTEM_VERIFIED -> CANCELLED`
- `REJECTED` is locked in MVP; dosen creates a new activity instead of reopening rejected records.
- Verification history table/model and immutable policy.
- Revision/rejection reasons, notifications, and audit logs.
- `SYSTEM_VERIFIED` official-field protection with personal notes/visibility allowed.
- Issue report workflow for official/system-verified data.
- Portfolio participants and tag attachment.
- Document metadata expansion, MIME/extension validation, private upload, document version foundation, and file cleanup on transaction failure.
- Soft delete metadata for document deletion; physical purge deferred.
- Data-driven dosen/admin dashboards.
- Filament resources for verification histories, issue reports, participants, tags, documents, and document versions.
- Filament portfolio activity actions: view, verify, request revision, reject, and archive.
- M3 foundation: inbox list/read state, agenda list, overlap warning, database notification center.

### Validation

- `vendor\bin\pint.bat` - PASS.
- `composer validate` - PASS.
- `php artisan optimize:clear` - PASS.
- `php artisan migrate:fresh --seed --env=testing` - PASS.
- `php artisan migrate --seed` - PASS when run standalone against local SQLite.
- `php artisan test` - PASS: 30 tests, 96 assertions.
- `php artisan route:list --except-vendor` - PASS: 50 application routes.
- `php artisan dosen:storage-check` - PASS.
- `npm.cmd run build` - PASS.
- Browser smoke on `http://127.0.0.1:8012/login` - PASS; title/H1 correct and console error list empty.

### Browser Notes

- Protected browser flows were not manually executed because real Core credentials and real Core app access records are not available in this workspace.
- Protected dashboard/portfolio/document/inbox/agenda flows are covered by feature tests using local/fake users.

### Known Issues / Follow-ups

- M3 admin-created inbox/calendar UI is not built yet; current M3 data can be tested via local fixtures.
- Integration event API and TU/TA/KP/Lab handlers are still M4/M5 scope.
- Browser end-to-end login should be repeated after Core `dosen-farmasi` app access is configured.

## 2026-07-17 - Dual Role Login Selection

Status: Done.

### Implemented

- Core bridge auth now resolves all valid `dosen-farmasi` roles for a Core user, not only the first role.
- Users with both `dosen` and `admin` access are redirected to `/pilih-role` after login.
- Dashboard routes redirect back to role selection until a role is chosen.
- Selected role updates the local `app_users.role` for the current session/user.
- Added role selection Blade page with `Dosen` and `Admin` options.

### Validation

- `vendor\bin\pint.bat` - PASS.
- `php artisan test --filter=CoreBridgeAuthTest` - PASS: 5 tests, 26 assertions.
- `php artisan test` - PASS: 31 tests, 108 assertions.
- `php artisan route:list --except-vendor` - PASS: 52 application routes.
- `npm.cmd run build` - PASS.

### Security Note

- Real user password was not stored in code, tests, docs, logs, or shell commands.

## 2026-07-17 - M3 Completion and M4 Integration Kernel

Status: M3 complete for local workflow scope; M4 push-event kernel implemented and validated locally.

### Implemented

- Hardened document upload with extension allowlist, server MIME check, magic-byte/signature validation, Office zip structure validation, SHA-256 checksum, and transaction cleanup.
- Inbox workflow service for single/multiple recipients with grouped inbox items and per-recipient state.
- Inbox transitions for read, accept, decline, complete, archive, and cancelled history; only invitations can be accepted/declined.
- Agenda workflow service with grouped attendee rows, overlap warning support, meeting URL safety validation, and cancelled/completed history.
- Notification center bulk mark-read action.
- Integration client token service with hashed bearer token, one-time plaintext token on rotation, revoke, active/expired checks, and ability checks.
- Internal API:
  - `GET /api/internal/v1/health`
  - `POST /api/internal/v1/events`
- Canonical event ingestion with envelope validation, supported version check, payload size limit, source-app spoof protection, known event registry, idempotent duplicate handling, and payload-hash conflict rejection.
- TU handlers:
  - `tu.letter.assigned`
  - `tu.letter.published`
  - `tu.letter.cancelled`
- TA handlers:
  - `ta.exam.scheduled`
  - `ta.exam.rescheduled`
  - `ta.exam.completed`
  - `ta.exam.cancelled`
- Ordering/stale revision protection for TA agenda lifecycle.
- `SYSTEM_VERIFIED` portfolio creation/update from completed TA exam events.
- Integration failure capture with safe error fields, retry time, `integration_failures`, and admin retry/ignore routes.
- Filament integration monitoring/actions for clients and events: activate/deactivate, rotate/revoke token, retry failed event, ignore failed/queued event.
- Pull adapter interface and sync cursor table foundation for future source-app polling.

### Validation

- `vendor\bin\pint.bat` - PASS.
- `php artisan migrate:fresh --seed --env=testing` - PASS.
- `php artisan test --filter=Document` - PASS: 9 tests, 30 assertions.
- `php artisan test --filter=InboxAgendaM3FoundationTest` - PASS: 5 tests, 27 assertions.
- `php artisan test --filter=IntegrationKernelM4Test` - PASS: 5 tests, 39 assertions.
- `php artisan test` - PASS: 41 tests, 172 assertions.
- `php artisan route:list` - PASS: 74 routes, including internal API and admin retry/ignore routes.
- `npm.cmd run build` - PASS; Vite production assets generated.

### Browser Notes

- Browser protected flows were not repeated in this pass.
- Real Core credential supplied in the conversation was not used in commands, tests, seeders, docs, or code.
- Core role-selection behavior remains covered by feature tests; full browser login should be run only after confirming real Core `dosen-farmasi` access in a safe local session.

### Known Issues / Follow-ups

- MySQL runtime migration was not executed in this pass; validation used Laravel testing database.
- Email channel is still represented by database notifications; queued mail templates/provider wiring remain later scope.
- M5 discovery only: KP/KP PSPA/Lab event handlers are not implemented yet.
- Workspace Git metadata remains invalid/incomplete, so final changed-file inventory is manual.

## 2026-07-17 - M5 KP, KP PSPA, Lab, and TA Assignment Integration

Status: Done for `dosen-farmasi` consumer/kernel scope.

### Discovery

- KP Farmasi inspected read-only:
  - `kp_assignments` stores KP placement/supervisor state.
  - `kp_exams` stores exam schedule, supervisor, examiner, mode/location/link, and status.
  - `kp_exam_examiners` supports additional examiners.
  - `lecturers.core_lecturer_id` is available for Core mapping.
- KP PSPA inspected read-only:
  - Actual folder is `apps/kppspa-farmasi`; consumer canonical app code is `kp-pspa`.
  - PKPA/PSPA domain includes rotation assignment, supervisors/preceptors, publication, assessment, and finalization.
- Lab inspected read-only:
  - Lab domain includes rooms, attendance sessions, equipment, safety documents/incidents, and material requests.
  - No formal lecturer practical schedule table was found; M5 uses explicit event/outbox contract.
- TA inspected read-only:
  - Existing M4 exam lifecycle remains.
  - M5 adds supervisor/examiner assignment handlers only.

### Implemented

- Added M5 config for source connections, outbox table, pull batch size, failure categories, source document disks, and integration mail toggle.
- Added read-only source DB connection placeholders: `kp_mysql`, `kp_pspa_mysql`, and `lab_mysql`.
- Extended integration failures with category, retryable flag, resolution metadata.
- Extended sync cursors with last source ID and last updated timestamp.
- Added `notification_preferences` table/model and AppUser relation.
- Added `LecturerIdentityResolver` with direct Core ID and fallback NIP/NIDN/email/lecturer number resolution.
- Added `SourceDocumentReferenceValidator` for safe source document references.
- Added `IntegrationProcessingException` for categorized retryable/non-retryable failures.
- Added queued notification mail channel guarded by config and per-dosen preferences.
- Added base `AcademicSourceEventHandler`.
- Added KP handlers:
  - `kp.supervisor.assigned`
  - `kp.supervisor.changed`
  - `kp.examiner.assigned`
  - `kp.exam.scheduled`
  - `kp.exam.rescheduled`
  - `kp.exam.completed`
  - `kp.exam.cancelled`
- Added KP PSPA handlers:
  - `kpspa.supervisor.assigned`
  - `kpspa.preceptor.assigned`
  - `kpspa.examiner.assigned`
  - `kpspa.activity.scheduled`
  - `kpspa.activity.completed`
  - `kpspa.activity.cancelled`
  - `kpspa.assessment.finalized`
- Added Lab handlers:
  - `lab.lecturer.assigned`
  - `lab.schedule.created`
  - `lab.schedule.rescheduled`
  - `lab.activity.completed`
  - `lab.activity.cancelled`
- Added TA assignment handlers:
  - `ta.supervisor.assigned`
  - `ta.supervisor.changed`
  - `ta.examiner.assigned`
  - `ta.examiner.changed`
- Added concrete pull adapters:
  - `KpFarmasiPullAdapter`
  - `KpPspaPullAdapter`
  - `LabFarmasiPullAdapter`
- Added `dosen:sync-integrations` with source selection, dry-run, from/to, and limit options.
- Added `dosen:integration-health` diagnostic.
- Added Filament resources for integration failures and sync cursors.
- Added M5 docs:
  - `docs/M5_SOURCE_DISCOVERY.md`
  - `docs/M5_EVENT_CATALOGUE.md`
  - `docs/M5_SOURCE_MAPPING.md`
  - `docs/M5_PRODUCER_PLAN.md`
  - `docs/M5_RUNBOOK.md`

### Validation

- `vendor\bin\pint.bat --dirty` - PASS.
- `vendor\bin\pint.bat` - PASS.
- `composer validate` - PASS.
- `php artisan optimize:clear` - PASS.
- `php artisan migrate:fresh --seed --env=testing` - PASS.
- `php artisan test --filter=IntegrationKernelM4Test` - PASS: 5 tests, 39 assertions.
- `php artisan test --filter=IntegrationM5SourceLifecycleTest` - PASS: 6 tests, 35 assertions.
- `php artisan test` - PASS: 49 tests, 220 assertions.
- `php artisan route:list --except-vendor` - PASS: 60 routes.
- `php artisan dosen:storage-check` - PASS.
- `php artisan dosen:integration-health` - PASS command execution; local DB/storage/queue OK, Core and source DB connections report failed because credentials/connections are not configured in local `.env`.
- `npm.cmd run build` - PASS.
- `php artisan migrate --seed --force` - PASS against current local SQLite environment; nothing to migrate.
- `curl.exe -s -I http://127.0.0.1:3002/login` - PASS: HTTP 200.

### MySQL and Browser Notes

- Current local `.env` uses `DB_CONNECTION=sqlite`; MySQL runtime migration was not executed in this pass.
- Source DB health for KP/KP PSPA/Lab reports failed until read-only source credentials are configured.
- Browser real-login with live Core was not posted in shell or docs to avoid exposing credentials. Login page on port 3002 is reachable; role-selection/auth behavior is covered by feature tests.

### Security Notes

- No real password, token, or secret was written into code, docs, tests, or command output.
- Source apps were inspected only; no application sibling was modified.
- Pull adapters are read-only against source DB outbox tables.
- Source document references reject absolute paths, traversal, Windows drives, unknown disk aliases, and invalid checksums.

### Known Issues / Follow-ups

- Producer event/outbox changes still need to be implemented in KP, KP PSPA, Lab, and TA after pilot approval.
- Lab schema needs a formal schedule/assignment producer or outbox event writer.
- Email integration remains opt-in and requires queue worker, mailer configuration, and dosen preferences.
- Workspace Git metadata remains empty/incomplete, so changed-file inventory is manual.

## 2026-07-17 - M6 KP Producer Pilot

Status: Technical integration complete for automated local tests; MySQL and real-login acceptance pending.

### Implemented

- Added safe token command `dosen:integration-client-token {app_code}`.
- Added `kp.supervisor.changed` consumer behavior that cancels old lecturer inbox and creates/updates new lecturer inbox.
- Added `kp.examiner.changed` handler registration.
- Added KP producer pilot in `apps/kp-farmasi`: outbox table, source revision columns, event factory, outbox service, HTTP delivery client, queued delivery job, delivery/health commands, scheduler, and management monitoring UI.

### Validation

- KP `php artisan test` - PASS: 247 tests, 1538 assertions.
- Dosen `php artisan test` - PASS: 50 tests, 224 assertions.
- KP focused `DosenFarmasiIntegrationOutboxTest` - PASS: 5 tests, 25 assertions.
- Dosen focused `IntegrationM5SourceLifecycleTest` - PASS: 7 tests, 39 assertions.
- KP and Dosen migration testing plus local SQLite `migrate:fresh --seed --force` - PASS.
- KP changed-file Pint test - PASS; full Pint blocked by an existing non-writable file outside changed set.
- Dosen Pint test - PASS.
- KP/Dosen build - PASS.
- KP route list - PASS: 255 routes.
- Dosen route list - PASS: 60 routes.
- KP health command - PASS command execution; integration disabled/unconfigured.
- Dosen health command - PASS command execution; Core/source DB unconfigured.

### Pending

- MySQL nonproduction migration and tests.
- Real browser login with Core.
- Live HTTP E2E with real token and running apps.

## 2026-07-17 - M6 Acceptance Closure Attempt

Status: Acceptance pending; M7 TA producer not started.

### Implemented

- Added `dosen:audit-kp-integration` read-only audit command.
- Added `kp:prune-integration-outbox` safe dry-run/default retention command with explicit orphan recovery.
- Added `apps/dosen-farmasi/docs/M6_ACCEPTANCE_REPORT.md`.
- Added `apps/kp-farmasi/docs/INTEGRATION_OPERATIONS.md`.

### Validation

- KP `php artisan test` - PASS: 249 tests, 1552 assertions.
- Dosen `php artisan test` - PASS: 51 tests, 229 assertions.
- KP focused `DosenFarmasiIntegrationOutboxTest` - PASS: 7 tests, 39 assertions.
- Dosen focused `IntegrationM5SourceLifecycleTest` - PASS: 8 tests, 44 assertions.
- Changed-file Pint for KP and Dosen acceptance files - PASS.
- KP full Pint - FAIL on pre-existing style issues outside changed acceptance files.
- KP/Dosen `composer validate`, `optimize:clear`, `route:list --except-vendor`, integration health commands, and `npm.cmd run build` - PASS command execution.
- KP route count: 255.
- Dosen route count: 60.
- `kp:prune-integration-outbox --show-rows` - PASS dry-run.
- `dosen:audit-kp-integration` - PASS command execution; KP source outbox unavailable because source DB is not configured.

### Acceptance Findings

- Active KP and Dosen `.env` files still use SQLite, not MySQL.
- KP integration is disabled and has no configured base URL/token in local runtime.
- Dosen Core/source DB health fails because credentials are not configured in local runtime.
- Core seeder contains `kp-farmasi` and `ta-farmasi`, but not `dosen-farmasi`; actual nonproduction Core DB access still needs operator verification.
- MySQL acceptance, live HTTP E2E, failure recovery, concurrent delivery, and real browser QA were not executed.

### Gate Decision

- M6 remains `technical integration complete; acceptance pending`.
- M7 TA producer work is blocked until M6 MySQL/live HTTP/Core/browser acceptance passes.

## 2026-07-17 - M6 Acceptance Execution

Status: MySQL and live KP -> Dosen main path passed; final acceptance pending; M7 TA producer not started.

### Environment

- `apps/dosen-farmasi` and `apps/kp-farmasi` were switched to isolated nonproduction MySQL databases for acceptance.
- KP integration client was activated locally with base URL and token in `.env`; token plaintext was not written to docs, tests, or output.
- Dosen identity snapshots were seeded for the acceptance lecturers used by KP events.

### Validation

- Dosen `optimize:clear`, MySQL driver check, `migrate:fresh --seed --force`, `migrate:status`, `dosen:storage-check`, route list, tests, and build: PASS.
- Dosen tests: 51 tests, 229 assertions.
- KP `optimize:clear`, MySQL driver check, `migrate:fresh --seed --force`, `migrate:status`, route list, focused outbox suite, full serial MySQL tests, and build: PASS.
- KP focused outbox suite: 7 tests, 39 assertions.
- KP full serial MySQL suite: 249 tests, 1553 assertions.
- `php artisan dosen:audit-kp-integration --show-rows`: PASS with all findings 0 after fixing audit portfolio lookup to follow actual handler `related_records`.
- `php artisan kp:prune-integration-outbox --days=90 --show-rows`: PASS dry-run, no changes applied.

### Live HTTP Main Path

- KP business services produced events for supervisor assigned, examiner assigned, scheduled, rescheduled, completed, and cancelled flows.
- KP outbox rows observed: 20.
- KP outbox `SENT`: 20.
- HTTP responses from Dosen: 20 x `202`.
- Dosen integration events `PROCESSED`: 20.
- Dosen domain side effects verified: inbox, calendar, notifications, and 3 `SYSTEM_VERIFIED` portfolio activities.

### Remaining Gate Items

- Browser QA for real Core login, role selection, admin/dosen dashboards, and responsive views.
- Live consumer-down retry drill.
- Live invalid-token, timeout, `429`, and two-worker concurrency drills.
- Operator confirmation of real nonproduction Core `dosen-farmasi` app access.

### Gate Decision

- M6 remains `technical integration complete; acceptance pending`.
- M7 TA producer work remains blocked until the remaining M6 gate items pass.

## 2026-07-17 - M6 Failure Drills And Browser QA

Status: M6 technical integration complete; strict acceptance still pending because `/admin/integration-clients` was blocked by the in-app browser URL policy. M7 TA producer was not started.

### Completed

- Consumer-down live drill: PASS. Business committed, outbox stayed retryable, retry after Dosen restart delivered the event, audit all 0.
- Invalid-token live drill: PASS. Unauthorized delivery became `FAILED` with `AUTHORIZATION_FAILED`, no token leaked, explicit retry after valid runtime config delivered the event.
- Timeout/connection failure drill: PASS. Event stayed retryable and recovered to `SENT`.
- HTTP `5xx` drill: PASS. HTTP `500` was classified retryable and recovered to `SENT`.
- HTTP `429` classification: PASS in automated KP outbox test coverage.
- Two-worker concurrency: PASS. One effective delivery, attempt count 1, no stuck `PROCESSING`.
- Orphan recovery: PASS. Dry-run detected stale lock; explicit recovery returned the row to `PENDING`; retry delivered.
- Dosen Core login and role selection: PASS for Dosen/Admin roles.
- Dosen browser pages: dashboard, inbox, agenda, notifications, portfolio PASS.
- Dosen Admin browser pages: dashboard, portfolio activity, integration events, integration failures PASS after Filament assets were published.
- KP Admin browser pages: dashboard, outbox list, outbox detail PASS after outbox views were aligned to `layouts.app`.
- Responsive sweep: PASS, 27 checks, no console errors, no horizontal overflow, no 500 pages.

### Regression

- Dosen MySQL `migrate:fresh --seed --force`: PASS.
- Dosen tests: PASS, 51 tests, 229 assertions.
- KP MySQL `migrate:fresh --seed --force`: PASS.
- KP tests: PASS with local `APP_URL=http://localhost` override, 249 tests, 1555 assertions.
- KP focused outbox suite: PASS, 7 tests, 41 assertions.
- Dosen/KP route lists, builds, health commands, retention dry-run, final cross-app audit, and changed-file Pint: PASS.

### Gate Decision

- M6 remains `technical integration complete / acceptance pending`.
- Remaining blocker: browser verification of `/admin/integration-clients` in an environment not blocked by the in-app browser URL policy.
- KP PSPA/Lab health warnings are outside M6 KP producer scope but remain operational follow-up items.

## 2026-07-18 - M7 TA Producer Pilot Start

Status: M7 technical implementation started; acceptance pending.

### Implemented

- Added TA producer outbox in `apps/ta-farmasi` for `dosen-farmasi`.
- Added `integration_revision` on `ta_supervisor_assignments` and `ta_events`.
- Added canonical TA event factory for supervisor, examiner, schedule, reschedule, completed, and cancelled lifecycle events.
- Integrated outbox creation into TA supervisor assignment, sempro scheduling/cancellation/completion, and final defense scheduling/cancellation/completion services.
- Added HTTP delivery client, queued delivery job, delivery command, health command, audit command, prune/recovery command, scheduler, and Filament outbox monitoring page.
- Added TA documentation:
  - `apps/ta-farmasi/docs/DOSEN_FARMASI_INTEGRATION_DISCOVERY.md`
  - `apps/ta-farmasi/docs/DOSEN_FARMASI_INTEGRATION.md`
  - `apps/ta-farmasi/docs/OUTBOX_RUNBOOK.md`
  - `apps/ta-farmasi/docs/INTEGRATION_OPERATIONS.md`
- Added M7 Dosen-side docs:
  - `docs/M7_TA_PILOT.md`
  - `docs/M7_E2E_TEST_REPORT.md`

### Validation

- TA `php artisan migrate:fresh --seed --env=testing`: PASS.
- TA `php artisan test`: PASS, 517 tests, 2735 assertions.
- TA `php artisan test --filter=DosenFarmasiIntegrationOutboxTest`: PASS, 7 tests, 30 assertions.
- TA `php artisan route:list --except-vendor`: PASS, 212 routes.
- TA `php artisan ta:integration-health`: PASS command execution; local integration disabled/unconfigured.
- TA `php artisan ta:audit-dosen-integration --show-rows`: PASS, all findings 0 on clean local state.
- TA `php artisan ta:prune-integration-outbox --show-rows`: PASS dry-run.
- TA changed-file Pint: PASS.
- TA `npm.cmd run build`: PASS.
- Dosen `php artisan test --filter=IntegrationKernelM4Test`: PASS, 5 tests, 39 assertions.
- Dosen `php artisan test --filter=IntegrationM5SourceLifecycleTest`: PASS, 8 tests, 44 assertions.
- Dosen `php artisan route:list --except-vendor`: PASS, 60 routes.
- Dosen `npm.cmd run build`: PASS.

### Pending

- Nonproduction MySQL migration and tests for M7.
- Live TA -> Dosen HTTP delivery with real integration client runtime config.
- Failure drills for consumer down, invalid token, timeout, HTTP `429`, HTTP `5xx`, concurrency, and orphan recovery.
- Browser QA for TA outbox monitoring and Dosen inbox/agenda/portfolio side effects.

## 2026-07-18 - M7 Acceptance Validation

Status: M7 technical integration complete; strict acceptance pending.

### Completed

- Both apps confirmed using MySQL driver.
- TA MySQL `migrate:fresh --seed --force`: PASS.
- Dosen MySQL `migrate:fresh --seed --force`: PASS.
- TA full test suite: PASS, 517 tests, 2735 assertions.
- Dosen full test suite: PASS, 55 tests, 239 assertions.
- TA focused M7 test: PASS, 7 tests, 30 assertions.
- Dosen integration tests: PASS.
- TA route list: PASS, 212 routes.
- Dosen route list: PASS, 60 routes.
- TA and Dosen builds: PASS.
- Integration client `ta-farmasi` verified active with abilities `events:push` and `integration:health`; token hash length 64 and no plaintext token column.
- Live HTTP main path from TA business services: PASS.
- Lifecycle coverage passed for supervisor assigned/changed, examiner assigned/changed, scheduled, rescheduled, completed, and cancelled.
- Failure drills passed for consumer down, invalid token, connection failure, HTTP `500`, HTTP `429`, two-worker concurrency, and orphan recovery.
- Queue worker processed database notifications; final pending jobs 0 and failed jobs 0.
- Cross-app audit command `dosen:audit-ta-integration` added and passed with all findings 0.
- Added `docs/M7_ACCEPTANCE_REPORT.md`.

### Final Observed Live State

- TA outbox: `SENT` 43; `PENDING`, `PROCESSING`, and `FAILED` all 0.
- Dosen TA integration events: `PROCESSED` 43; `FAILED`, `DUPLICATE`, and `IGNORED` all 0.
- Dosen TA objects: inbox 25, agenda 9, `SYSTEM_VERIFIED` portfolio 4, database notifications 73.

### Still Pending

- Browser functional QA in Chrome/Edge for admin/dosen/TA monitoring flows.
- Official TA document references for surat tugas, undangan, and berita acara were not available in producer payload; current producer sends empty `document_references`.
- Premium UI/UX remains not complete and is an M8 gate, not an M7 completion item.

### Gate Decision

M7 is not marked complete. Strict acceptance remains pending until browser functional QA and official document reference acceptance are closed.

## 2026-07-18 - M8 Premium UI/UX Slice

Status: Implemented and browser-checked for core auth, role selection, dosen dashboard, and admin control-room entry.

### Implemented

- Rebuilt the shared Blade app shell with a premium desktop sidebar, mobile navigation, profile block, and admin-only control-room access.
- Rebuilt `/login` with a premium authentication layout, institutional logo, stronger form treatment, and password visibility toggle.
- Rebuilt `/pilih-role` with premium role cards and clear Indonesian role descriptions.
- Rebuilt `/dosen/dashboard` as a usable dosen workspace with priority stats, action items, recent activity, agenda, and quick actions.
- Added Filament `Ruang Kontrol Admin` dashboard at `/admin` with operational stats.
- Changed admin login and admin role selection to redirect directly to `/admin`.
- Changed `/admin/dashboard` into a compatibility redirect to `/admin`.
- Kept all admin resources in the existing Filament panel; no duplicate resource or panel was created.
- Added M8 docs: `M8_DESIGN_SYSTEM.md`, `M8_INFORMATION_ARCHITECTURE.md`, and `M8_UI_UX_AUDIT.md`.

### Validation

- `php -l` on changed PHP files: PASS.
- Changed-file Pint: PASS.
- `php artisan test --filter=FoundationBootTest`: PASS, 9 tests, 21 assertions.
- `php artisan test --filter=CoreBridgeAuthTest`: PASS, 7 tests, 39 assertions.
- `php artisan test`: PASS, 56 tests, 243 assertions.
- `php artisan route:list --except-vendor`: PASS, 61 routes.
- `npm.cmd run build`: PASS.
- Browser QA:
  - `/login` desktop: PASS.
  - `/pilih-role` desktop: PASS.
  - `/dosen/dashboard` desktop and mobile: PASS.
  - `/admin` desktop: PASS.
  - `/admin/integration-clients`: PASS.
  - Dosen access denied to admin integration clients: PASS.
  - Console errors: NONE.
  - Horizontal overflow: NONE observed.

### Screenshot Evidence

- `storage/app/m8-ui-qa/login-desktop.png`
- `storage/app/m8-ui-qa/role-desktop.png`
- `storage/app/m8-ui-qa/dosen-dashboard-desktop.png`
- `storage/app/m8-ui-qa/dosen-dashboard-mobile.png`
- `storage/app/m8-ui-qa/admin-control-room-desktop.png`

### Pending

- Secondary CRUD page premium redesign remains future M8 continuation scope.
- M7 official document reference acceptance remains pending; M7 is not marked complete.

## 2026-07-18 - M8 Tridharma And Academic Profile Slice

Status: Implemented and validated as a substantial M8 continuation. M8 remains not final-complete.

### Implemented

- Added first-class Tridharma routes and UI:
  - `/tridharma`
  - `/tridharma/pendidikan`
  - `/tridharma/penelitian`
  - `/tridharma/pengabdian`
- Added Tridharma menu item in the dosen shell.
- Added category-backed Tridharma summaries using existing portfolio categories.
- Added Tridharma filters for year, semester, status, source, and subcategory.
- Added Tridharma sections for needs-completion, recent activity, system source, manual input, and yearly summary.
- Added academic profile route `/profil` and shell menu item `Profil Akademik`.
- Added profile header, identity cards, completeness checklist, visibility settings, education timeline, career/position section, expertise chips, certifications, external identifiers, and public profile entry.
- Added `/profil-publik/{lecturerCoreId}` with privacy filtering.
- Added academic profile migration, models, policy, and services.
- Added education create/update/delete routes and admin verification route.
- Added Filament resources under `Profil Akademik` for education, positions, employment, expertise, certifications, and external identifiers.
- Added `docs/M8_TRIDHARMA_PROFILE_REPORT.md`.

### Validation

- `php artisan migrate --force`: PASS.
- Changed-file Pint: PASS.
- `php artisan test --filter=M8TridharmaProfileTest`: PASS, 8 tests, 41 assertions.
- `php artisan test`: PASS, 64 tests, 284 assertions.
- `php artisan route:list --except-vendor`: PASS, 78 routes.
- `npm.cmd run build`: PASS.
- Browser QA:
  - `/tridharma`: PASS.
  - `/tridharma/pendidikan`: PASS.
  - `/tridharma/penelitian`: PASS.
  - `/tridharma/pengabdian`: PASS.
  - `/profil`: PASS.
  - Admin academic resources: PASS.
  - 768 x 1024 and 390 x 844: PASS.
  - Console errors: NONE.
  - Horizontal overflow: NONE observed.

### Still Pending For Final M8

- Premium redesign of secondary CRUD pages beyond `/tridharma` and `/profil`.
- Full create/edit workflows for all non-education academic profile entities.
- Verification/revision history expansion across all academic profile entities.
- M7 official document-reference acceptance remains pending.
- No commit or push has been performed.

## 2026-07-18 - M8 Premium Visual Redesign Continuation

Status: Premium foundation redesigned and automated validation passed. M8 final premium gate remains pending.

### Implemented

- Added centralized design tokens and component classes in `resources/css/app.css`.
- Added reusable UI components under `resources/views/components/ui`.
- Added reusable academic components under `resources/views/components/academic`.
- Rebuilt the shared application shell with:
  - grouped sticky desktop sidebar;
  - lecturer mini-profile;
  - active state indicator;
  - inbox/agenda badges;
  - sticky topbar with breadcrumb, academic period, search, agenda, notification, and profile entry;
  - five-item mobile bottom navigation.
- Rebuilt `/dosen/dashboard` as an academic workspace with progress ring, focus panel, quick routes, recent activity, agenda, and contribution bars.
- Rebuilt `/tridharma` and domain pages as a command center with segmented navigation, compact filters, filter chips, domain cards, empty states, attention list, automatic sources, timeline, and year summary.
- Rebuilt `/profil` as an academic identity page with professional header, completeness ring, education milestones/timeline, career progression, expertise fingerprint, scientific identities, certification section, visibility rail, education form, and public profile entry.
- Added actual recent academic activities to `ProfileController`.
- Added custom Filament admin theme via `resources/css/filament/admin/theme.css`.
- Registered Filament theme with `AdminPanelProvider::viteTheme()`.
- Renamed admin terminology for nontechnical Indonesian users:
  - Aplikasi Terhubung.
  - Kegagalan Sinkronisasi.
  - Posisi Sinkronisasi.
  - Laporan Kesalahan Data.
- Added M8 feature coverage for premium shell navigation, empty/populated states, profile sections, admin terminology, and dosen admin denial.

### Validation

- `npm.cmd run build` - PASS.
- Changed-file Pint - PASS.
- `php artisan test --filter=M8TridharmaProfileTest` - PASS: 12 tests, 70 assertions.
- `php artisan test` - PASS: 68 tests, 313 assertions.
- `php artisan route:list --except-vendor` - PASS: 78 routes.
- Browser QA on `127.0.0.1:3002`:
  - `/pilih-role` rendered role selection: PASS.
  - Dosen role opened `/dosen/dashboard`: PASS.
  - Desktop 1440 x 900 checks for dashboard, Tridharma, and profile: PASS, no overflow, console errors 0.
  - Mobile 390 x 844 checks for dashboard, Tridharma, and profile: PASS, bottom navigation visible, no overflow after profile stat-wrap fix, console errors 0.
- Screenshots saved under `storage/app/m8-premium-qa`.

### Pending

- Secondary CRUD redesign for portfolio, documents, inbox, agenda, and notifications.
- Populated demo data for final visual baseline.
- Full final M8 QA ring.
- No commit or push has been performed.

## 2026-07-18 - M8 Visual Refinement And Secondary Page Redesign

Status: Implemented and browser-checked with populated local demo data. M8 UI/UX is substantially ready for human visual review.

### Implemented

- Refined product language from `Command Center` to `Ruang Akademik`.
- Reduced visual weight in shell, headings, buttons, badges, and progress treatment.
- Replaced sidebar/mobile letter placeholders with SVG icons.
- Simplified topbar to icon controls for agenda, notifications, and profile.
- Added local-only populated demo data command:
  - `php artisan dosen:seed-ui-demo --clear`
- Redesigned secondary dosen pages:
  - `/dosen/portofolio`
  - `/dosen/portofolio/create`
  - `/dosen/portofolio/{activity}`
  - `/dosen/portofolio/{activity}/edit`
  - `/dosen/dokumen`
  - `/dosen/dokumen/create`
  - `/dosen/inbox`
  - `/dosen/agenda`
  - `/dosen/notifikasi`
- Refined dashboard zero-state and populated progress treatment.
- Refined Tridharma domain header copy and replaced repeated large ring with compact progress.
- Fixed profile mobile horizontal overflow from nested grid/min-content behavior.

### Validation

- `vendor\bin\pint.bat tests\Feature\M8TridharmaProfileTest.php app\Console\Commands\SeedUiDemoCommand.php bootstrap\app.php` - PASS.
- `php artisan dosen:seed-ui-demo --clear` - PASS.
- `php artisan test --filter=M8TridharmaProfileTest` - PASS: 13 tests, 75 assertions.
- `php artisan test --filter=Integration` - PASS: 15 tests, 88 assertions.
- `php artisan route:list --except-vendor` - PASS: 78 routes.
- `npm.cmd run build` - PASS.
- Browser QA on `127.0.0.1:3002`:
  - Desktop 1440 x 900: dashboard, portfolio, documents, inbox, agenda, notifications, Tridharma Pengabdian, profile - PASS.
  - Mobile 390 x 844: dashboard, portfolio, documents, inbox, agenda, notifications, Tridharma Pengabdian, profile - PASS.
  - Console errors: NONE.
  - Horizontal overflow: NONE.
  - Login redirects: NONE.

### Screenshot Evidence

- `storage/app/m8-premium-final-qa/qa-results.json`
- `storage/app/m8-premium-final-qa/preview-desktop-dashboard.png`
- `storage/app/m8-premium-final-qa/preview-mobile-portfolio.png`
- Full page desktop/mobile screenshots for all checked pages are in `storage/app/m8-premium-final-qa`.

### Notes

- Demo records are local-only and marked with `source_app = m8-ui-demo`.
- Integration contracts for KP, TA, KP PSPA, and Lab were not changed.
- No M9 or new integration milestone was started.
- No commit or push has been performed.

## 2026-07-18 - Release Candidate Gate Status

Status: RC technical QA passed. Premium visual approval remains pending project manager review.

### Current Gate

- RC technical QA: passed.
- Premium visual approval: pending project manager review.
- External KP PSPA/Lab health: not configured in current environment.
- TA source outbox audit: unavailable in current environment.
- Git repository: not initialized.
- Commit: pending.
- Push: pending.
- Tag: none.
- Release: none.
- Deployment: not started.

### Git Handling

- Root workspace and application folder are not valid Git repositories.
- Do not repair or initialize Git at the workspace root.
- A standalone repository may be initialized only in `apps/dosen-farmasi` after final screenshot approval.
- Remote `https://github.com/farhamzah/dosen-farmasi.git` was checked with `git ls-remote` and returned no refs.
- No commit or push has been performed.
