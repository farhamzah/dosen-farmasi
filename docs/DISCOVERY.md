# Discovery

Tanggal discovery: 2026-07-17 Asia/Jakarta.

## Kondisi Awal

- Root workspace: `E:\Aplikasi\farmasi-ubp-workspace`.
- Target: `apps\dosen-farmasi`.
- Folder target baru berisi `docs/Dosen-Farmasi-Planning-dan-Kontrak-Codex.md`.
- Belum ada scaffold Laravel di `apps\dosen-farmasi`.
- `git status` dari root gagal dengan `fatal: not a git repository`. Diagnosis 2026-07-17 menunjukkan `.git` adalah direktori, tetapi kosong/tidak memiliki `HEAD` dan `config`. Tidak ada environment variable `GIT*` yang terdeteksi melalui fallback .NET environment enumeration. Perubahan perlu ditinjau dengan pemeriksaan file manual sampai repository state diperbaiki.
- Tidak ditemukan `AGENTS.md`/`AGENTS.override.md` yang berlaku dari root sampai `apps\dosen-farmasi`.

## Aplikasi Saudara yang Diperiksa

- `apps/core-farmasi`
- `apps/ta-farmasi`
- `apps/tu-farmasi`
- `apps/lab-farmasi`
- `apps/kp-farmasi` dan `apps/kppspa-farmasi` teridentifikasi untuk tahap integrasi berikutnya.

## Stack Workspace

Mayoritas aplikasi baru memakai:

- PHP `^8.2`
- Laravel `^12.0`
- PHPUnit `^11.5`
- Laravel Pint
- Vite
- Tailwind CSS 4
- `laravel-vite-plugin` 2.x pada Core/TA/KP/Lab, TU masih memakai varian lebih lama
- Filament `^5.6` pada Core, TA, dan beberapa backoffice

Core juga memakai `maatwebsite/excel`. TU memakai `phpoffice/phpword`. Dependency baru untuk export/PDF tidak boleh ditambah sebelum memeriksa kebutuhan nyata.

## Core Schema yang Relevan

Temuan dari migration Core:

- `users`: `id`, `name`, `email`, `username`, `identity_type`, `identity_number`, `active`, `password`, `remember_token`, `must_change_password`, metadata password change.
- `lecturers`: `id`, `user_id`, `lecturer_number`, `national_id_number`, `nip`, `nidn`, `nidk`, `nuptk`, `name`, `front_title`, `back_title`, `email`, `department_id`, `study_program_id`, `active`.
- `user_app_accesses`: `user_id`, `app_code`, `role_slug`, `permissions`, `is_active`, `activated_at`, `deactivated_at`, unique `(user_id, app_code, role_slug)`.

Keputusan: `core_user_id` dan `core_lecturer_id` menjadi referensi utama. `lecturer_number`, NIP, dan NIDN hanya snapshot/string nullable.

## Auth dan Core Access

Pola saudara:

- TA memiliki `TaCoreBridgeAuthService` yang mencari user Core lewat email/username/identity/nomor mahasiswa/nomor dosen, memakai `Hash::check`, menolak user inactive dan `must_change_password`, lalu membaca `user_app_accesses`.
- Lab memakai `LabCoreBridgeAuthService` dengan prinsip sama.
- TU memiliki mode Core portal auth melalui DB read-only dan HTTP verify endpoint, default disabled.
- TA/TU memiliki adapter HTTP Core read-only default disabled dengan app-client header.
- Koneksi DB read-only memakai connection `core_mysql` dengan env `CORE_DB_*`.

Keputusan M1 untuk `dosen-farmasi`: login mengikuti bridge auth DB read-only seperti TA/Lab. Service `CoreBridgeAuthService` membaca koneksi `core_mysql`, memverifikasi password via `Hash::check`, menolak inactive/must-change, membaca `user_app_accesses` untuk `dosen-farmasi`, lalu membuat local `app_users` tanpa password.

## Core Internal API

Core menyediakan app-client credentials:

- Header `X-Core-Client-Id`, `X-Core-Client-Secret`, `X-Core-App-Code`.
- Directory read-only endpoints untuk users, students, lecturers, employees, study programs, departments.
- App access check endpoint.
- TU portal password verify endpoint khusus TU.
- Audit Core tidak mencatat body/secret.

Untuk `dosen-farmasi`, HTTP adapter harus default disabled dan tidak menjadi SSO.

## UI dan Admin

Core/TA/TU memakai Filament untuk backoffice dan resource administratif. TA juga memiliki portal dosen/mahasiswa berbasis controller/view lokal. Untuk MVP:

- Filament dipakai untuk admin dashboard, kategori, verifikasi, integrasi, audit, dan laporan agregat.
- Portal dosen dapat memakai route/view Laravel biasa agar object-level ownership lebih eksplisit.

## Queue, Cache, Session, Jobs

Laravel skeleton workspace menyertakan migration default `jobs`, `cache`, dan `sessions`. Composer script `dev` menjalankan `queue:listen`, `pail`, dan Vite. Queue driver final harus mengikuti env, dengan database queue sebagai fallback aman.

## File Storage

TU menekankan file private dan download via policy/controller. `dosen-farmasi` harus memakai disk private configurable, checksum SHA-256, server-generated filename, dan tidak membuat direct public URL.

## Risiko

- Workspace Git metadata tidak valid sebagai repository Git.
- Core app registry/access untuk `dosen-farmasi` belum diverifikasi pada Core nyata.
- Core app registry belum terlihat mencantumkan `dosen-farmasi`; perlu dicek/didaftarkan di Core pada milestone akses, tanpa menulis ke Core dari app ini.
- Credential Core/source tidak tersedia dan tidak boleh ditebak.

## Scaffold M1

- Laravel 12 scaffold dibuat di folder sementara `apps/dosen-farmasi-scaffold`.
- Struktur scaffold disalin ke target tanpa menimpa `AGENTS.md`, `README.md`, atau `docs/`.
- Dependency dipasang di target; Filament `v5.6.5` terkunci di lockfile dengan constraint `^5.6`.
- Folder scaffold sementara sudah dihapus setelah merge.
