# Implement Runbook

## Start

1. Read `AGENTS.md` and docs in the order listed there.
2. Confirm Git/repository state; if Git remains unavailable, record manual file changes in `docs/STATUS.md`.
3. Work one milestone at a time.

## M1 Scaffold Notes

Because `apps/dosen-farmasi` already contains docs, do not run a scaffold command that overwrites the folder blindly. Use a temporary Laravel 12 scaffold or copy a verified sibling skeleton carefully, then merge into target while preserving docs.

Preferred compatibility target:

- Laravel 12
- PHP 8.2
- Filament 5.6
- PHPUnit 11
- Tailwind 4/Vite

## Verification

For every milestone:

- run relevant tests;
- run formatter/linter where available;
- run build when frontend assets change;
- update `docs/STATUS.md` with commands and result;
- do not mark acceptance as done unless verified.

## M2 Workflow Notes

- Semua transisi status portofolio harus lewat `PortfolioStatusTransitionService`.
- `REVISION_REQUIRED` dan `REJECTED` wajib memiliki alasan.
- `REJECTED` dikunci; tidak dibuka kembali menjadi draft pada MVP.
- `SYSTEM_VERIFIED` tidak boleh dihapus atau diubah field resminya oleh dosen.
- Issue report hanya melaporkan dugaan kesalahan data resmi; tidak langsung mengubah sumber resmi.
- Upload dokumen harus private, memakai checksum, nama server-generated, dan cleanup file jika transaksi database gagal.

## M3 Foundation Notes

- Inbox dan agenda M3 memiliki workflow service untuk single/multiple recipient, grouped rows, per-recipient/per-attendee status, transition rules, dan notification database.
- Agenda cancelled tetap terlihat sebagai histori.
- Deteksi bentrok agenda hanya warning, bukan auto-reject.

## M4 Integration Notes

- Semua push event masuk melalui `POST /api/internal/v1/events` dengan bearer token service-client.
- Token disimpan hashed; plaintext hanya muncul saat rotate.
- `source_app` harus cocok dengan authenticated client.
- `event_id` dan `payload_hash` menjadi lapisan idempotency pertama.
- Handler harus update aggregate berdasarkan `source_app`, `source_record_id`, `lecturer_core_id`, dan `source_revision`.
- Event lama tidak boleh mengaktifkan ulang agenda yang sudah cancelled oleh revision lebih baru.
- Failure harus tersimpan di `integration_failures` dengan safe context saja.
- Admin retry/ignore tersedia lewat route admin dan action Filament.

## M5 Source Integration Notes

- Handler KP, KP PSPA, Lab, dan TA assignment memakai base handler `AcademicSourceEventHandler`.
- Resolver identitas wajib dipakai; jangan langsung percaya NIP/email tanpa deteksi ambigu.
- Direct `lecturer_core_id` hanya diterima jika ditemukan aktif di `app_users` atau `lecturer_snapshots`.
- Completion/finalized dapat membuat `PortfolioActivity` `SYSTEM_VERIFIED`; assignment saja tidak.
- `document_references` hanya divalidasi sebagai referensi aman; jangan membaca file source tanpa storage contract eksplisit.
- Pull adapter konkret harus membaca outbox canonical dan tidak menulis ke DB sumber.
- `--dry-run` tidak boleh membuat `integration_events` atau mengubah aggregate.
- Source app producer belum boleh diubah sebelum consumer tests hijau dan mapping disepakati.

## M6 KP Producer Pilot Notes

- Perubahan producer hanya diterapkan pada `apps/kp-farmasi`.
- `kp-farmasi` menulis outbox di transaction domain, lalu delivery job mengirim setelah commit.
- `dosen-farmasi` command `dosen:integration-client-token kp-farmasi` menyiapkan client/token tanpa seeder produksi.
- `kp.supervisor.changed` dan `kp.examiner.changed` harus menutup assignment dosen lama.
- MySQL nonproduction validation wajib sebelum menyatakan M6 complete.

## Safety

- Do not read or print real `.env` secret values.
- Do not run destructive database commands against non-test databases.
- Do not modify sibling apps unless a milestone explicitly requires it.
- Do not add large dependencies before checking sibling patterns.
