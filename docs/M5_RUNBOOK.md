# M5 Runbook

## Commands

Health:

```bash
php artisan dosen:integration-health
```

Dry-run satu source:

```bash
php artisan dosen:sync-integrations kp-farmasi --dry-run --from=2026-01-01 --to=2026-12-31 --limit=100
```

Backfill nyata:

```bash
php artisan dosen:sync-integrations kp-farmasi --from=2026-01-01 --limit=100
```

Semua source terdaftar:

```bash
php artisan dosen:sync-integrations --limit=50
```

## Source Configuration

Set koneksi read-only sesuai source:

```env
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

LAB_DB_HOST=127.0.0.1
LAB_DB_PORT=3306
LAB_DB_DATABASE=lab_farmasi
LAB_DB_USERNAME=
LAB_DB_PASSWORD=
```

Credential harus read-only.

## Monitoring

Filament admin menyediakan monitoring:

- Integration Clients
- Integration Events
- Integration Failures
- Integration Sync Cursors

Gunakan `Integration Failures` untuk melihat kategori, retryable flag, resolution note, dan safe context.

## Email Preferences

Email tidak dikirim default. Syarat email:

- `DOSEN_INTEGRATION_MAIL_ENABLED=true`;
- `notification_preferences.email_enabled=true`;
- kategori terkait diaktifkan pada preferensi dosen;
- mailer dan queue worker siap.

Database notification tetap berjalan sebagai channel utama.

## Recovery

- Event duplikat dengan payload sama aman diulang.
- Event dengan payload berbeda untuk `event_id` sama ditolak.
- Event stale dengan `source_revision` lebih lama tidak mengubah aggregate.
- Source DB down dilaporkan sebagai `DATABASE_SOURCE_UNAVAILABLE` dan cursor tidak maju.
- Failure non-retryable perlu koreksi payload/source mapping lalu retry manual bila aman.

## KP Producer Pilot

1. Jalankan `php artisan dosen:integration-client-token kp-farmasi`.
2. Simpan plaintext token sekali ke environment KP sebagai `DOSEN_FARMASI_INTEGRATION_TOKEN`.
3. Set `DOSEN_FARMASI_BASE_URL`.
4. Jalankan `php artisan kp:integration-health`.
5. Jalankan `php artisan kp:deliver-integration-outbox --dry-run`.
6. Aktifkan `DOSEN_FARMASI_INTEGRATION_ENABLED=true`.
7. Kirim batch kecil dan pantau Filament Dosen serta halaman outbox KP.
