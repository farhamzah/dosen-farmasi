# Planning dan Kontrak Implementasi untuk Codex

## Aplikasi Portofolio Dosen Farmasi UBP — `dosen-farmasi`

**Workspace:** `farmasi-ubp-workspace`  
**Versi dokumen:** 1.0  
**Tanggal:** 16 Juli 2026  
**Status:** Siap digunakan sebagai sumber kebenaran implementasi  
**Pemilik kebutuhan:** Farmasi UBP  
**Pelaksana:** Codex dengan pengawasan pemilik produk/tim teknis

---

## Ringkasan keputusan

Tidak ada pertanyaan bisnis tambahan yang menghambat implementasi. Codex harus memulai dengan discovery terhadap `core-farmasi` dan aplikasi Laravel saudara, lalu mengikuti versi, struktur, autentikasi, UI, queue, mail, storage, dan testing yang sudah berlaku di workspace.

Kontrak utama:

- akun, password, status, dan profil dosen tetap dimiliki `core-farmasi`;
- `dosen-farmasi` memiliki database MySQL sendiri untuk portofolio, inbox, agenda, dokumen metadata, notifikasi, integrasi, dan audit;
- core dan database sumber hanya dibaca menggunakan kredensial read-only;
- password tidak pernah disalin;
- referensi memakai ID internal core, sedangkan NIP/NIDN hanya atribut string nullable;
- role manusia hanya `admin` dan `dosen`;
- data aplikasi internal langsung `SYSTEM_VERIFIED`;
- file berada pada server file bersama yang private;
- notifikasi menggunakan in-app dan email queue;
- push API/event menjadi target, dengan pull adapter read-only sebagai jalur transisi;
- integrasi awal TU dan TA, lalu KP, KP PSPA, dan Lab;
- struktur disiapkan untuk SISTER tetapi production sync bukan MVP.

## Cara menggunakan dokumen

Untuk repository, gunakan berkas terpisah pada package:

1. `AGENTS.md` sebagai instruksi ringkas Codex;
2. `docs/CONTRACT.md` sebagai kontrak produk/teknis;
3. `docs/INTEGRATION_CONTRACT.md` sebagai kontrak integrasi;
4. `docs/EXECUTION_PLAN.md` sebagai milestone dan acceptance criteria;
5. `docs/IMPLEMENT.md` sebagai runbook;
6. `docs/STATUS.md` sebagai status log;
7. `docs/adr/0001-workspace-compatibility.md` sebagai keputusan discovery;
8. `CODEX_START_PROMPT.md` sebagai prompt kickoff.

Dokumen gabungan ini ditujukan untuk review, persetujuan, dan arsip.

---

# Bagian I — Kontrak Produk dan Teknis


| Atribut | Nilai |
|---|---|
| Aplikasi | `dosen-farmasi` |
| Workspace | `farmasi-ubp-workspace` |
| Stack | Laravel dan MySQL, mengikuti aplikasi yang sudah berjalan |
| Versi dokumen | 1.0 |
| Tanggal | 16 Juli 2026 |
| Status | Disetujui untuk implementasi bertahap oleh Codex |
| Zona waktu tampilan | `Asia/Jakarta` |

## 1. Tujuan

`dosen-farmasi` menjadi pusat aktivitas, dokumen, agenda, notifikasi, dan portofolio dosen Farmasi UBP. Aplikasi ini menggabungkan dua jenis data:

1. data yang dimasukkan dosen sendiri untuk pengajaran, penelitian, pengabdian, penunjang, kompetensi, penghargaan, dan organisasi; dan
2. data resmi yang diterima dari aplikasi lain seperti `tu-farmasi`, `ta-farmasi`, `kp-farmasi`, `kp-pspa`, `lab-farmasi`, serta aplikasi lain yang ditambahkan kemudian.

Aplikasi tidak menggantikan `core-farmasi` dan tidak menggantikan aplikasi sumber. Ia menjadi tampilan terpadu dan repositori portofolio dosen dengan jejak asal data yang jelas.

## 2. Urutan kewenangan keputusan

Jika ditemukan perbedaan saat implementasi, Codex memakai urutan berikut:

1. aturan keamanan dan infrastruktur yang sudah terbukti dipakai di workspace;
2. kontrak ini;
3. `docs/INTEGRATION_CONTRACT.md`;
4. `docs/EXECUTION_PLAN.md`;
5. keputusan teknis Codex yang dicatat sebagai ADR.

Konvensi workspace boleh mengubah detail teknis, tetapi tidak boleh melanggar prinsip kepemilikan data, tidak menyimpan password, akses basis data sumber secara read-only, otorisasi per objek, privasi dokumen, idempotensi integrasi, dan audit trail.

## 3. Keputusan arsitektur yang sudah dikunci

| Area | Keputusan |
|---|---|
| Lokasi aplikasi | `farmasi-ubp-workspace/dosen-farmasi` |
| Framework | Gunakan versi Laravel, PHP, Node, package manager, dan pola UI yang sama dengan aplikasi saudara |
| Database lokal | Database MySQL terpisah, nama mengikuti konvensi deployment; nama semantik: `dosen_farmasi` |
| Identitas | `core-farmasi` adalah sumber kebenaran user, password, status akun, dan profil dosen |
| Cara akses core | Koneksi database langsung mengikuti pola aplikasi saudara, menggunakan kredensial read-only |
| Password | Tidak pernah disalin ke database lokal dan tidak pernah masuk log |
| Referensi dosen | ID internal dari `core-farmasi`; NIP/NIDN hanya atribut nullable bertipe string |
| Role manusia | Hanya `admin` dan `dosen` |
| Aplikasi terhubung | Integration client/service account, bukan role manusia |
| Data aplikasi internal | Langsung `SYSTEM_VERIFIED` |
| Penyimpanan file | Server file bersama melalui Laravel filesystem disk yang private |
| Notifikasi | Notifikasi dalam aplikasi dan email berbasis queue |
| WhatsApp | Di luar ruang lingkup awal |
| Profil publik | Disiapkan sebagai fitur opt-in, default nonaktif/private |
| SISTER | Struktur data siap dipetakan; sinkronisasi produksi bukan bagian MVP |

## 4. Sumber kebenaran data

| Domain data | Pemilik/sumber kebenaran | Aturan di `dosen-farmasi` |
|---|---|---|
| Akun, password, status aktif | `core-farmasi` | Dibaca langsung; tidak disalin kecuali snapshot non-rahasia untuk kebutuhan session/tampilan |
| Profil dosen, NIP, NIDN, email institusi | `core-farmasi` | Dibaca langsung atau dicache sebagai snapshot; perubahan profil dilakukan di core |
| Surat TU | `tu-farmasi` | Ditampilkan dan ditautkan; field resmi dikelola sumber |
| Penugasan/sidang TA | `ta-farmasi` | Membentuk inbox, agenda, dan portofolio terverifikasi |
| Kegiatan KP | `kp-farmasi` | Membentuk inbox, agenda, dan portofolio terverifikasi |
| Kegiatan KP PSPA | `kp-pspa` | Membentuk inbox, agenda, dan portofolio terverifikasi |
| Kegiatan laboratorium | `lab-farmasi` | Membentuk agenda dan portofolio terverifikasi sesuai jenis kegiatan |
| Portofolio manual | `dosen-farmasi` | Dikelola dosen; dapat melalui verifikasi admin |
| Dokumen portofolio lokal | `dosen-farmasi` | Metadata lokal; berkas pada storage bersama private |
| Preferensi notifikasi dan profil publik | `dosen-farmasi` | Dikelola dosen atau admin sesuai izin |
| Data SISTER | SISTER sebagai sistem eksternal | Simpan mapping/identifier; sinkronisasi hanya setelah fase khusus dan sandbox |

## 5. Ruang lingkup produk

### 5.1 Ruang lingkup MVP

- koneksi dan autentikasi melalui `core-farmasi` dengan pola yang sama seperti aplikasi saudara;
- provisioning akses lokal tanpa password;
- role `admin` dan `dosen`;
- dashboard dosen;
- portofolio manual untuk pengajaran, penelitian, pengabdian, penunjang, kompetensi, penghargaan, dan organisasi;
- unggah, tautkan, versi, dan unduh dokumen private;
- inbox terpadu;
- agenda dosen;
- notifikasi dalam aplikasi dan email;
- verifikasi aktivitas manual oleh admin;
- audit trail;
- fondasi integrasi API/event dan adapter pembacaan database read-only;
- integrasi prioritas `tu-farmasi` dan `ta-farmasi`;
- monitoring integrasi dan retry;
- laporan dasar per semester dan rekap portofolio.

### 5.2 Ruang lingkup lanjutan

- integrasi `kp-farmasi`, `kp-pspa`, dan `lab-farmasi`;
- profil publik dosen yang opt-in;
- CV otomatis;
- ekspor PDF/Excel sesuai library yang sudah ada di workspace;
- pemetaan kategori dan identifier SISTER;
- sinkronisasi SISTER melalui sandbox lalu production setelah kredensial dan persetujuan tersedia;
- calendar feed/iCalendar bila dibutuhkan;
- digest email dan preferensi notifikasi yang lebih rinci.

### 5.3 Di luar ruang lingkup

- penyimpanan atau pengelolaan password di `dosen-farmasi`;
- pengubahan profil utama dosen;
- penggantian fungsi `tu-farmasi`, `ta-farmasi`, `kp-farmasi`, `kp-pspa`, atau `lab-farmasi`;
- penulisan langsung ke database aplikasi lain;
- WhatsApp otomatis;
- aplikasi mobile native;
- tanda tangan elektronik tersertifikasi;
- sinkronisasi SISTER production pada MVP;
- penggajian, kehadiran pegawai, dan sistem kepegawaian penuh;
- hard delete atas data resmi yang sudah pernah diterima.

## 6. Aktor dan hak akses

### 6.1 Dosen

Dosen hanya dapat mengakses objek yang terkait dengan ID dosennya sendiri. Dosen dapat:

