# Dosen Farmasi

`dosen-farmasi` adalah aplikasi portofolio dosen Farmasi UBP. Aplikasi ini akan menjadi pusat dashboard dosen, portofolio Tridharma, dokumen private, inbox, agenda, notifikasi, laporan, dan pemrosesan integrasi dari aplikasi internal.

Status saat ini: **M6 KP producer pilot selesai untuk automated local tests**. Workflow portofolio, dokumen private, dashboard, inbox, agenda, notifikasi database, kernel event internal, TU/TA lifecycle, integrasi KP/KP PSPA/Lab/TA assignment, dan pilot outbox producer KP sudah tersedia. MySQL nonproduksi dan real browser login masih pending.

## Prinsip Utama

- Identitas, password, status akun, dan profil resmi dosen tetap milik `core-farmasi`.
- `dosen-farmasi` memiliki database lokal sendiri untuk data domainnya.
- Koneksi ke Core dan aplikasi sumber harus read-only kecuali endpoint push event yang memang milik `dosen-farmasi`.
- Dokumen disimpan pada disk private, bukan `public/`.
- Role manusia hanya `admin` dan `dosen`.

## Stack Target

Berdasarkan discovery awal aplikasi saudara:

- PHP `^8.2`
- Laravel `^12.0`
- MySQL untuk database lokal dan koneksi read-only sumber
- Filament `^5.6` untuk admin/backoffice
- Vite, Tailwind CSS 4, dan Laravel Vite Plugin
- PHPUnit 11 dan Laravel Pint

## Setup Development

```bash
composer install
npm install
php artisan key:generate
php artisan migrate --seed
npm run build
php artisan test
```

Untuk local default, `.env` memakai SQLite dan placeholder aman. Untuk MySQL, ubah `DB_CONNECTION=mysql` dan isi `DB_*` sesuai database lokal `dosen_farmasi`.

## Environment Penting

- `DOSEN_CORE_APP_CODE=dosen-farmasi`
- `DOSEN_CORE_DB_CONNECTION=core_mysql`
- `CORE_DB_*` untuk koneksi read-only ke Core.
- `DOSEN_ADMIN_CORE_USER_IDS` untuk bootstrap admin berbasis Core user ID.
- `DOSEN_PRIVATE_DISK=shared_private`
- `DOSEN_SHARED_PRIVATE_ROOT` untuk mount shared private storage jika tersedia.
- `KP_DB_*`, `KP_PSPA_DB_*`, dan `LAB_DB_*` untuk pull adapter read-only source app.
- `DOSEN_INTEGRATION_MAIL_ENABLED=false` default aman; email butuh queue/mailer dan preferensi dosen.

## Command Penting

```bash
php artisan dosen:storage-check
php artisan dosen:integration-health
php artisan dosen:integration-client-token kp-farmasi
php artisan dosen:sync-integrations kp-farmasi --dry-run --limit=50
php artisan migrate:fresh --seed --env=testing
php artisan test
vendor/bin/pint
npm run build
```

## Batasan Saat Ini

- Core app registry/access untuk `dosen-farmasi` perlu dipersiapkan di Core oleh admin/integrasi untuk real-login penuh.
- Browser protected flow dengan Core nyata bergantung pada ketersediaan Core DB/session lokal.
- Producer event/outbox pilot KP sudah tersedia; KP PSPA, Lab, dan TA producer belum diterapkan.
- Mail queue default nonaktif untuk integrasi sampai preferensi dosen, mailer, dan queue worker siap.
- `.git` workspace tidak valid sebagai repository; lihat `docs/STATUS.md`.

## Dokumen

- `docs/CONTRACT.md` - kontrak produk/teknis ringkas.
- `docs/DISCOVERY.md` - temuan workspace M0.
- `docs/ARCHITECTURE.md` - komponen dan aliran data target.
- `docs/EXECUTION_PLAN.md` - milestone dan acceptance criteria.
- `docs/INTEGRATION_CONTRACT.md` - kontrak event/API internal.
- `docs/M5_SOURCE_DISCOVERY.md` - hasil discovery KP, KP PSPA, Lab, dan TA tambahan.
- `docs/M5_EVENT_CATALOGUE.md` - katalog event M5 dan failure category.
- `docs/M5_SOURCE_MAPPING.md` - mapping source app, outbox, cursor, aggregate.
- `docs/M5_PRODUCER_PLAN.md` - rencana producer event di aplikasi sumber.
- `docs/M5_RUNBOOK.md` - operasi health check, dry-run, backfill, monitoring.
- `docs/M6_KP_PILOT.md` - implementasi pilot producer KP.
- `docs/M6_E2E_TEST_REPORT.md` - hasil fixture E2E dan acceptance pending.
- `docs/DEPLOYMENT.md` - kebutuhan environment dan operasi.
- `docs/STATUS.md` - status aktual dan command yang dijalankan.
- `docs/adr/0001-workspace-compatibility.md` - ADR keputusan kompatibilitas.

Dokumen lengkap awal tetap tersimpan di `docs/Dosen-Farmasi-Planning-dan-Kontrak-Codex.md`.
