# M8 Design System

Status: Premium foundation implemented for shell, dashboard, Tridharma, profile, secondary dosen pages, demo data, and Filament admin theme.

## Product Concept

`Pharma Faculty Academic Workspace`

The interface should feel professional, academic, modern, quiet, and usable by nontechnical lecturers. The design system prioritizes clear work context over decorative effects, populated states over empty placeholders, and compact task flow over marketing-style panels.

## Tokens

Central tokens live in `resources/css/app.css`.

- Brand: `--brand-950`, `--brand-900`, `--brand-800`, `--brand-700`, `--brand-600`, `--brand-100`, `--brand-50`.
- Domain accents: `--education`, `--research`, `--service` and soft variants.
- Status: `--success`, `--warning`, `--danger`, `--info`.
- Surfaces: `--surface`, `--surface-elevated`, `--surface-muted`, `--border`.
- Text: `--text-primary`, `--text-secondary`, `--text-muted`.
- Radius: 10, 14, 18, 24.
- Shadow: subtle, floating, overlay.

Primary font stack: Plus Jakarta Sans, Inter, Instrument Sans, system sans.

## Components

Reusable Blade components were added under:

```text
resources/views/components/ui
resources/views/components/academic
```

Implemented components include buttons, icon buttons, badges, cards, section headers, empty states, SVG icons, progress ring, filter chip, stat, mobile bottom nav, page header, timeline, Tridharma domain card, activity item, academic profile header, education timeline item, career timeline item, scientific identity card, and profile completeness.

## Shell

- Desktop sidebar is sticky, grouped, and includes a lecturer mini-profile.
- Topbar uses the `Ruang Akademik` label and includes breadcrumb context, academic period, search, agenda, notification, and profile entry.
- Mobile uses five-item bottom navigation: Beranda, Tridharma, Agenda, Inbox, Profil.
- Admin-only control-room links render only for admin users.

## Filament Theme

Custom theme added at:

```text
resources/css/filament/admin/theme.css
```

Registered through `AdminPanelProvider::viteTheme()` and Vite. The admin panel keeps the existing Filament resources and applies the same brand language, density, rounded controls, and Indonesian terminology.

## Accessibility

- Skip link added.
- Focus-visible ring uses 2px brand outline.
- Controls target at least 40-44px.
- Mobile navigation avoids horizontal clipping.
- Empty states are textual and do not rely on color alone.
- Reduced motion is respected through CSS.

## Demo Data

Local-only populated UI data is created with:

```text
php artisan dosen:seed-ui-demo --clear
```

The command refuses production, marks all generated domain records with `source_app = m8-ui-demo`, and does not change integration contracts or source-app consumers.

## Current Limit

This document describes the M8 premium UI/UX implementation through secondary page redesign. Full product acceptance can continue with human visual review and any remaining academic entity CRUD expansion.