- melihat dashboard, inbox, agenda, notifikasi, dokumen, dan portofolio sendiri;
- menambahkan dan mengubah aktivitas manual yang masih dapat diedit;
- mengunggah dan menghubungkan bukti kegiatan;
- mengirim aktivitas manual untuk diverifikasi;
- menambahkan catatan atau bukti tambahan pada aktivitas hasil sinkronisasi;
- melaporkan kesalahan data resmi;
- mengatur preferensi notifikasi;
- memilih item yang ditampilkan pada profil publik bila fitur aktif;
- menghasilkan laporan miliknya sendiri.

Dosen tidak dapat:

- mengubah field resmi yang dikelola aplikasi sumber;
- melihat dokumen atau aktivitas dosen lain;
- mengelola integration client;
- memverifikasi aktivitasnya sendiri sebagai admin;
- melihat log yang berisi data lintas dosen.

### 6.2 Admin

Admin dapat:

- melihat dan mengelola data lokal seluruh dosen;
- mengelola akses lokal admin/dosen tanpa mengubah password core;
- mengelola kategori dan tipe aktivitas;
- memverifikasi atau meminta revisi aktivitas manual;
- melihat dan menindaklanjuti laporan kesalahan data;
- mengelola integration client dan token;
- melihat status event, retry event gagal, dan menjalankan rekonsiliasi;
- mengelola template/pengaturan notifikasi;
- menghasilkan laporan agregat;
- melihat audit log sesuai kebutuhan operasional.

Hak admin tidak menghapus kewajiban audit, validasi, atau proteksi file.

## 7. Kontrak autentikasi dan akses ke core

### 7.1 Prinsip

- Codex wajib meniru pola autentikasi core yang telah digunakan aplikasi saudara.
- Koneksi core harus memakai user MySQL read-only dan tidak boleh memakai akun `root`.
- Password diverifikasi terhadap hash di core dengan mekanisme yang sama seperti aplikasi saudara.
- Setelah login berhasil, session aplikasi mengikuti pola workspace.
- Session harus diregenerasi setelah login dan diinvalidasi saat logout.
- Fitur ubah password, lupa password, dan perubahan profil mengarah ke `core-farmasi` atau mekanisme bersama yang sudah ada.

### 7.2 Provisioning lokal

`dosen-farmasi` boleh memiliki tabel akses lokal seperti `app_users`, tetapi dengan syarat:

- tidak ada kolom password;
- `core_user_id` unik;
- `core_lecturer_id` dipakai sebagai referensi kepemilikan data;
- nama, email, NIP, dan NIDN lokal hanya snapshot/cache, bukan sumber kebenaran;
- status aktif core selalu dihormati saat login dan pada pemeriksaan berkala yang mengikuti pola workspace;
- dosen yang valid dapat di-auto-provision sebagai role `dosen` bila konvensi aplikasi saudara mendukungnya;
- role `admin` diberikan melalui seeder/config/halaman admin yang aman, bukan berdasarkan input user.

### 7.3 Identitas

- `core_user_id` dan `core_lecturer_id` memakai tipe data yang sama dengan core.
- NIP dan NIDN disimpan sebagai `VARCHAR`/string nullable.
- NIP dan NIDN bukan primary key, bukan foreign key utama, dan tidak diasumsikan selalu ada.
- Bila core menyediakan identifier lain yang lebih stabil, Codex mendokumentasikannya dalam ADR dan menggunakannya.

## 8. Modul fungsional dan persyaratan

### 8.1 Dashboard

**FR-DASH-001** Dashboard dosen menampilkan minimal:

- jumlah inbox belum dibaca;
- agenda terdekat;
- surat atau penugasan baru;
- aktivitas portofolio semester berjalan;
- aktivitas manual berstatus draft, submitted, atau perlu revisi;
- dokumen terbaru;
- status sinkronisasi yang relevan bagi dosen tanpa menampilkan detail sensitif.

**FR-DASH-002** Dashboard admin menampilkan minimal:

- aktivitas menunggu verifikasi;
- integration event gagal/tertunda;
- jumlah user aktif;
- dokumen atau proses notifikasi yang bermasalah;
- ringkasan aktivitas per kategori.

**FR-DASH-003** Semua daftar harus dipaginasi dan bebas N+1 yang jelas.

### 8.2 Portofolio

**FR-PORT-001** Kategori awal:

1. Pendidikan dan Pengajaran;
2. Penelitian dan Pengembangan;
3. Pengabdian kepada Masyarakat;
4. Penunjang;
5. Pengembangan Kompetensi;
6. Penghargaan;
7. Organisasi dan Kepanitiaan.

**FR-PORT-002** Admin dapat mengelola tipe aktivitas di bawah kategori tanpa migrasi baru.

**FR-PORT-003** Aktivitas manual mendukung minimal:

- judul;
- deskripsi;
- peran dosen;
- kategori dan tipe;
- tahun akademik dan semester;
- tanggal mulai dan selesai;
- institusi/mitra;
- lokasi;
- sumber dana dan nilai dana bila relevan;
- SKS/angka kredit bila relevan;
- anggota/partisipan;
- tag;
- bukti dokumen;
- visibilitas private/internal/public;
- status verifikasi.

**FR-PORT-004** Status verifikasi:

- `DRAFT`;
- `SUBMITTED`;
- `ADMIN_VERIFIED`;
- `SYSTEM_VERIFIED`;
- `REVISION_REQUIRED`;
- `REJECTED`;
- `CANCELLED`;
- `ARCHIVED`.

**FR-PORT-005** Aktivitas hasil aplikasi internal dibuat `SYSTEM_VERIFIED`.

**FR-PORT-006** Field resmi dari sumber tidak dapat diubah dosen. Field tambahan lokal, catatan, tag, dokumen tambahan, dan visibilitas dapat diubah sesuai policy.

**FR-PORT-007** Perubahan status harus tercatat dalam history, termasuk aktor, waktu, alasan, status sebelum, dan status sesudah.

**FR-PORT-008** Data manual memakai soft delete. Data resmi memakai event cancel/supersede dan tetap meninggalkan riwayat.

### 8.3 Dokumen

**FR-DOC-001** Jenis awal:

- surat tugas;
- surat keputusan;
- undangan;
- berita acara;
- sertifikat;
- kontrak;
- laporan;
- bukti publikasi;
- bukti HKI;
- bahan ajar;
- dokumentasi;
- lainnya.

**FR-DOC-002** Metadata minimal:

- UUID/ID lokal;
- jenis dokumen;
- disk;
- relative path;
- nama asli;
- nama simpan yang dibuat aplikasi;
- ekstensi;
- MIME type yang terdeteksi;
- ukuran;
- SHA-256;
- sumber aplikasi dan ID sumber bila ada;
- pengunggah;
- waktu unggah;
- status pemindaian bila fitur antivirus tersedia.

**FR-DOC-003** Berkas disimpan pada disk private di server file bersama. Tidak boleh ada direct public URL atau symlink publik untuk dokumen internal.

**FR-DOC-004** Download melalui controller/service yang menjalankan policy.

**FR-DOC-005** Validasi upload memakai allowlist ekstensi, batas ukuran berbasis config, pemeriksaan MIME, pemeriksaan file signature sejauh library workspace mendukung, nama file UUID, dan perlindungan path traversal.

**FR-DOC-006** Default allowlist awal dapat mencakup PDF, DOCX, XLSX, PPTX, JPG/JPEG, dan PNG. Perubahan allowlist dilakukan melalui config dan diuji. Arsip executable atau file script tidak diizinkan.

**FR-DOC-007** Satu dokumen dapat ditautkan ke lebih dari satu aktivitas/inbox bila hak aksesnya sesuai.

### 8.4 Inbox

**FR-INBOX-001** Inbox adalah objek bisnis, berbeda dari notification.

**FR-INBOX-002** Inbox mendukung minimal:

- tipe;
- judul;
- ringkasan;
- prioritas;
- sumber aplikasi dan ID sumber;
- status `UNREAD`, `READ`, `COMPLETED`, `CANCELLED`, atau `ARCHIVED`;
- action URL yang telah divalidasi;
- waktu kejadian dan tenggat;
- dokumen terkait.

**FR-INBOX-003** Satu item sumber dapat menghasilkan satu item per penerima. Composite uniqueness mencegah duplikasi.

**FR-INBOX-004** Action URL hanya boleh menuju base URL yang dikonfigurasi untuk integration client terkait.

### 8.5 Agenda

**FR-CAL-001** Agenda mendukung jadwal sidang, bimbingan, pengujian, rapat, praktikum, pengajaran, penelitian, pengabdian, dan kegiatan lain.

**FR-CAL-002** Agenda minimal menyimpan judul, waktu mulai/selesai, all-day, lokasi, status, sumber, ID sumber, dan deskripsi.

**FR-CAL-003** Event `rescheduled` memperbarui agenda yang sama dan menyimpan audit/history, bukan membuat duplikat tanpa hubungan.

**FR-CAL-004** Event `cancelled` mempertahankan catatan dan menampilkan status batal.

**FR-CAL-005** UI menampilkan potensi bentrok jadwal. Pada MVP, bentrok adalah peringatan, bukan otomatis menolak data sumber.

**FR-CAL-006** Payload integrasi selalu menggunakan ISO 8601 dengan offset. Tampilan menggunakan `Asia/Jakarta`; aturan penyimpanan database mengikuti konvensi workspace dan dicatat dalam ADR.

### 8.6 Notifikasi dan email

**FR-NOTIF-001** Semua event penting membuat database notification.

**FR-NOTIF-002** Email langsung minimal untuk:

- undangan/penugasan baru;
- perubahan jadwal;
- pembatalan jadwal;
- surat tugas atau surat penting baru;
- permintaan revisi portofolio;
- kegagalan integrasi kritis kepada admin.

**FR-NOTIF-003** Pengiriman email dan notifikasi yang berat menggunakan queue dan dijalankan setelah transaction commit.

**FR-NOTIF-004** Kegagalan email tidak membatalkan transaksi bisnis utama. Ia masuk retry/failed-job monitoring.

