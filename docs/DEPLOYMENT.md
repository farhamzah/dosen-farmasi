# Deployment

## Environment

Required placeholders:

```env
APP_NAME="Dosen Farmasi"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000
APP_TIMEZONE=Asia/Jakarta

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=dosen_farmasi
DB_USERNAME=
DB_PASSWORD=

CORE_DB_CONNECTION=core_mysql
CORE_DB_HOST=127.0.0.1
CORE_DB_PORT=3306
CORE_DB_DATABASE=core_farmasi_ubp
CORE_DB_USERNAME=
CORE_DB_PASSWORD=

DOSEN_CORE_HTTP_ENABLED=false
DOSEN_CORE_READ_MODE=disabled
DOSEN_CORE_BASE_URL=
DOSEN_CORE_APP_CODE=dosen-farmasi
DOSEN_CORE_CLIENT_ID=
DOSEN_CORE_CLIENT_SECRET=

DOSEN_PRIVATE_DISK=dosen_private
DOSEN_DOCUMENT_MAX_KB=10240
DOSEN_ALLOWED_SOURCE_DOCUMENT_DISKS=shared_private
DOSEN_INTEGRATION_MAIL_ENABLED=false
QUEUE_CONNECTION=database
MAIL_MAILER=log

KP_DB_HOST=127.0.0.1
KP_DB_PORT=3306
KP_DB_DATABASE=kp_farmasi
KP_DB_USERNAME=
KP_DB_PASSWORD=

KP_PSPA_DB_HOST=127.0.0.1
KP_PSPA_DB_PORT=3306
KP_PSPA_DB_DATABASE=kppspa_farmasi
KP_PSPA_DB_USERNAME=
KP_PSPA_DB_PASSWORD=

TA_DB_HOST=127.0.0.1
TA_DB_PORT=3306
TA_DB_DATABASE=ta_farmasi
TA_DB_USERNAME=
TA_DB_PASSWORD=

LAB_DB_HOST=127.0.0.1
LAB_DB_PORT=3306
LAB_DB_DATABASE=lab_farmasi
LAB_DB_USERNAME=
LAB_DB_PASSWORD=
```

Real secrets must live in environment/secret manager, not repository docs.

## Services

- Web server for Laravel app.
- Queue worker for mail, notifications, integration processing, document processing, and report jobs.
- Scheduler cron for pull sync, retry, cleanup, and digest features when implemented.
- Private shared filesystem mount for document storage.

## Commands

```bash
composer install
php artisan key:generate
php artisan migrate --seed
npm install
npm run build
php artisan test
```

Diagnostics:

```bash
php artisan dosen:storage-check
php artisan about
php artisan route:list --except-vendor
php artisan dosen:integration-health
php artisan dosen:audit-kp-integration
php artisan dosen:audit-ta-integration --source-database=<ta_nonproduction_database>
php artisan dosen:integration-client-token kp-farmasi
php artisan dosen:integration-client-token ta-farmasi
php artisan dosen:sync-integrations kp-farmasi --dry-run --limit=50
```

## Operations

- Run queue worker with retry/backoff configured by jobs.
- Run scheduler every minute if scheduled jobs are enabled.
- Keep Core/source DB credentials read-only.
- Ensure private disk path is not web-public and has backup/restore procedure.
- Rotate integration client tokens through admin flow or command once available.
- Run source backfill with `--dry-run` first, then small `--limit` batches.
- For KP pilot, deploy KP producer with integration disabled first, configure token/base URL, run `kp:integration-health`, then enable delivery.

## Known Deployment Notes

- `DOSEN_ADMIN_CORE_USER_IDS` is the M1 bootstrap mechanism for admin identity based on Core user IDs.
- `CORE_DB_USERNAME` should be a read-only database user.
- Do not publish or symlink `shared_private`.
- Local `.env` in this workspace contains only placeholders and an empty `APP_KEY`; real environments must generate a key and use secret management.
- Database notifications are the MVP notification channel; mail may stay `log` until SMTP/queue operations are configured.
- Integration email remains off until `DOSEN_INTEGRATION_MAIL_ENABLED=true`, dosen preferences are enabled, and queue workers are running.
- Allowed upload extensions are configured in `config/dosen_farmasi.php`: `pdf`, `docx`, `xlsx`, `pptx`, `jpg`, `jpeg`, `png`.
- Document metadata is soft-deleted; permanent purge/retention automation is not a user-facing action yet.
- Source app DB users for KP/KP PSPA/Lab must be read-only and should expose only the outbox table needed by pull adapters.
- KP push pilot uses KP's local outbox and does not require `dosen-farmasi` to write to KP DB.
- `dosen:audit-kp-integration` is read-only and should be run after live pilot events to compare KP outbox with Dosen consumer objects.
- TA push pilot uses TA's local outbox and does not require `dosen-farmasi` to write to TA DB.
- `dosen:audit-ta-integration` is read-only and should be run after live TA pilot events to compare TA outbox with Dosen consumer objects.
- M6 acceptance must not be marked production-ready until MySQL, live HTTP, failure recovery, concurrent delivery, Core real-login, and browser QA pass in nonproduction.
- For Filament admin pages, publish/update Filament assets during deployment with `php artisan filament:assets` when assets are missing or stale.
- If `migrate:fresh` is used in a local acceptance environment, rotate/reconfigure the KP integration client token afterward before repeating live HTTP delivery.
- As of 2026-07-17, M6 failure drills and broad browser QA passed, but strict acceptance remains pending until `/admin/integration-clients` is verified in a browser environment that does not block the route.
