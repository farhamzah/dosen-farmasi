# M5 Source Discovery

Discovery dilakukan read-only terhadap aplikasi saudara. Tidak ada perubahan pada aplikasi sumber.

## KP Farmasi

Folder sumber: `apps/kp-farmasi`.

Temuan utama:

- `kp_assignments` menyimpan penempatan KP, mahasiswa, tempat KP, pembimbing internal/lapangan, status, dan waktu mulai/selesai.
- `kp_exams` menyimpan jadwal ujian KP, pembimbing, penguji, tanggal, jam, mode, ruangan/link, dan status.
- `kp_exam_examiners` mendukung penguji tambahan.
- Mapping Core dosen tersedia lewat `lecturers.core_lecturer_id`.
- Service lokal seperti `KpAssignmentService` dan `KpExamService` menjadi kandidat producer event.

Event consumer M5 di `dosen-farmasi` mencakup assignment pembimbing, assignment penguji, jadwal, reschedule, selesai, dan batal.

## KP PSPA

Folder sumber aktual: `apps/kppspa-farmasi`. Kode canonical consumer memakai `kp-pspa`; nama folder lama didokumentasikan sebagai alias.

Temuan utama:

- Domain PKPA/PSPA mencakup program, enrollment, site, rotasi, supervisor/preceptor, logbook, assessment, dan nilai akhir.
- `pkpa_rotation_assignments` dan `pkpa_rotation_assignment_supervisors` menyimpan assignment rotasi dan pembimbing.
- `pkpa_published_assignments`, `pkpa_published_assignment_supervisors`, dan notification deliveries menjadi kandidat sumber event setelah publikasi penempatan.
- Action/service rotasi mendukung activate, hold, resume, complete, finalize/release assessment.

Event consumer M5 mencakup supervisor, preceptor, examiner, activity scheduled/completed/cancelled, dan assessment finalized.

## Lab Farmasi

Folder sumber: `apps/lab-farmasi`.

Temuan utama:

- Domain lab mencakup ruangan, sesi presensi, pemakaian/perawatan alat, dokumen safety, insiden, dan permintaan material.
- `lab_attendance_sessions` memiliki `core_lecturer_id`, participant/context/purpose, check-in/out, dan status.
- Belum ditemukan tabel jadwal praktikum dosen formal yang setara dengan jadwal ujian TA/KP.

Karena schema assignment/jadwal lab belum eksplisit, M5 menyediakan kontrak event consumer dan pull adapter outbox. Producer lab disarankan menulis event ke outbox canonical sampai schema lab final.

## TA Farmasi Tambahan

Folder sumber: `apps/ta-farmasi`.

Temuan utama:

- M4 sudah menangani event jadwal sidang/reschedule/selesai/batal.
- M5 menambah assignment supervisor dan examiner dari model/service TA yang sudah ada.
- Assignment TA tidak otomatis membuat portofolio sampai ada event completion resmi.

## Kesimpulan

- Jalur utama tetap push event ke `dosen-farmasi`.
- Jalur transisi memakai pull adapter read-only dari tabel outbox sumber `dosen_integration_outbox`.
- `dosen-farmasi` tidak melakukan query spekulatif ke tabel bisnis sumber untuk menebak perubahan. Producer harus mengirim event canonical atau menulis outbox canonical.