**FR-NOTIF-005** Dosen dapat mengatur preferensi email untuk kategori non-kritis. Notifikasi kritis tetap mengikuti kebijakan admin.

**FR-NOTIF-006** WhatsApp tidak diimplementasikan pada fase awal.

### 8.7 Verifikasi dan laporan kesalahan

**FR-VER-001** Aktivitas manual dapat dikirim dari `DRAFT` ke `SUBMITTED`.

**FR-VER-002** Admin dapat menetapkan `ADMIN_VERIFIED`, `REVISION_REQUIRED`, atau `REJECTED` dengan catatan.

**FR-VER-003** Dosen tidak dapat mengubah sendiri status menjadi verified.

**FR-VER-004** Aktivitas `SYSTEM_VERIFIED` tidak melalui approval admin ulang.

**FR-VER-005** Kesalahan pada data sumber dilaporkan melalui `data_issue_reports`, dengan status open/in_review/resolved/rejected dan referensi ke objek terkait.

### 8.8 Admin integrasi

**FR-INT-ADMIN-001** Admin dapat melihat daftar integration client, status aktif, abilities, last used, dan expiry token tanpa melihat token plaintext setelah dibuat.

**FR-INT-ADMIN-002** Admin dapat melihat event berdasarkan client, type, source record, status, tanggal, dan error.

**FR-INT-ADMIN-003** Admin dapat retry event gagal secara aman dan idempotent.

**FR-INT-ADMIN-004** Admin dapat menjalankan rekonsiliasi adapter pull sesuai hak akses dan audit.

### 8.9 Laporan

**FR-REP-001** Laporan dasar dosen:

- daftar aktivitas per semester;
- rekap per kategori;
- daftar pembimbingan/pengujian;
- daftar surat tugas/dokumen;
- aktivitas yang belum lengkap buktinya.

**FR-REP-002** Laporan admin dapat difilter menurut dosen, program studi, semester, kategori, sumber, dan status.

**FR-REP-003** Ekspor memakai library yang sudah ada di workspace. Jangan menambah dependency baru tanpa evaluasi.

### 8.10 Profil publik

**FR-PUB-001** Profil publik default nonaktif.

**FR-PUB-002** Hanya item yang dipilih dosen dan lolos aturan admin yang tampil.

**FR-PUB-003** Dokumen internal, nomor pribadi, alamat, identifier sensitif, surat, dan log tidak pernah otomatis publik.

**FR-PUB-004** Slug unik, dapat diubah secara aman, dan tidak mengekspos ID internal.

## 9. Model data minimum

Nama tabel dapat disesuaikan dengan konvensi workspace, tetapi kapabilitas berikut wajib tersedia.

### 9.1 Akses lokal

`app_users`

- `id`;
- `core_user_id` unique;
- `core_lecturer_id` nullable, indexed;
- `role` (`admin`/`dosen`);
- `status`;
- snapshot nama/email/NIP/NIDN non-rahasia;
- `last_login_at`;
- `profile_synced_at`;
- timestamps.

Tidak ada kolom password.

### 9.2 Portofolio

- `portfolio_categories`;
- `portfolio_activity_types`;
- `portfolio_activities`;
- `portfolio_activity_participants`;
- `verification_histories`;
- `tags` dan pivot tag;
- `data_issue_reports`.

Kolom inti `portfolio_activities`:

- ID lokal;
- `core_lecturer_id`;
- tipe aktivitas;
- judul/deskripsi/peran;
- tahun akademik/semester;
- tanggal;
- institusi/lokasi/dana/SKS/angka kredit;
- status verifikasi dan lifecycle;
- `source_type`;
- `source_app`;
- `source_entity`;
- `source_record_id`;
- `source_url` yang divalidasi;
- hash payload sumber bila dipakai;
- visibility;
- created/updated actor;
- timestamps dan soft delete untuk data manual.

Composite unique minimum untuk data integrasi:

```text
(source_app, source_entity, source_record_id, core_lecturer_id, activity_type)
```

Jika skema aktual membutuhkan variasi, hasilnya tetap harus menjamin satu record portofolio per dosen per aktivitas sumber.

### 9.3 Dokumen

- `documents`;
- `document_links` atau relasi polymorphic yang ekuivalen;
- `document_versions` bila versi dokumen diperlukan sumber.

### 9.4 Komunikasi

- `inbox_items`;
- `calendar_events`;
- tabel notifications standar Laravel atau ekuivalen;
- `notification_preferences` bila dibutuhkan.

### 9.5 Integrasi

- `integration_clients`;
- personal access tokens/hashed tokens sesuai solusi workspace;
- `integration_events`;
- `integration_sync_cursors`;
- failed jobs/queue monitoring menggunakan mekanisme Laravel/workspace.

### 9.6 Audit dan pengaturan

- `audit_logs`;
- `application_settings` bila tidak ada sistem setting bersama;
- `public_profile_settings` untuk fase profil publik.

## 10. Aturan integritas data

- Event diterima dan direkam sebelum side effect diproses.
- `event_id` unik global pada `dosen-farmasi`.
- Pemrosesan side effect dilakukan dalam transaction lokal.
- Job/notification/email dipicu after commit.
- Replay event yang sama mengembalikan hasil idempotent dan tidak membuat objek baru.
- Event yang memperbarui record harus merujuk source key yang sama.
- Pembatalan tidak menghapus jejak historis.
- Record resmi tidak dapat diubah menjadi manual untuk menghindari hilangnya provenance.
- Semua query daftar dipaginasi dan memiliki index pada filter utama.
- JSON payload boleh disimpan untuk audit/troubleshooting, tetapi field sensitif harus direduksi atau dienkripsi sesuai pola workspace.

## 11. Arsitektur integrasi

Target arsitektur:

```text
core-farmasi  --read-only identity/profile-->  dosen-farmasi

 tu-farmasi -----\
 ta-farmasi ------\
 kp-farmasi -------+-- push API/event --> integration_events --> processors
 kp-pspa ----------/
 lab-farmasi -----/

legacy source DB --read-only pull adapter--> canonical event --> same processors

processors --> inbox + calendar + portfolio + documents + notifications + audit
```

Semua sumber, baik push maupun pull, harus melalui canonical event processor yang sama. Adapter pull tidak boleh menulis langsung ke tabel bisnis dengan logika sendiri.

Detail endpoint, payload, idempotensi, dan mapping event terdapat pada `docs/INTEGRATION_CONTRACT.md`.

## 12. Penyimpanan file bersama

### 12.1 Konfigurasi

- Gunakan disk Laravel bernama semantik `shared_private` atau nama yang sesuai pola workspace.
- Root berasal dari environment variable.
- Local mount NFS/SMB diperlakukan sebagai local disk; SFTP digunakan bila pola server mengharuskannya.
- Default visibility private.
- Tidak membuat `storage:link` untuk disk ini.

### 12.2 Struktur path

Contoh semantik:

```text
dosen-farmasi/{year}/{month}/{uuid}.{extension}
```

Path aktual dapat mengikuti konvensi workspace. Database hanya menyimpan relative path.

### 12.3 Akses

- Setiap download memeriksa policy berdasarkan dokumen dan objek yang ditautkan.
- Response memakai nama asli yang telah disanitasi sebagai filename download.
- Raw path tidak dikirim ke frontend.
- URL sementara hanya digunakan bila storage dan pola keamanan workspace mendukungnya.

## 13. Keamanan, privasi, dan audit

### 13.1 Otorisasi per objek

Setiap endpoint yang menerima ID wajib memeriksa bahwa user berhak atas objek tersebut. UUID tidak menggantikan policy. Minimal policy:

- `PortfolioActivityPolicy`;
- `DocumentPolicy`;
- `InboxItemPolicy`;
- `CalendarEventPolicy`;
- `ReportPolicy`;
- `DataIssueReportPolicy`;
- policy admin untuk integrasi dan pengaturan.

### 13.2 API internal

- HTTPS pada environment non-lokal;
- token per aplikasi, disimpan dalam bentuk hash;
- abilities/scopes minimum;
- rate limiting per client;
- token dapat dirotasi dan dicabut;
- optional IP allowlist;
- validation ketat dan batas payload;
- audit request ID/client/event ID;
- tidak mengembalikan stack trace atau data sensitif.

### 13.3 Database

- user database terpisah per aplikasi/koneksi;
- core dan source read-only;
- tidak memakai query unprepared dengan input user;
- credential hanya dari secret/environment management;
- migration hanya pada database lokal;
- backup/restore mengikuti operasi workspace.

### 13.4 Log

- Log tidak boleh memuat password, token plaintext, cookie, file content, atau data pribadi berlebihan.
- Error integrasi menyimpan pesan yang cukup untuk diagnosis tetapi meredaksi secret.
- Audit log minimal memuat actor, action, objek, waktu, request/correlation ID, source app, serta before/after untuk perubahan penting.

### 13.5 File upload

- allowlist;
- ukuran maksimum configurable;
- MIME dan signature check;
- nama server-generated;
- private storage;
- scan hook bila antivirus tersedia;
- reject path traversal, double extension yang berbahaya, dan executable/script.

## 14. Kesiapan SISTER

Implementasi awal tidak memanggil API SISTER, tetapi harus:

- menyimpan identifier eksternal seperti `sister_id_sdm` jika tersedia;
- memungkinkan mapping kategori/tipe lokal ke kode resource SISTER;
- menyimpan status sinkronisasi eksternal tanpa mencampurnya dengan status verifikasi lokal;
- menjaga provenance dan dokumen bukti;
- membedakan Pendidikan/Pengajaran, Penelitian/Pengembangan, dan Pengabdian;
- menyediakan extension point untuk sandbox dan production endpoint;
- tidak hardcode token atau endpoint dalam source code.

Fase SISTER baru dimulai setelah akses, data mapping, aturan validasi, dan skenario konflik disetujui. Pengujian harus melalui sandbox sebelum production.

## 15. Persyaratan non-fungsional

### 15.1 Kompatibilitas

- Ikuti versi dan pola aplikasi saudara.
- Tidak melakukan upgrade workspace terselubung.
- Tidak menambah dependency production bila fungsi sudah tersedia di workspace.
- `.env.example` harus lengkap tanpa secret.

