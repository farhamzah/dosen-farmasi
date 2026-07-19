# M8 Information Architecture

Status: Updated for premium UI slice.

## Canonical Role Entry

- Dosen login with a single role redirects to `/dosen/dashboard`.
- Admin login with a single role redirects to `/admin`.
- Users with both roles must choose role at `/pilih-role`.
- Choosing `Admin` redirects to the Filament dashboard route `filament.admin.pages.admin-dashboard`.
- Choosing `Dosen` redirects to `dosen.dashboard`.

## Admin IA

Canonical admin workspace:

```text
/admin
```

Legacy compatibility route:

```text
/admin/dashboard -> /admin
```

Admin resources remain under the existing Filament panel. No duplicate panel or resource was created.

Key admin navigation remains Filament-owned:

- Integration clients
- Integration events
- Integration failures
- Sync cursors
- Portfolio activities
- Documents
- App users
- Audit logs
- Application settings

## Dosen IA

Canonical dosen workspace:

```text
/dosen/dashboard
```

Primary navigation:

- Dashboard
- Tridharma
- Portofolio
- Dokumen
- Inbox
- Agenda
- Profil Akademik
- Notifikasi

Admin users who intentionally enter the dosen shell see `Ruang Kontrol Admin`; dosen users do not.

## Tridharma IA

Canonical Tridharma workspace:

```text
/tridharma
/tridharma/pendidikan
/tridharma/penelitian
/tridharma/pengabdian
```

The page is category-backed, not a single generic portfolio list with a hidden filter. It maps existing database categories:

- `pendidikan-dan-pengajaran`
- `penelitian-dan-pengembangan`
- `pengabdian-kepada-masyarakat`

## Academic Profile IA

Canonical academic profile workspace:

```text
/profil
```

Public profile route:

```text
/profil-publik/{lecturerCoreId}
```

Admin academic profile resources live inside the existing Filament admin panel under the `Profil Akademik` navigation group.
