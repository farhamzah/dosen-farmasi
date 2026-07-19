# M5 Producer Plan

Dokumen ini menjelaskan perubahan yang perlu dilakukan nanti di aplikasi sumber. Perubahan tersebut belum diterapkan pada M5 consumer.

## Prinsip

- Producer tidak boleh menulis langsung ke tabel bisnis `dosen-farmasi`.
- Producer mengirim push event setelah transaksi domain sumber commit, atau menulis row outbox canonical dalam transaksi yang sama.
- Event harus membawa `source_revision` monoton untuk record sumber yang sama.
- Payload tidak boleh membawa password, token, isi file, atau secret.
- URL aksi harus internal dan aman.

## KP Farmasi

Kandidat producer:

- setelah pembimbing internal ditetapkan atau diganti;
- setelah penguji ujian KP ditetapkan atau diganti;
- setelah jadwal ujian dibuat, diubah, selesai, atau dibatalkan.

Minimal event:

- `kp.supervisor.assigned`
- `kp.supervisor.changed`
- `kp.examiner.assigned`
- `kp.exam.scheduled`
- `kp.exam.rescheduled`
- `kp.exam.completed`
- `kp.exam.cancelled`

## KP PSPA

Kandidat producer:

- publikasi placement/rotation assignment;
- assignment supervisor/preceptor/examiner;
- jadwal aktivitas/assessment;
- finalisasi assessment atau release nilai.

Minimal event:

- `kpspa.supervisor.assigned`
- `kpspa.preceptor.assigned`
- `kpspa.examiner.assigned`
- `kpspa.activity.scheduled`
- `kpspa.activity.completed`
- `kpspa.activity.cancelled`
- `kpspa.assessment.finalized`

## Lab Farmasi

Karena schedule dosen formal belum eksplisit, producer lab disarankan menulis outbox saat:

- dosen ditugaskan ke sesi/kegiatan;
- jadwal kegiatan dibuat/diubah;
- kegiatan selesai atau dibatalkan.

Minimal event:

- `lab.lecturer.assigned`
- `lab.schedule.created`
- `lab.schedule.rescheduled`
- `lab.activity.completed`
- `lab.activity.cancelled`

## TA Farmasi

M4 sudah menangani exam lifecycle. M5 menambah:

- `ta.supervisor.assigned`
- `ta.supervisor.changed`
- `ta.examiner.assigned`
- `ta.examiner.changed`

## Rollout

1. Aktifkan satu source app dengan token service-client atau outbox read-only.
2. Jalankan `php artisan dosen:sync-integrations <source> --dry-run --from=<date> --to=<date>`.
3. Jalankan backfill limit kecil.
4. Periksa `integration_events`, `integration_failures`, inbox, agenda, dan portofolio.
5. Baru aktifkan scheduler/queue untuk sync berkala.