### 15.2 Kinerja

- Semua daftar besar paginated.
- Index untuk `core_lecturer_id`, status, tanggal, source key, event ID, dan filter utama.
- Dashboard memakai query terukur dan tidak memuat seluruh dataset.
- Proses email, file processing berat, rekonsiliasi, dan integrasi besar masuk queue.
- Event ingestion memberi respons cepat setelah validasi dan persistence; side effect dapat asynchronous.

### 15.3 Keandalan

- Idempotency dan retry aman.
- Scheduler pull memakai lock `withoutOverlapping`; gunakan single-server lock bila deployment multi-node dan cache mendukung.
- Rekonsiliasi periodik mendeteksi event yang terlewat.
- Failed jobs dan failed events dapat dilihat admin.
- Tidak ada silent failure.

### 15.4 Keterpeliharaan

- Business logic utama berada di service/action/domain layer sesuai pola workspace, bukan controller besar.
- Request validation memakai Form Request atau pola setara.
- API response memakai resource/serializer yang konsisten.
- Enum/status terpusat bila versi PHP/Laravel mendukung.
- Mapping aplikasi sumber terisolasi dalam adapter/mapper.
- Kode dan dokumentasi memakai istilah domain yang konsisten.

### 15.5 Aksesibilitas dan UX

- Responsive mengikuti desain aplikasi saudara.
- Form memiliki label, error message, state loading/empty/error.
- Warna status tidak menjadi satu-satunya penanda.
- Tabel memiliki filter yang jelas dan dapat digunakan keyboard sejauh komponen workspace mendukung.

## 16. Strategi pengujian

### 16.1 Unit test

Minimal untuk:

- mapping event ke aktivitas/inbox/agenda;
- status transition;
- source-managed field guard;
- idempotency key;
- document path/filename validation;
- permission helper/policy logic;
- SISTER mapping extension point bila dibuat.

### 16.2 Feature test

Minimal untuk:

- login berhasil/gagal menggunakan fixture core yang aman;
- user core nonaktif ditolak;
- role admin dan dosen;
- dosen tidak dapat mengakses objek dosen lain;
- CRUD aktivitas manual;
- submit, verify, revision, reject;
- upload dan download private;
- invalid extension/MIME/oversize;
- event ingestion sukses;
- replay event;
- reschedule dan cancel;
- email/database notification queued after commit;
- admin retry failed event;
- filter dan pagination.

### 16.3 Integration/smoke test

- koneksi read-only core;
- koneksi shared storage;
- queue worker;
- scheduler lock;
- SMTP/mail sink di non-production;
- token integration client;
- satu end-to-end flow TU;
- satu end-to-end flow TA dari assignment/schedule hingga completed/cancelled.

### 16.4 Uji keamanan wajib

- IDOR/BOLA untuk activity, document, inbox, calendar, dan report;
- event dengan recipient ID yang tidak valid;
- replay token/event;
- action URL di luar allowlist;
- file path traversal;
- file spoofing dasar;
- log tidak memuat token/password;
- dosen mencoba mengubah source-managed fields;
- aplikasi source tanpa ability yang benar.

## 17. Definition of Done produk

Produk dianggap selesai untuk milestone terkait bila:

- semua acceptance criteria milestone terpenuhi;
- migration dapat dijalankan dari database kosong dan rollback sesuai batas aman;
- seeder dapat dijalankan berulang tanpa duplikasi;
- test/lint/build yang berlaku lulus;
- tidak ada defect kritis terkait akses lintas dosen, duplikasi event, kehilangan provenance, password, atau file publik;
- dokumentasi setup, environment, queue, scheduler, storage, mail, integrasi, dan troubleshooting diperbarui;
- `docs/STATUS.md` mencatat hasil aktual;
- demo/smoke scenario dapat dijalankan dari instruksi tertulis.

## 18. Risiko dan mitigasi

| Risiko | Mitigasi kontraktual |
|---|---|
| Skema core berbeda dari asumsi | Milestone discovery wajib; mapping dicatat dalam ADR; jangan hardcode sebelum inspeksi |
| Core tidak tersedia | Tampilkan error yang aman; jangan mengizinkan login baru; session existing mengikuti kebijakan workspace; monitoring koneksi |
| Duplikasi data lintas aplikasi | Unique source key, unique event ID, canonical processor, payload hash, reconciliation |
| Perubahan jadwal menghasilkan record baru | Upsert berdasarkan source key; simpan history reschedule |
| Data resmi diedit lokal | Source-managed field guard dan policy; issue report untuk koreksi |
| File bocor | Private disk, policy download, raw path tersembunyi, allowlist dan validation |
| Email memperlambat request | Queue after commit, retry, failed-job monitoring |
| Integrasi aplikasi lama belum dapat push | Read-only pull adapter yang menghasilkan canonical event |
| Ketergantungan antar database terlalu erat | Adapter/repository terisolasi; hanya core identity direct-read; source data masuk melalui integration layer |
| SISTER berubah | Mapping terpisah dan sandbox-first; jangan hardcode ke model inti |
| Scope melebar | Milestone, non-goals, dan change log; usulan tambahan dicatat sebagai follow-up |

## 19. Keputusan yang boleh ditentukan Codex saat discovery

Tanpa meminta ulang keputusan bisnis, Codex boleh memilih setelah inspeksi:

- versi Laravel/PHP/Node;
- Blade/Livewire/Inertia/Vue/React dan CSS framework;
- Pest atau PHPUnit;
- Pint/PHP-CS-Fixer/PHPStan/Larastan yang sudah dipakai;
- database queue atau Redis sesuai workspace;
- Sanctum atau mekanisme token internal yang sudah dipakai;
- local mount atau SFTP untuk shared storage;
- tipe ID bigint/UUID/ULID sesuai konvensi;
- struktur namespace/service/action;
- format audit log yang kompatibel dengan paket workspace;
- library export yang sudah tersedia.

Semua pilihan aktual dicatat dalam ADR dan `docs/STATUS.md`.

## 20. Referensi rancangan

- [R1] OpenAI, “Custom instructions with AGENTS.md”: panduan instruksi repository dan override bertingkat.
- [R2] OpenAI, “Using PLANS.md for multi-hour problem solving”: ExecPlan untuk fitur kompleks.
- [R3] OpenAI, “Run long horizon tasks with Codex”: spec, milestone, runbook, verifikasi berkelanjutan, dan status log.
- [R4] Laravel Documentation, “Database: Using Multiple Database Connections”.
- [R5] Laravel Documentation, “Authentication: Custom User Providers”.
- [R6] Laravel Documentation, “Authorization: Policies”.
- [R7] Laravel Documentation, “Notifications” dan “Queues”: queued notification serta after-commit.
- [R8] Laravel Documentation, “File Storage”: private local/SFTP filesystem disks.
- [R9] Laravel Documentation, “Sanctum”: hashed API token dan token abilities.
- [R10] Laravel Documentation, “Task Scheduling”: `withoutOverlapping`.
- [R11] OWASP API Security Top 10, “Broken Object Level Authorization”.
- [R12] OWASP File Upload Cheat Sheet.
- [R13] SISTER, “Informasi API SISTER (Versi Cloud)”.

URL lengkap dicantumkan pada dokumen gabungan dan dapat diperbarui saat implementasi.

---

# Bagian II — Kontrak Integrasi


## 1. Tujuan

Dokumen ini menetapkan cara aplikasi Farmasi UBP mengirim aktivitas, surat, jadwal, dokumen, dan perubahan status kepada `dosen-farmasi` tanpa menulis langsung ke tabel bisnisnya.

## 2. Prinsip

- Push API/event adalah target utama.
- Read-only pull adapter adalah jalur transisi untuk aplikasi lama.
- Push dan pull menghasilkan canonical event yang sama dan diproses oleh pipeline yang sama.
- Setiap aplikasi memiliki integration client dan token sendiri.
- Event immutable; perubahan dikirim sebagai event baru.
- Side effect wajib idempotent.
- Sumber tetap menjadi pemilik field resminya.
- Semua tanggal/waktu payload memakai ISO 8601 dengan offset.

## 3. Authentication dan transport

### 3.1 Target

```http
Authorization: Bearer <token-per-aplikasi>
Content-Type: application/json
Accept: application/json
X-Request-ID: <uuid-opsional>
```

Persyaratan:

- HTTPS di staging/production;
- token disimpan hashed;
- token plaintext hanya ditampilkan sekali saat dibuat;
- abilities minimum, misalnya `events:write`, `documents:write`, atau `health:read`;
- rate limit per client;
- token expiry/rotation mengikuti kebijakan workspace;
- optional IP allowlist;
- secret tidak masuk source code atau log.

Jika workspace telah memiliki standar autentikasi service-to-service, Codex menggunakannya selama kemampuan di atas tetap terpenuhi.

## 4. Endpoint minimum

### 4.1 Health

```http
GET /api/internal/v1/health
```

Respons contoh:

```json
{
  "status": "ok",
  "service": "dosen-farmasi",
  "version": "1.0",
  "time": "2026-07-16T10:00:00+07:00"
}
```

Health publik tidak boleh mengungkap detail database, storage, queue, atau exception.

### 4.2 Ingest event

```http
POST /api/internal/v1/events
```

Respons baru diterima:

```http
202 Accepted
```

```json
{
  "event_id": "f664f34c-7071-4bfd-bca6-f1645933b8f1",
  "status": "accepted",
  "duplicate": false
}
```

Respons replay event yang sama:

```http
200 OK
```

```json
{
  "event_id": "f664f34c-7071-4bfd-bca6-f1645933b8f1",
  "status": "processed",
  "duplicate": true
}
```

Status umum:

- `200` duplicate/idempotent result;
- `202` accepted;
- `400` malformed JSON;
- `401` invalid/missing token;
- `403` token tidak memiliki ability/client tidak aktif;
- `422` payload validation error;
- `429` rate limited;
- `500` internal error dengan request ID, tanpa stack trace.

### 4.3 Upload dokumen internal

