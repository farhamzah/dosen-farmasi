# M7 TA Pilot

M7 memulai producer pilot di `apps/ta-farmasi` untuk consumer `apps/dosen-farmasi`.

## Scope

Diubah:

- `apps/ta-farmasi`
- dokumentasi `apps/dosen-farmasi`

Tidak diubah:

- `core-farmasi`
- `kp-farmasi`
- `kppspa-farmasi`
- `lab-farmasi`

## Consumer Dosen

`dosen-farmasi` sudah memiliki integration client `ta-farmasi` di config dan handler canonical untuk:

- assignment pembimbing;
- assignment penguji;
- jadwal/reschedule ujian;
- completed exam menjadi portfolio `SYSTEM_VERIFIED`;
- pembatalan ujian.

Kontrak endpoint tidak diubah.

## Producer TA

Ditambahkan di `ta-farmasi`:

- `integration_outbox_events`;
- `integration_revision` pada `ta_supervisor_assignments` dan `ta_events`;
- `TaDosenPortfolioEventFactory`;
- `TaIntegrationOutboxService`;
- `DosenFarmasiIntegrationClient`;
- `DeliverIntegrationOutboxEvent`;
- `ta:deliver-integration-outbox`;
- `ta:integration-health`;
- `ta:audit-dosen-integration`;
- `ta:prune-integration-outbox`;
- scheduler delivery yang gated oleh `DOSEN_FARMASI_INTEGRATION_ENABLED`;
- Filament monitoring `/admin/integration-outbox-events`.

## Lifecycle

- pembimbing TA ditetapkan/diganti;
- penguji sidang ditetapkan/diganti;
- sempro atau sidang akhir dijadwalkan;
- sempro atau sidang akhir dijadwalkan ulang;
- sempro atau sidang akhir selesai divalidasi koordinator;
- jadwal dibatalkan.

## Acceptance Tooling

- `dosen:audit-ta-integration` tersedia untuk audit read-only antara TA outbox dan objek consumer Dosen.
- `ta:audit-dosen-integration` tersedia untuk audit lokal outbox producer.
- `ta:prune-integration-outbox` tersedia dengan default dry-run, retensi minimum 90 hari, dan recovery orphan `PROCESSING` yang harus eksplisit.
- Laporan acceptance tersedia di `docs/M7_ACCEPTANCE_REPORT.md`.

## Status

MySQL regression, live HTTP from TA business services, failure drills, queue processing, and cross-app audit passed. M7 is not complete until browser functional QA and official TA document reference acceptance are executed and documented.
