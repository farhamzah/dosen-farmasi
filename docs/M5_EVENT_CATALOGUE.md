# M5 Event Catalogue

Semua event memakai endpoint `POST /api/internal/v1/events` atau tabel outbox canonical dengan envelope yang sama.

## Identity Payload

Minimal salah satu identitas dosen wajib ada:

- `lecturer_core_id`
- `core_lecturer_id`
- `core_dosen_id`
- `nip`
- `nidn`
- `email`
- `lecturer_number`

Resolver memilih direct Core lecturer ID bila valid. Jika tidak ada, resolver fallback ke NIP/NIDN/email/lecturer number dan menolak hasil ambigu.

## KP

- `kp.supervisor.assigned` -> inbox assignment.
- `kp.supervisor.changed` -> inbox assignment terbaru.
- `kp.examiner.assigned` -> inbox assignment penguji.
- `kp.exam.scheduled` -> agenda.
- `kp.exam.rescheduled` -> update agenda yang sama.
- `kp.exam.completed` -> agenda completed dan portofolio `SYSTEM_VERIFIED`.
- `kp.exam.cancelled` -> agenda/inbox cancelled.

## KP PSPA

- `kpspa.supervisor.assigned` -> inbox assignment.
- `kpspa.preceptor.assigned` -> inbox assignment.
- `kpspa.examiner.assigned` -> inbox assignment.
- `kpspa.activity.scheduled` -> agenda.
- `kpspa.activity.completed` -> agenda completed dan portofolio `SYSTEM_VERIFIED`.
- `kpspa.activity.cancelled` -> agenda/inbox cancelled.
- `kpspa.assessment.finalized` -> portofolio `SYSTEM_VERIFIED`.

## Lab

- `lab.lecturer.assigned` -> inbox assignment.
- `lab.schedule.created` -> agenda.
- `lab.schedule.rescheduled` -> update agenda yang sama.
- `lab.activity.completed` -> agenda completed dan portofolio `SYSTEM_VERIFIED`.
- `lab.activity.cancelled` -> agenda/inbox cancelled.

## TA Assignment Tambahan

- `ta.supervisor.assigned` -> inbox assignment.
- `ta.supervisor.changed` -> inbox assignment terbaru.
- `ta.examiner.assigned` -> inbox assignment.
- `ta.examiner.changed` -> inbox assignment terbaru.

TA completion tetap memakai event M4:

- `ta.exam.completed` -> portofolio `SYSTEM_VERIFIED`.

## Document References

Payload completion boleh membawa `document_references`:

```json
[
  {
    "storage_disk_alias": "shared_private",
    "relative_path": "kp/2026/berita-acara.pdf",
    "sha256": "64-char-hex"
  }
]
```

Validation:

- disk alias harus ada pada `DOSEN_ALLOWED_SOURCE_DOCUMENT_DISKS`;
- path harus relatif, tidak boleh absolute, traversal, atau Windows drive;
- `sha256` harus 64 karakter hex bila dikirim.

M5 menyimpan metadata referensi pada aggregate metadata. Copy file fisik dan ingest dokumen penuh tetap menjadi scope producer/storage contract berikutnya.

## Failure Categories

- `VALIDATION`
- `LECTURER_NOT_FOUND`
- `LECTURER_AMBIGUOUS`
- `DOCUMENT_NOT_FOUND`
- `DATABASE_SOURCE_UNAVAILABLE`
- `CONTRACT_VIOLATION`
- `UNSUPPORTED_EVENT`
- `PROCESSING_ERROR`

Failure disimpan di `integration_failures` dengan `retryable`, kategori, dan safe context.