Implementasikan hanya bila pola workspace memerlukan transfer file ke `dosen-farmasi`:

```http
POST /api/internal/v1/documents
Content-Type: multipart/form-data
```

Metadata wajib mengandung `source_app`, `source_document_id`, `document_type`, dan referensi event/source record. Endpoint memakai allowlist dan limit yang sama dengan upload UI.

Bila semua aplikasi menggunakan shared file server dan memiliki reference contract yang aman, event dapat membawa file reference relatif. Jangan menerima absolute path atau path yang keluar dari root allowlist.

## 5. Canonical event envelope

```json
{
  "event_id": "f664f34c-7071-4bfd-bca6-f1645933b8f1",
  "event_type": "ta.defense.scheduled",
  "schema_version": 1,
  "source_app": "ta-farmasi",
  "source_entity": "defense",
  "source_record_id": "SIDANG-2026-00123",
  "occurred_at": "2026-07-16T10:00:00+07:00",
  "correlation_id": "TA-MHS-000123",
  "recipients": [
    {
      "core_user_id": "1001",
      "core_lecturer_id": "D-001",
      "role": "PENGUJI_1"
    }
  ],
  "subject": {
    "type": "student",
    "id": "MHS-00123",
    "name": "Nama Mahasiswa",
    "title": "Judul Tugas Akhir"
  },
  "schedule": {
    "starts_at": "2026-07-22T09:00:00+07:00",
    "ends_at": "2026-07-22T11:00:00+07:00",
    "all_day": false,
    "location": "Ruang Sidang 1"
  },
  "content": {
    "title": "Sidang Tugas Akhir",
    "summary": "Penugasan sebagai Penguji 1",
    "description": null,
    "priority": "HIGH"
  },
  "action": {
    "label": "Buka detail sidang",
    "url": "https://ta.example.internal/sidang/SIDANG-2026-00123"
  },
  "documents": [
    {
      "source_document_id": "ST-2026-0042",
      "document_type": "SURAT_TUGAS",
      "title": "Surat Tugas Penguji",
      "file_reference": "ta-farmasi/2026/07/st-2026-0042.pdf",
      "sha256": null
    }
  ],
  "metadata": {}
}
```

### 5.1 Field wajib

- `event_id`: UUID, unik global;
- `event_type`: lowercase dot notation;
- `schema_version`: integer positif;
- `source_app`: harus sama dengan code client atau ada dalam allowlist client;
- `source_entity`;
- `source_record_id`;
- `occurred_at`;
- `recipients`: minimal satu penerima untuk event dosen;
- content yang cukup untuk side effect event type terkait.

### 5.2 Recipient

- `core_lecturer_id` adalah identifier utama;
- `core_user_id` dapat disertakan untuk cross-check;
- NIP/NIDN tidak digunakan sebagai recipient key;
- recipient yang tidak ditemukan masuk status failed/needs_review; jangan diam-diam dipetakan berdasarkan nama.

### 5.3 Action URL

- base URL harus didaftarkan pada integration client;
- hanya `https` pada non-lokal;
- tidak boleh `javascript:`, data URI, credential in URL, atau host di luar allowlist;
- frontend boleh menyembunyikan tombol bila URL tidak valid.

### 5.4 File reference

- selalu relative path atau source document ID;
- normalisasi separator;
- tolak `..`, null byte, encoded traversal, drive letter, dan absolute path;
- root prefix harus sesuai client;
- hash diverifikasi bila disediakan;
- izin download tetap berdasarkan recipient/policy lokal.

## 6. Event type awal

### 6.1 TU

- `tu.letter.assigned`
- `tu.letter.updated`
- `tu.letter.cancelled`
- `tu.assignment_letter.published`

Side effect umum:

- inbox;
- document link;
- notification;
- agenda bila surat memiliki jadwal;
- portfolio hanya bila event menyatakan kegiatan yang sudah terjadi/ditetapkan sebagai portofolio.

### 6.2 TA

- `ta.supervisor.assigned`
- `ta.examiner.assigned`
- `ta.defense.scheduled`
- `ta.defense.rescheduled`
- `ta.defense.completed`
- `ta.defense.cancelled`
- `ta.document.published`

Side effect contoh:

| Event | Inbox | Agenda | Portofolio | Email |
|---|---:|---:|---:|---:|
| supervisor.assigned | Ya | Opsional | Ya, `SYSTEM_VERIFIED` | Ya |
| examiner.assigned | Ya | Belum bila tidak ada jadwal | Ya/placeholder sesuai aturan sumber | Ya |
| defense.scheduled | Upsert | Upsert | Tidak membuat duplikat assignment | Ya |
| defense.rescheduled | Update | Update + history | Tidak duplikat | Ya |
| defense.completed | Complete | Complete | Finalisasi aktivitas pengujian | Opsional |
| defense.cancelled | Cancel | Cancel | Cancel/supersede sesuai state | Ya |

### 6.3 KP

- `kp.supervisor.assigned`
- `kp.examiner.assigned`
- `kp.exam.scheduled`
- `kp.exam.rescheduled`
- `kp.activity.completed`
- `kp.activity.cancelled`

### 6.4 KP PSPA

Namespace canonical menggunakan `kp_pspa` walaupun folder aplikasi bernama `kp-pspa`:

- `kp_pspa.preceptor.assigned`
- `kp_pspa.supervisor.assigned`
- `kp_pspa.examiner.assigned`
- `kp_pspa.activity.scheduled`
- `kp_pspa.activity.completed`
- `kp_pspa.activity.cancelled`

Jika istilah domain aktual berbeda, mapper dapat menambah event type baru dengan versioning tanpa mengubah envelope.

### 6.5 Lab

- `lab.lecturer.assigned`
- `lab.schedule.created`
- `lab.schedule.rescheduled`
- `lab.activity.completed`
- `lab.activity.cancelled`

### 6.6 Generic extension

- `portfolio.activity.upserted`
- `portfolio.activity.cancelled`
- `document.assigned`
- `calendar.event.upserted`
- `calendar.event.cancelled`

Event generic hanya digunakan setelah mapping dan validation rule disetujui; event domain-spesifik lebih diutamakan.

## 7. Idempotensi dan ordering

### 7.1 Event ID

- unique index pada `event_id`;
- duplicate exact event mengembalikan hasil sebelumnya;
- duplicate `event_id` dengan payload berbeda ditandai conflict/security anomaly dan tidak diproses otomatis.

### 7.2 Source key

Side effect bisnis memakai source key:

```text
source_app + source_entity + source_record_id + core_lecturer_id + side_effect_type
```

### 7.3 Payload hash

Simpan canonical payload hash untuk mendeteksi perubahan pada event ID yang sama dan membantu audit.

### 7.4 Ordering

Sistem tidak boleh mengasumsikan event selalu tiba berurutan. Gunakan `occurred_at`, lifecycle rules, dan source version/updated_at bila disediakan. Event lama yang datang setelah state baru tidak boleh menurunkan state tanpa aturan eksplisit.

## 8. Processing pipeline

```text
HTTP/pull adapter
  -> authenticate/authorize client
  -> validate envelope + event-specific payload
  -> persist integration_event
  -> dispatch ProcessIntegrationEvent after commit
  -> lock/idempotency check
  -> resolve recipient against core read-only
  -> map event to canonical commands
  -> local transaction:
       upsert inbox
       upsert calendar
       upsert portfolio
       link documents
       append audit/history
  -> commit
  -> dispatch database notification/email after commit
  -> mark processed
```

Kegagalan:

- event status `FAILED`;
- `attempt_count`, `last_error_code`, pesan teredaksi, dan timestamp;
- retry exponential/backoff sesuai queue convention;
- dead-letter/admin review setelah batas retry;
- event validation permanen tidak di-retry otomatis tanpa perubahan data.

## 9. Pull adapter untuk aplikasi lama

Setiap source adapter:

- memakai koneksi MySQL read-only;
- berada di namespace/module khusus sumber;
- hanya membaca view/table yang diperlukan;
- menggunakan cursor `source_updated_at` plus tie-breaker ID;
- mengubah row menjadi canonical event;
- mengirim event ke processor lokal, bukan menulis tabel bisnis;
- memakai scheduler lock `withoutOverlapping`;
- menyimpan last success, cursor, row count, dan error;
- mendukung dry-run/reconcile;
- memiliki test fixture yang tidak berisi data pribadi nyata.

Perintah semantik:

```text
php artisan integrations:sync <source> [--from=...] [--to=...] [--dry-run]
php artisan integrations:reconcile <source> [--date=YYYY-MM-DD]
php artisan integrations:retry <event-id>
```

Nama command final mengikuti konvensi workspace.

## 10. Versioning

- Endpoint major version pada URL: `/v1/`.
- Envelope memiliki `schema_version`.
- Perubahan backward-compatible menambah field optional.
- Penghapusan/ubah makna field memerlukan schema version baru.
- Processor mempertahankan versi yang masih dipakai aplikasi sumber selama masa migrasi.
- Client dan supported event versions terdokumentasi di admin/integration docs.

## 11. Mapping status

### Inbox

```text
new/assigned/scheduled -> UNREAD
viewed locally         -> READ
completed              -> COMPLETED
cancelled              -> CANCELLED
archived by user       -> ARCHIVED
```

### Calendar

```text
scheduled   -> SCHEDULED
rescheduled -> RESCHEDULED atau SCHEDULED + history sesuai model
completed   -> COMPLETED
cancelled   -> CANCELLED
```

### Portfolio

```text
approved internal assignment/completion -> SYSTEM_VERIFIED
source cancellation                     -> CANCELLED
source correction                       -> upsert + history
manual input                             -> DRAFT/SUBMITTED/... admin workflow
```

## 12. Acceptance criteria integrasi

- token client A tidak dapat mengirim sebagai client B;
- client tanpa ability ditolak;
- invalid recipient tidak membuat data yatim;
- event baru menghasilkan side effect yang diharapkan;
- replay tidak menggandakan side effect atau notifikasi;
- event ID sama dengan payload berbeda ditahan;
- reschedule mengubah agenda yang benar dan menyimpan history;
- cancel tidak hard-delete;
- action URL di luar allowlist ditolak atau dihapus secara aman;
- invalid file reference tidak dapat diunduh;
- notification/email dijalankan after commit;
- failed event terlihat admin dan dapat di-retry;
- pull adapter dan push event untuk source key sama tetap idempotent.

