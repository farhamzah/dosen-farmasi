# M8 Tridharma and Academic Profile Report

Status: Substantial M8 slice implemented and validated. M8 is not final-complete yet.

## Implemented

- Added first-class Tridharma navigation and routes:
  - `/tridharma`
  - `/tridharma/pendidikan`
  - `/tridharma/penelitian`
  - `/tridharma/pengabdian`
- Added Tridharma page-level navigation:
  - Ringkasan
  - Pendidikan
  - Penelitian
  - Pengabdian
- Added premium Tridharma UI:
  - header summary;
  - verified progress;
  - active year/semester signal;
  - per-domain totals;
  - verified counts;
  - needs-completion counts;
  - source/system and manual sections;
  - latest activities;
  - yearly summary;
  - empty states explaining KP, TA, TU, KP PSPA, and Lab sources.
- Added academic profile route:
  - `/profil`
- Added profile page navigation:
  - Ringkasan
  - Identitas
  - Riwayat Pendidikan
  - Karier dan Jabatan
  - Bidang Keilmuan
  - Sertifikasi
  - Identitas Ilmiah
  - Penghargaan
  - Organisasi
  - Profil Publik
- Added profile completeness checklist and safe visibility settings.
- Added education vertical timeline UI and create/update/delete routes.
- Added external identifier form with unsafe URL rejection.
- Added public profile route:
  - `/profil-publik/{lecturerCoreId}`
- Added local academic profile tables:
  - `lecturer_educations`
  - `lecturer_functional_positions`
  - `lecturer_structural_positions`
  - `lecturer_employments`
  - `lecturer_expertise_areas`
  - `lecturer_certifications`
  - `lecturer_external_identifiers`
  - `profile_visibility_settings`
- Added models, policy, and services for the above profile foundation.
- Added Filament resources:
  - Pendidikan Dosen
  - Jabatan Fungsional
  - Jabatan Struktural
  - Riwayat Pekerjaan
  - Bidang Keilmuan
  - Sertifikasi
  - Identitas Ilmiah
- Added admin education verification endpoint and Filament education verify action.

## Security and Privacy

- Core remains source of truth for identity.
- Dosen Farmasi stores profile extension data only.
- No cross-database foreign keys were added.
- SD/SMP/SMA/SMK education defaults to `PRIVATE`.
- Higher education defaults to `INTERNAL`.
- Linked education documents are forced to `PRIVATE`.
- Public profile does not show NIP/NIDN, NIK, address, personal phone, document numbers, or private education rows.
- Unsafe `javascript:` profile URLs are rejected.
- Technical source record identifiers are not rendered on the dosen profile UI.

## Validation

- `php artisan migrate --force`: PASS.
- Changed-file Pint: PASS.
- `php artisan test --filter=M8TridharmaProfileTest`: PASS, 8 tests, 41 assertions.
- `php artisan test`: PASS, 64 tests, 284 assertions.
- `php artisan route:list --except-vendor`: PASS, 78 routes.
- `npm.cmd run build`: PASS.

## Browser QA

Runtime:

```text
http://localhost:3002
```

Checked:

- `/tridharma`: PASS.
- `/tridharma/pendidikan`: PASS.
- `/tridharma/penelitian`: PASS.
- `/tridharma/pengabdian`: PASS.
- `/profil`: PASS.
- Dosen denied from `/admin/lecturer-educations`: PASS.
- Admin resources:
  - `/admin/lecturer-educations`: PASS.
  - `/admin/lecturer-functional-positions`: PASS.
  - `/admin/lecturer-expertise-areas`: PASS.
  - `/admin/lecturer-certifications`: PASS.
  - `/admin/lecturer-external-identifiers`: PASS.
- Viewports:
  - 1280 desktop runtime: PASS.
  - 768 x 1024: PASS.
  - 390 x 844: PASS.
- Horizontal overflow: NONE observed.
- Console errors: NONE observed.

Screenshots:

```text
storage/app/m8-ui-qa/tridharma-desktop.png
storage/app/m8-ui-qa/profil-desktop.png
storage/app/m8-ui-qa/tridharma-pendidikan-tablet.png
storage/app/m8-ui-qa/profil-tablet.png
storage/app/m8-ui-qa/tridharma-pendidikan-mobile.png
storage/app/m8-ui-qa/profil-mobile.png
```

## Still Not Final M8

- Secondary CRUD pages outside `/tridharma` and `/profil` still need premium redesign.
- Full create/edit forms for jabatan, expertise, certifications, and identifiers can be expanded beyond this initial usable slice.
- Admin verification/revision history for every academic profile entity should be broadened beyond education.
- M7 official document-reference acceptance remains separately pending.
- No commit or push has been performed.
