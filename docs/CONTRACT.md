# Contract

## Scope

`dosen-farmasi` menjadi pusat portofolio dosen, inbox, agenda, dokumen private, notifikasi, laporan, audit, dan integrasi aplikasi internal Farmasi UBP.

MVP diprioritaskan sampai fondasi integrasi TU dan TA stabil. KP, KP PSPA, Lab, profil publik, dan SISTER readiness disiapkan setelah pola TU/TA terbukti.

## Identity Boundary

- `core-farmasi` adalah sumber kebenaran akun, password, status aktif, role/app access, dan profil dosen.
- Login `dosen-farmasi` harus memverifikasi Core sesuai pola aplikasi saudara.
- Password tidak disimpan di database lokal. Jika local user/session model diperlukan, tabel lokal hanya menyimpan referensi dan snapshot aman.
- Ubah password, lupa password, dan profil resmi diarahkan ke Core/Profile Portal.

## Local Ownership

Database lokal `dosen-farmasi` menyimpan:

- local app users/references;
- kategori dan tipe portofolio;
- aktivitas portofolio dan verification history;
- dokumen dan versi dokumen;
- inbox, agenda, notifikasi, preferensi;
- integration clients, events, failures, sync cursors;
- audit logs, issue reports, laporan, public profile settings, external identifiers.

## Security Rules

- Object-level authorization wajib untuk aktivitas, dokumen, inbox, agenda, notifikasi, laporan, dan issue report.
- Dosen hanya mengakses data dengan `core_lecturer_id` miliknya.
- Admin boleh lintas dosen sesuai fitur dan tetap diaudit.
- Dokumen hanya dapat diunduh melalui controller/route berpolicy.
- Semua workflow multi-tabel memakai transaction.
- Job/mail/notification dikirim setelah commit jika bergantung pada transaksi.
- Token integration client disimpan hash dan secret plaintext hanya ditampilkan sekali saat dibuat.

## Roles

Role manusia:

- `admin`
- `dosen`

Role atau aplikasi seperti `tu-farmasi`, `ta-farmasi`, `kp-farmasi`, `kppspa-farmasi`, dan `lab-farmasi` diperlakukan sebagai integration client/service account.

## Source Data

- Data manual mulai `DRAFT`.
- Data dari aplikasi internal masuk sebagai `SYSTEM_VERIFIED`.
- Field resmi dari source app tidak dapat diedit dosen.
- Koreksi data source memakai issue report atau event baru dari source.
- Record resmi tidak di-hard-delete; cancellation/supersede mempertahankan riwayat.