---

# Bagian III — Execution Plan


Dokumen ini adalah ExecPlan aktif. Codex memperbarui status, keputusan, hasil verifikasi, dan deviation langsung di dokumen ini serta `docs/STATUS.md`.

## Status ringkas

| Milestone | Nama | Status awal | Gate |
|---|---|---|---|
| M0 | Discovery dan compatibility contract | NOT_STARTED | Wajib sebelum coding fitur |
| M1 | Scaffold dan fondasi aplikasi | NOT_STARTED | Aplikasi boot, DB lokal, health, CI commands |
| M2 | Core authentication dan authorization | NOT_STARTED | Login core, role, policy isolation |
| M3 | Portofolio manual dan dokumen private | NOT_STARTED | CRUD, workflow, upload/download aman |
| M4 | Dashboard, inbox, agenda, notifikasi | NOT_STARTED | Flow user end-to-end lokal |
| M5 | Integration kernel | NOT_STARTED | Client, event ingestion, idempotency, admin monitoring |
| M6 | Integrasi TU dan TA | NOT_STARTED | Dua flow sumber end-to-end |
| M7 | Integrasi KP, KP PSPA, dan Lab | NOT_STARTED | Source adapters/events aktif |
| M8 | Laporan, profil publik, dan SISTER readiness | NOT_STARTED | Output dan extension point |
| M9 | Hardening, dokumentasi, dan handover | NOT_STARTED | Release candidate terverifikasi |

Status yang diperbolehkan: `NOT_STARTED`, `IN_PROGRESS`, `BLOCKED`, `DONE`, `DEFERRED`.

## Cara menjalankan plan

1. Selesaikan milestone secara berurutan kecuali dependency jelas memungkinkan paralel.
2. Sebelum milestone dimulai, tulis pendekatan dan file yang diperkirakan berubah di `docs/STATUS.md`.
3. Setelah implementasi, jalankan semua validation milestone.
4. Perbaiki kegagalan sebelum menandai `DONE`.
5. Catat keputusan berbeda dari contract sebagai ADR.
6. Jangan menandai `DONE` hanya karena kode terkompilasi; acceptance criteria harus dibuktikan.

---

## M0 — Discovery dan compatibility contract

### Tujuan

Memastikan aplikasi baru benar-benar mengikuti pola yang sudah digunakan dalam workspace dan tidak menciptakan stack, autentikasi, atau desain UI baru yang tidak perlu.

### Pekerjaan

- [ ] Baca `AGENTS.md` workspace/root bila ada.
- [ ] Inspeksi struktur `farmasi-ubp-workspace`.
- [ ] Inspeksi `core-farmasi` untuk model/tabel user, profil dosen, status akun, hash password, identifier, dan koneksi.
- [ ] Inspeksi minimal `ta-farmasi` dan `tu-farmasi`; tambah aplikasi saudara lain bila keduanya tidak mewakili pola terbaru.
- [ ] Catat versi PHP, Laravel, Composer, Node, package manager, database, queue, cache, mail, storage, testing, linting, frontend, dan deployment.
- [ ] Catat cara aplikasi saudara membaca database core dan melakukan login/session/logout.
- [ ] Catat komponen layout, navigation, form, table, notification, dan error page yang harus dipakai ulang.
- [ ] Catat shared file server mount/disk convention.
- [ ] Catat struktur data minimal dari TU dan TA untuk mapping pertama.
- [ ] Buat `docs/adr/0001-workspace-compatibility.md`.
- [ ] Perbarui `docs/STATUS.md` dengan command aktual.

### Deliverable

- ADR compatibility;
- matrix source-to-field;
- daftar command setup/test/lint/build;
- keputusan scaffold;
- daftar dependency yang akan dipakai ulang;
- daftar gap non-blocking.

### Acceptance criteria

- [ ] Tidak ada versi framework yang diasumsikan tanpa bukti dari repository.
- [ ] Mapping `core_user_id` dan `core_lecturer_id` teridentifikasi.
- [ ] Mekanisme password verification dan session teridentifikasi tanpa menyalin password.
- [ ] UI stack dan layout reference teridentifikasi.
- [ ] Queue/mail/storage patterns teridentifikasi.
- [ ] Test/lint/build commands dapat dijalankan atau alasan kegagalannya dicatat dengan tindakan perbaikan.

### Validation

- command inspeksi dan hasil dicatat;
- tidak ada perubahan destructive pada aplikasi existing;
- tidak ada secret disalin ke docs.

---

## M1 — Scaffold dan fondasi aplikasi

### Tujuan

Membuat `dosen-farmasi` yang dapat dijalankan dengan stack yang sama, konfigurasi aman, database lokal, dan kerangka dokumentasi/quality.

### Pekerjaan

- [ ] Buat folder/repository aplikasi sesuai struktur workspace.
- [ ] Scaffold dari versi/pola aplikasi saudara, bukan versi terbaru secara otomatis.
- [ ] Konfigurasi koneksi database lokal dan koneksi read-only core di `.env.example`.
- [ ] Konfigurasi cache, queue, mail, dan shared private disk mengikuti workspace.
- [ ] Buat route health yang aman.
- [ ] Buat migration awal untuk akses lokal, categories/types, portfolio, documents, inbox, calendar, integration, audit.
- [ ] Buat seeder kategori dan tipe awal yang idempotent.
- [ ] Tambahkan request/correlation ID bila pola workspace mendukung.
- [ ] Terapkan layout/navigation dasar yang sama dengan aplikasi saudara.
- [ ] Siapkan error pages dan logging redaction.
- [ ] Tambahkan README setup dan `.env.example` tanpa secret.

### Acceptance criteria

- [ ] Aplikasi boot pada environment lokal yang sesuai workspace.
- [ ] `migrate:fresh --seed` atau command ekuivalen berhasil pada DB test.
- [ ] Health route tidak membocorkan detail internal.
- [ ] Shared storage disk dapat melakukan write/read/delete smoke test pada folder test tanpa public URL.
- [ ] Queue dan mail configuration dapat divalidasi dengan fake/local sink.
- [ ] Formatter/linter/test baseline lulus.
- [ ] Tidak ada password column pada tabel lokal.

### Validation scenario

1. install dependencies;
2. copy env example dan set credential lokal;
3. generate app key bila pola memerlukan;
4. migrate/seed;
5. start app;
6. hit health;
7. run tests/lint/build.

---

## M2 — Core authentication dan authorization

### Tujuan

Menggunakan user/password/profile dari `core-farmasi` dengan aman dan memberi akses lokal `admin` atau `dosen`.

### Pekerjaan

- [ ] Implementasikan core models/repository/adapter dengan koneksi read-only sesuai pola sibling.
- [ ] Implementasikan login form/controller/service sesuai UI sibling.
- [ ] Verifikasi password menggunakan mechanism core.
- [ ] Provision/update local `app_users` tanpa password.
- [ ] Implementasikan logout, session regeneration/invalidation, auth middleware.
- [ ] Tautkan profil dosen live/snapshot.
- [ ] Implementasikan role middleware/gate.
- [ ] Implementasikan policy dasar untuk lecturer ownership dan admin override yang diaudit.
- [ ] Buat seeder/config admin awal sesuai konvensi aman.
- [ ] Arahkan perubahan password/profile ke core.

### Acceptance criteria

- [ ] Kredensial core valid dapat login.
- [ ] Kredensial salah ditolak tanpa membocorkan apakah username ada.
- [ ] User core nonaktif ditolak.
- [ ] Password tidak tersimpan di DB/log lokal.
- [ ] Dosen valid memperoleh role `dosen` sesuai provisioning policy.
- [ ] Admin hanya berasal dari assignment yang disetujui.
- [ ] Session diregenerasi saat login dan diinvalidasi saat logout.
- [ ] Test membuktikan dosen A tidak dapat mengakses route/objek dosen B.
- [ ] Core DB user pada dokumentasi deployment adalah read-only.

---

## M3 — Portofolio manual dan dokumen private

### Tujuan

Memberi dosen kemampuan menyusun portofolio lengkap dengan bukti kegiatan dan workflow admin.

### Pekerjaan

- [ ] CRUD category/type admin.
- [ ] CRUD aktivitas manual dosen.
- [ ] Participant/tag support.
- [ ] Status transition dan verification history.
- [ ] Admin verification queue.
- [ ] `data_issue_reports` foundation.
- [ ] Upload service ke shared private disk.
- [ ] Document metadata, hash, link, dan download policy.
- [ ] Optional document versioning bila source model memerlukan.
- [ ] Filter/pagination/search aktivitas dan dokumen.
- [ ] Audit log untuk create/update/delete/verify/download sensitif sesuai pola.

### Acceptance criteria

- [ ] Dosen dapat membuat draft, mengubah, melampirkan bukti, dan submit.
- [ ] Admin dapat verify, request revision, atau reject dengan alasan.
- [ ] Dosen tidak dapat self-verify.
- [ ] Dosen lain tidak dapat melihat/mengunduh objek tersebut.
- [ ] Invalid/oversize/spoofed upload dasar ditolak.
- [ ] File disimpan private dengan server-generated name dan SHA-256.
- [ ] Soft delete data manual tercatat.
- [ ] Semua state transition memiliki history.
- [ ] Seed categories tidak duplikat saat dijalankan ulang.

---

## M4 — Dashboard, inbox, agenda, dan notifikasi

### Tujuan

Menyediakan pengalaman harian dosen sebelum integrasi sumber diaktifkan penuh.

### Pekerjaan

- [ ] Dashboard dosen dan admin.
- [ ] Inbox list/detail/read/archive.
- [ ] Calendar month/list atau komponen sesuai workspace.
- [ ] Conflict warning.
- [ ] Database notification.
- [ ] Email notification queue after commit.
- [ ] Notification preferences dasar.
- [ ] Mark read/unread.
- [ ] Scheduler/queue operational docs.
- [ ] Empty/loading/error states.

