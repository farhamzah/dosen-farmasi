# Execution Plan

## M0 - Workspace Discovery and Compatibility

Status: Done.

Acceptance:

- [x] Prompt dan dokumen kontrak awal dibaca.
- [x] Folder target diperiksa.
- [x] Aplikasi saudara awal diperiksa: Core, TA, TU, Lab.
- [x] Stack dan pola Core auth awal dicatat.
- [x] Git state awal dicatat.
- [x] Dokumen M0 dibuat.
- [x] Scaffold strategy dipilih dan divalidasi dengan command lokal.

## M1 - Foundation and Authentication

Acceptance:

- [x] Laravel app dapat boot di `apps/dosen-farmasi`.
- [x] `.env.example` berisi placeholder aman untuk DB lokal, Core DB read-only, mail, queue, storage.
- [x] Migration local users/references tersedia tanpa password.
- [x] Login Core DB read-only bekerja mengikuti pola TA/Lab melalui fake Core DB di test.
- [x] Role `admin`/`dosen` dan middleware dasar bekerja.
- [x] Policies dasar untuk activity/document tersedia.
- [x] Dashboard awal admin dan dosen tersedia.
- [x] Filament panel dan resource foundation tersedia.
- [x] Shared private storage diagnostic tersedia.
- [x] Tests auth, authorization, boot, database, dan seeder lulus.

## M2 - Portfolio and Documents

Acceptance:

- [x] Migration/model kategori, tipe, aktivitas, peserta, tag, history, issue report tersedia.
- [x] Seeder kategori idempotent.
- [x] CRUD draft manual tersedia.
- [x] Submit draft tersedia.
- [x] Verify/revision/reject tersedia.
- [x] Private document upload/download dengan policy tersedia.
- [x] Checksum dan server-generated filename tersimpan.
- [x] Verification history, peserta, tag, dan issue report tersedia.
- [x] Proteksi `SYSTEM_VERIFIED` tersedia.
- [x] Document version foundation tersedia.
- [x] Dashboard data aktual tersedia.
- [x] Filament resource dan action verifikasi minimum tersedia.
- [x] Tests M2 lulus.

## M3 - Dashboard, Inbox, Agenda, Notifications

Acceptance:

- [x] Dashboard dosen dan admin render dengan data aktual.
- [x] Inbox domain object tersedia.
- [x] Agenda list/calendar dasar tersedia.
- [x] Database notification center tersedia.
- [x] Read/unread state tersedia.
- [x] Deteksi sederhana bentrok agenda tersedia.
- [x] Ownership tests untuk inbox/agenda/notifikasi lulus.
- [ ] Admin-created inbox/calendar UI khusus belum dibuat.

## M4 - Integration Kernel

Status: Done.

Acceptance:

- [x] Integration clients dan token hash tersedia.
- [x] Endpoint `POST /api/internal/v1/events` tersedia.
- [x] Envelope validation, idempotency, raw event, failure/retry tersedia.
- [x] Pull adapter interface dan sync cursor tersedia.
- [x] API tests 401/403/422/accepted/duplicate/failure lulus.

## M5 - KP, KP PSPA, Lab, and TA Assignment Integration

Status: Done for consumer/kernel scope. Producer changes in source apps are planned, not applied.

Acceptance:

- [x] Discovery KP/KP PSPA/Lab/TA tambahan terdokumentasi.
- [x] Handler KP assignment, exam schedule/reschedule/completion/cancellation tersedia.
- [x] Handler KP PSPA supervisor/preceptor/examiner/activity/assessment tersedia.
- [x] Handler Lab lecturer/schedule/activity tersedia.
- [x] Handler TA supervisor/examiner assignment tersedia.
- [x] Identity resolver direct dan fallback tersedia.
- [x] Pull adapter konkret KP, KP PSPA, dan Lab tersedia.
- [x] Cursor, dry-run, limit, from/to backfill command tersedia.
- [x] Failure categories, retryable flag, dan monitoring Filament tersedia.
- [x] Source document reference validator tersedia.
- [x] Tests M5 lulus.

## M6 - Pilot Producer Integration and Reports Foundation

Status: Technical KP pilot implemented; MySQL/live browser acceptance pending.

Acceptance:

- [x] Minimal satu producer source app mengirim push event atau outbox canonical.
- [ ] Pilot backfill satu semester tervalidasi di MySQL.
- [ ] Laporan dosen/admin dasar tersedia.
- [ ] Export/print memakai library workspace.
- [ ] Public profile default off.
- [ ] SISTER external identifiers/mapping siap tanpa production sync.

## M7 - Hardening and Readiness

Acceptance:

- [ ] Full quality gate dijalankan.
- [ ] Security/authorization review selesai.
- [ ] Deployment docs final.
- [ ] Known issues diberi severity dan mitigasi.
