# Mission
Build `dosen-farmasi` as the Laravel/MySQL lecturer portfolio app inside `farmasi-ubp-workspace`.

# Read First
1. `docs/CONTRACT.md`
2. `docs/DISCOVERY.md`
3. `docs/EXECUTION_PLAN.md`
4. `docs/INTEGRATION_CONTRACT.md`
5. `docs/IMPLEMENT.md`
6. `docs/STATUS.md`

# Non-Negotiables
- `core-farmasi` owns users, passwords, account status, and canonical lecturer profiles.
- Never store, copy, log, queue, or expose Core passwords, password hashes, remember tokens, API tokens, or app-client secrets.
- Use Core internal IDs as references. NIP/NIDN are nullable strings only.
- Human roles in this app are only `admin` and `dosen`; connected apps are integration clients.
- Source app databases are read-only from this app. Do not write to Core, TU, TA, KP, KP PSPA, Lab, or other source databases.
- Store documents on a private filesystem disk and authorize every download.
- Enforce object-level policies for lecturer-owned records.
- Process integration events idempotently and preserve history.

# Working Method
Start with M0 discovery, then implement milestones in `docs/EXECUTION_PLAN.md`. Keep changes scoped to `apps/dosen-farmasi` unless an integration milestone explicitly requires a backward-compatible sibling-app change.