### Acceptance criteria

- [ ] Dashboard hanya menampilkan data user terkait.
- [ ] Inbox berbeda secara domain dari notification.
- [ ] Calendar reschedule/cancel state dapat ditampilkan.
- [ ] Database notification dibuat untuk event penting.
- [ ] Email di-queue dan kegagalannya tidak rollback transaksi bisnis.
- [ ] UI mengikuti layout sibling dan responsive.
- [ ] Pagination/filter bekerja dengan dataset fixture yang cukup.

---

## M5 — Integration kernel

### Tujuan

Membangun fondasi service-to-service yang aman, versioned, idempotent, dan dapat dimonitor.

### Pekerjaan

- [ ] Integration client model/admin CRUD aman.
- [ ] Token hashed dan abilities sesuai solusi workspace.
- [ ] Internal API route/middleware/rate limit.
- [ ] Event envelope validation.
- [ ] `integration_events` persistence dan payload hash.
- [ ] Async event processor after commit.
- [ ] Mapper/handler registry per event type/version.
- [ ] Idempotency dan source uniqueness.
- [ ] Action URL allowlist.
- [ ] File reference validator.
- [ ] Retry/dead-letter/admin monitoring.
- [ ] Pull-adapter interface dan sync cursor.
- [ ] Scheduler lock dan reconciliation command foundation.
- [ ] API documentation/example payload.

### Acceptance criteria

- [ ] Client tanpa token/ability ditolak.
- [ ] Event valid diterima dan diproses.
- [ ] Replay event tidak menduplikasi object atau notification.
- [ ] Event ID sama dengan payload berbeda ditandai conflict.
- [ ] Invalid recipient tidak menciptakan data yatim.
- [ ] Failure terlihat admin dan retry aman.
- [ ] Pull dan push menggunakan processor yang sama.
- [ ] API tests mencakup 401, 403, 422, 429, accepted, duplicate, dan failed processing.

---

## M6 — Integrasi TU dan TA

### Tujuan

Membuktikan pola integrasi melalui dua sumber prioritas dengan end-to-end flow nyata.

### Pekerjaan TU

- [ ] Mapping surat yang ditujukan kepada dosen.
- [ ] `tu.letter.assigned/updated/cancelled`.
- [ ] Surat tugas/document reference.
- [ ] Inbox + notification + optional agenda.
- [ ] Pull adapter jika TU belum dapat push.
- [ ] Reconciliation test.

### Pekerjaan TA

- [ ] Mapping pembimbing/penguji.
- [ ] Mapping jadwal, perubahan, completion, cancel.
- [ ] Inbox + agenda + `SYSTEM_VERIFIED` portfolio.
- [ ] Link surat tugas/berita acara bila tersedia.
- [ ] Source-managed fields.
- [ ] Pull adapter jika TA belum dapat push.
- [ ] Reconciliation test.

### Acceptance criteria TU

- [ ] Surat baru untuk dosen muncul satu kali di inbox.
- [ ] Update memperbarui item yang sama.
- [ ] Cancel mempertahankan history dan mengubah status.
- [ ] Dokumen hanya dapat diunduh penerima/admin.
- [ ] Email dikirim sesuai priority policy.

### Acceptance criteria TA

- [ ] Assignment dosen menghasilkan portofolio resmi yang tepat.
- [ ] Schedule menghasilkan agenda dan inbox.
- [ ] Reschedule mengubah agenda yang sama serta mengirim notification.
- [ ] Completion memfinalisasi aktivitas tanpa duplikasi.
- [ ] Cancel tidak menghapus history.
- [ ] Replay/pull+push tidak menggandakan data.
- [ ] Dosen dapat menambahkan bukti/catatan lokal tetapi tidak mengubah field sumber.

### Gate MVP

MVP dapat dinyatakan operasional setelah M0–M6 selesai, hardening kritis dijalankan, dan dokumentasi deployment tersedia.

---

## M7 — Integrasi KP, KP PSPA, dan Lab

### Tujuan

Memperluas pattern yang sudah terbukti tanpa menambah jalur processing baru.

### Pekerjaan

- [ ] Discovery skema sumber per aplikasi.
- [ ] Event type/mapper KP.
- [ ] Event type/mapper `kp_pspa`.
- [ ] Event type/mapper Lab.
- [ ] Pull adapter atau push integration.
- [ ] Document mapping.
- [ ] End-to-end test assignment/schedule/completion/cancel per sumber.
- [ ] Admin monitoring/filter per source.

### Acceptance criteria

- [ ] Setiap source memakai canonical event kernel.
- [ ] Source key dan status mapping terdokumentasi.
- [ ] Tidak ada direct write antar database.
- [ ] Satu flow sukses dan satu cancel/reschedule lulus per source.
- [ ] Reconciliation dan idempotency lulus.

---

## M8 — Laporan, profil publik, dan SISTER readiness

### Tujuan

Mengubah data operasional menjadi keluaran portofolio yang berguna dan menyiapkan ekstensi eksternal.

### Pekerjaan

- [ ] Laporan semester per dosen.
- [ ] Rekap kategori, pembimbingan, pengujian, surat tugas.
- [ ] Admin aggregate filters.
- [ ] Export menggunakan library workspace.
- [ ] CV generator bila disetujui dalam fase ini.
- [ ] Public profile settings default off.
- [ ] Per-item visibility.
- [ ] External identifiers termasuk `sister_id_sdm`.
- [ ] Mapping table/config kategori SISTER.
- [ ] Sync-state extension point tanpa production call.
- [ ] Data completeness report.

### Acceptance criteria

- [ ] Laporan dosen tidak dapat mengakses data dosen lain.
- [ ] Export memiliki filter dan provenance yang benar.
- [ ] Profil publik tidak menampilkan item private/internal atau dokumen internal.
- [ ] Default profil publik off.
- [ ] SISTER mapping terpisah dari model inti dan dapat ditambah tanpa migrasi besar.

---

## M9 — Hardening, dokumentasi, dan handover

### Tujuan

Mempersiapkan release candidate yang dapat dioperasikan dan dikembangkan tim berikutnya.

### Pekerjaan

- [ ] Full regression tests.
- [ ] Security test matrix.
- [ ] Query/index review dan pagination review.
- [ ] Queue retry/failed job drill.
- [ ] Event replay/reconciliation drill.
- [ ] Storage permission and restore drill sesuai environment yang tersedia.
- [ ] Log redaction review.
- [ ] `.env.example` dan deployment guide final.
- [ ] Scheduler/worker/service configuration guide.
- [ ] Integration onboarding guide.
- [ ] Admin/user quick guide.
- [ ] ADR index dan known issues.
- [ ] Release checklist.

### Acceptance criteria

- [ ] Semua test/lint/build lulus.
- [ ] Tidak ada known critical security/data integrity issue.
- [ ] Deployment dari instruksi tertulis berhasil pada environment target/test.
- [ ] Queue worker dan scheduler terkonfigurasi.
- [ ] Token dan secret tidak ada di repository.
- [ ] Admin dapat mendiagnosis event gagal dari UI/log tanpa membuka database secara manual untuk kasus umum.
- [ ] Handover docs memungkinkan developer baru menjalankan smoke test.

---

## Catatan keputusan dan deviation

Codex menambahkan entry bertanggal di bawah ini saat ada perubahan nyata terhadap plan.

```text
YYYY-MM-DD — Mx — Keputusan/deviation — Alasan — Dampak — Verifikasi
```

---

# Bagian IV — Runbook Codex


## 1. Mandat

Implementasikan `dosen-farmasi` sesuai `docs/CONTRACT.md` dan milestone di `docs/EXECUTION_PLAN.md`. Plan adalah sumber kebenaran urutan kerja; contract adalah sumber kebenaran ruang lingkup dan invariants.

## 2. Cara mulai

1. Baca seluruh instruksi repository yang aktif.
2. Baca contract, integration contract, execution plan, dan status.
3. Mulai M0, bukan langsung membuat scaffold berdasarkan asumsi.
4. Periksa aplikasi existing secara read-only terlebih dahulu.
5. Catat hasil discovery dan command aktual.
6. Baru implementasikan milestone berikutnya.

## 3. Aturan eksekusi

- Kerjakan satu milestone dengan diff yang terfokus.
- Jangan mengubah `core-farmasi` atau aplikasi sumber kecuali milestone integrasi memang membutuhkan perubahan push dan perubahan itu backward-compatible.
- Bila source belum dapat diubah, buat pull adapter read-only.
- Jangan menambahkan fitur di luar contract untuk “sekalian”.
- Jangan mengganti dependency/version workspace tanpa kebutuhan nyata dan ADR.
- Jangan menulis password, token, credential, data dosen nyata, atau file nyata ke fixture/repository.
- Gunakan data dummy yang jelas.
- Setiap migration harus aman, memiliki index/constraint, dan dapat rollback sejauh praktik workspace.
- Setiap side effect integrasi harus memiliki test idempotency.
- Setiap route objek dosen harus memiliki test unauthorized cross-user.

## 4. Checkpoint wajib setiap milestone

Sebelum menandai milestone selesai:

1. Jalankan migration/seed dari clean test database.
2. Jalankan test yang relevan dan full suite bila wajar.
3. Jalankan formatter/linter/static analysis yang dipakai workspace.
4. Jalankan frontend build bila frontend berubah.
5. Jalankan smoke test yang tertulis pada milestone.
6. Periksa `.env.example` dan README.
7. Periksa log untuk secret/PII.
8. Perbarui `docs/STATUS.md`:
   - status milestone;
   - file/komponen penting;
   - keputusan;
   - command dan hasil;
   - demo steps;
   - known issues/follow-ups.
9. Perbarui `docs/EXECUTION_PLAN.md` checkbox/status.
10. Perbaiki failure sebelum lanjut.

## 5. Format laporan status Codex

Gunakan ringkasan seperti:

