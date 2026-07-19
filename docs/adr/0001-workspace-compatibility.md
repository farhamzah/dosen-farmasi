# ADR 0001 - Workspace Compatibility

Date: 2026-07-17

## Status

Accepted for implementation baseline.

## Context

`dosen-farmasi` must follow the Farmasi UBP workspace instead of using a fresh unrelated Laravel stack. Existing apps include `core-farmasi`, `ta-farmasi`, `tu-farmasi`, `kp-farmasi`, `kppspa-farmasi`, and `lab-farmasi`.

Discovery found that the target folder has only planning documents and no scaffold. Git CLI does not currently recognize the root as a repository.

## Decision

Use the workspace baseline:

- PHP `^8.2`
- Laravel `^12.0`
- MySQL
- Filament `^5.6` for admin/backoffice where needed
- Tailwind CSS 4 and Vite for assets
- PHPUnit 11 and Laravel Pint
- Core DB read-only connection named `core_mysql`
- Optional Core HTTP read-only adapter default disabled

Authentication will follow TA/Lab bridge-auth pattern:

- find active Core user by safe login identifiers;
- reject inactive and `must_change_password`;
- verify password via `Hash::check` against Core;
- require active `user_app_accesses` for `dosen-farmasi`;
- provision local app user/reference without password.

## Consequences

- `dosen-farmasi` must not scaffold with a newer Laravel version just because external docs are newer.
- M1 must include a careful scaffold merge because target docs already exist.
- Core app registry/access for `dosen-farmasi` may need environment/Core admin preparation outside this app.
- Tests should fake Core/source connections where possible and avoid production databases.

## Verification

Discovery commands and source files are recorded in `docs/STATUS.md`.
