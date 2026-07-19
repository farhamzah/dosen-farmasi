# M6 KP Pilot

M6 menambahkan producer pilot di `apps/kp-farmasi` untuk consumer `apps/dosen-farmasi`.

## Scope

Diubah:

- `apps/kp-farmasi`
- `apps/dosen-farmasi`

Tidak diubah:

- `core-farmasi`
- `ta-farmasi`
- `kppspa-farmasi`
- `lab-farmasi`

## Consumer

- `kp.supervisor.changed` menutup inbox dosen lama dan membuat/memperbarui inbox dosen baru.
- `kp.examiner.changed` masuk registry consumer.
- `dosen:integration-client-token {app_code}` membuat/rotate token integration client dan menampilkan plaintext sekali.

## Producer KP

- `integration_outbox_events`
- `integration_revision` pada `kp_assignments` dan `kp_exams`
- `KpDosenPortfolioEventFactory`
- `KpIntegrationOutboxService`
- `DosenFarmasiIntegrationClient`
- `DeliverIntegrationOutboxEvent`
- `kp:deliver-integration-outbox`
- `kp:integration-health`
- scheduler gated by `DOSEN_FARMASI_INTEGRATION_ENABLED`
- monitoring UI `/management/integration/dosen-farmasi-outbox`
- operational prune/recovery command `kp:prune-integration-outbox`

## Lifecycle

- pembimbing ditetapkan/diganti;
- penguji ditetapkan/diganti;
- ujian dijadwalkan/reschedule;
- ujian selesai;
- ujian dibatalkan.

## Acceptance Tooling

- `dosen:audit-kp-integration` tersedia untuk audit read-only antara KP outbox dan objek consumer Dosen.
- `kp:prune-integration-outbox` tersedia dengan default dry-run, retensi minimum 90 hari, dan recovery orphan `PROCESSING` yang harus eksplisit.
- Runbook operasi KP tersedia di `apps/kp-farmasi/docs/INTEGRATION_OPERATIONS.md`.
- Laporan acceptance terbaru tersedia di `docs/M6_ACCEPTANCE_REPORT.md`.

## Status

Technical integration tests lulus pada SQLite/testing lokal. MySQL nonproduksi, live HTTP E2E, Core real-login, dan browser acceptance masih pending, jadi M6 belum production-ready.