```text
Milestone: M2 — Core authentication
Status: DONE
Implemented:
- ...
Decisions:
- ...
Validation:
- `php artisan test --filter=Authentication` — PASS
- `...` — PASS
Known follow-ups:
- ...
Next:
- M3
```

## 6. Penanganan blocker

Blocker teknis yang tidak mengubah keputusan bisnis ditangani dengan best effort dan dicatat. Contoh:

- bila Redis tidak tersedia, pakai database queue jika sesuai workspace;
- bila source belum punya API, gunakan read-only pull adapter;
- bila antivirus tidak tersedia, implementasikan scan hook/status tanpa mengklaim file telah dipindai;
- bila export library belum ada, selesaikan laporan HTML/print terlebih dahulu dan catat export sebagai follow-up milestone;
- bila identifier core ambigu, gunakan mapping yang terbukti pada sibling app dan catat ADR.

Jangan menebak credential, URL production, mount path, atau data rahasia. Gunakan environment placeholder yang jelas.

## 7. Change control

Perubahan contract hanya boleh dilakukan dengan entry di ADR/change log yang menjelaskan:

- requirement asal;
- perubahan;
- alasan;
- risiko;
- compatibility;
- migration/data impact;
- test baru;
- apakah memerlukan keputusan pemilik produk.

Codex boleh menyelesaikan detail teknis sendiri. Perubahan yang membatalkan non-negotiable tidak diperbolehkan.

## 8. Release gate

Sebelum release candidate:

- M0–M6 DONE untuk MVP;
- security test critical path PASS;
- queue worker dan scheduler terdokumentasi;
- integration token dapat dirotasi;
- database dan storage environment terpisah dari source apps;
- core/source credentials read-only;
- no secret in git history/diff saat ini;
- rollback/restore instructions tersedia;
- admin dan dosen smoke scenario PASS;
- known issues diberi severity dan mitigasi.

---

# Bagian V — Instruksi Repository (`AGENTS.md`)


## Mission
Build and maintain `dosen-farmasi`, the Laravel/MySQL lecturer portfolio application inside `farmasi-ubp-workspace`. The application owns lecturer portfolio, inbox, agenda, document metadata, notifications, reporting, and integration processing. Identity, credentials, and authoritative lecturer profile remain owned by `core-farmasi`.

## Read first
Before changing code, read these files in order:

1. `docs/CONTRACT.md`
2. `docs/EXECUTION_PLAN.md`
3. `docs/INTEGRATION_CONTRACT.md`
4. `docs/IMPLEMENT.md`
5. `docs/STATUS.md`

For complex features or significant refactors, maintain the active ExecPlan in `docs/EXECUTION_PLAN.md` from discovery through verification.

## Workspace discovery is mandatory
Before scaffolding or changing architecture:

- Inspect `core-farmasi` and at least two existing Laravel sibling applications, prioritizing `ta-farmasi` and `tu-farmasi`.
- Reuse the workspace's actual Laravel/PHP/Node versions, authentication pattern, UI stack, naming, environment-variable conventions, shared components, queue/mail setup, filesystem setup, testing stack, formatter, and deployment assumptions.
- Record discovered facts and commands in `docs/STATUS.md` and `docs/adr/0001-workspace-compatibility.md`.
- Do not assume framework versions or install a new production dependency when an established workspace solution exists.

## Non-negotiable rules

- Never copy, store, log, or rehash user passwords in the `dosen-farmasi` database.
- Access `core-farmasi` and source-application databases with read-only credentials. Never write to them from `dosen-farmasi`.
- Use the immutable internal IDs from `core-farmasi` as references. NIP and NIDN are nullable strings and never primary keys.
- Human roles are only `admin` and `dosen`. Connected applications are integration clients, not user roles.
- Enforce object-level authorization with policies for every lecturer-owned activity, inbox item, calendar event, document, report, and issue report.
- Treat records received from approved internal applications as `SYSTEM_VERIFIED`. Source-managed fields are read-only locally; corrections use an issue-report workflow or a new source event.
- Process integration events idempotently. A repeated `event_id` must never duplicate side effects.
- Store documents on the configured private shared-filesystem disk. Never expose raw paths or public symlinks.
- Use transactions for multi-record state changes. Dispatch jobs, mail, and notifications after commit.
- Keep migrations reversible and seeders idempotent. Do not put secrets or real credentials in the repository.
- Preserve audit history. Synced records are cancelled or superseded, not silently deleted.

## Working method

- Work one milestone at a time and keep diffs scoped to that milestone.
- Update `docs/STATUS.md` after each meaningful checkpoint with decisions, files changed, verification commands, and known issues.
- Fix failing validation before continuing.
- Do not expand scope beyond the contract. Record a proposed follow-up instead.
- Modify sibling applications only when the active milestone explicitly requires it; document every cross-application change and preserve backward compatibility.

## Validation
Use the exact commands discovered from sibling applications and recorded in `docs/STATUS.md`. At minimum, verify applicable items below:

- dependency installation and configuration validation;
- migrations and seeders from a clean test database;
- automated unit and feature tests;
- formatter/linter/static analysis already used by the workspace;
- frontend production build when frontend assets change;
- queue, scheduler, mail, shared-storage, and integration smoke tests;
- authorization, event replay, upload validation, and cancellation/reschedule scenarios.

## Definition of done
A milestone is complete only when its acceptance criteria pass, tests are green, documentation is current, `.env.example` is updated without secrets, and no known critical security or data-integrity defect remains.

---

# Bagian VI — Prompt Awal Codex


Gunakan prompt berikut dari root `farmasi-ubp-workspace` atau dari folder `dosen-farmasi` setelah package ini ditempatkan:

```text
Implementasikan aplikasi `dosen-farmasi` berdasarkan instruksi repository.

Baca dahulu:
- AGENTS.md
- docs/CONTRACT.md
- docs/EXECUTION_PLAN.md
- docs/INTEGRATION_CONTRACT.md
- docs/IMPLEMENT.md
- docs/STATUS.md

Mulai dari Milestone M0. Inspeksi `core-farmasi`, `ta-farmasi`, `tu-farmasi`, dan pola aplikasi saudara lain yang relevan. Jangan langsung mengasumsikan versi Laravel atau membuat scaffold dari versi terbaru. Catat hasil discovery di `docs/STATUS.md` dan selesaikan `docs/adr/0001-workspace-compatibility.md` sebelum coding fitur.

Setelah M0 tervalidasi, kerjakan milestone berurutan. Jaga diff tetap scoped, gunakan database source secara read-only, jangan menyimpan password, jalankan tests/lint/build setelah setiap milestone, perbaiki kegagalan sebelum lanjut, dan terus perbarui status serta acceptance criteria.

Untuk detail teknis yang dapat diselesaikan dari repository, buat keputusan terbaik dan catat ADR; jangan memperluas scope. Jangan gunakan credential atau data pribadi nyata dalam kode, fixture, dokumentasi, atau log.
```

---

# Bagian VII — Persetujuan dan Change Control

## Kriteria persetujuan implementasi

Kontrak dianggap diterima untuk mulai dikerjakan apabila:

- nama aplikasi dan workspace sudah benar;
- keputusan core sebagai sumber identitas diterima;
- role admin/dosen diterima;
- data internal sebagai `SYSTEM_VERIFIED` diterima;
- shared private storage dan notifikasi email/in-app diterima;
- milestone M0 discovery diwajibkan sebelum coding fitur;
- perubahan scope berikutnya dicatat melalui ADR/change log.

## Pihak yang menyetujui

| Peran | Nama | Tanggal | Catatan |
|---|---|---|---|
| Pemilik produk/kebutuhan |  |  |  |
| Penanggung jawab teknis |  |  |  |
| Reviewer keamanan/data |  |  |  |

## Change control

Setiap perubahan material harus mencatat requirement asal, perubahan, alasan, risiko, compatibility, dampak migrasi, dan test tambahan. Detail teknis yang dapat ditentukan dari repository boleh diputuskan Codex dan dicatat dalam ADR tanpa membuka kembali keputusan bisnis yang sudah dikunci.

---

# Referensi Web

Referensi berikut digunakan untuk memvalidasi pola kerja Codex, kemampuan Laravel, keamanan API/upload, dan kesiapan SISTER. Dokumentasi Laravel 13.x adalah referensi web terkini saat dokumen dibuat; implementasi tetap wajib memakai versi Laravel aktual yang ditemukan dalam workspace.

1. OpenAI — Custom instructions with AGENTS.md: https://developers.openai.com/codex/agent-configuration/agents-md
2. OpenAI — Using PLANS.md for multi-hour problem solving: https://developers.openai.com/cookbook/articles/codex_exec_plans
3. OpenAI — Run long horizon tasks with Codex: https://developers.openai.com/blog/run-long-horizon-tasks-with-codex
4. OpenAI — Codex best practices: https://developers.openai.com/codex/learn/best-practices
5. Laravel — Database / multiple connections: https://laravel.com/docs/13.x/database
6. Laravel — Authentication / custom user providers: https://laravel.com/docs/13.x/authentication
7. Laravel — Authorization / policies: https://laravel.com/docs/13.x/authorization
8. Laravel — Notifications: https://laravel.com/docs/13.x/notifications
9. Laravel — Queues / after commit: https://laravel.com/docs/13.x/queues
10. Laravel — File storage: https://laravel.com/docs/13.x/filesystem
11. Laravel — Sanctum / token abilities: https://laravel.com/docs/13.x/sanctum
12. Laravel — Task scheduling / withoutOverlapping: https://laravel.com/docs/13.x/scheduling
13. Laravel — Rate limiting: https://laravel.com/docs/13.x/rate-limiting
14. OWASP — API1:2023 Broken Object Level Authorization: https://owasp.org/API-Security/editions/2023/en/0xa1-broken-object-level-authorization/
15. OWASP — File Upload Cheat Sheet: https://cheatsheetseries.owasp.org/cheatsheets/File_Upload_Cheat_Sheet.html
16. SISTER — Informasi API SISTER (Versi Cloud): https://sister.kemdiktisaintek.go.id/pusat_informasi/detail/21829793658649
