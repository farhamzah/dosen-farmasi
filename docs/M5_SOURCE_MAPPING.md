# M5 Source Mapping

## Source Apps

| Canonical app | Folder | Pull connection env prefix | Notes |
| --- | --- | --- | --- |
| `kp-farmasi` | `apps/kp-farmasi` | `KP_DB_*` | KP assignment dan exam lifecycle. |
| `kp-pspa` | `apps/kppspa-farmasi` | `KP_PSPA_DB_*` | Folder legacy bernama `kppspa-farmasi`. |
| `lab-farmasi` | `apps/lab-farmasi` | `LAB_DB_*` | M5 memakai outbox canonical karena schema schedule dosen belum eksplisit. |
| `ta-farmasi` | `apps/ta-farmasi` | existing push kernel | M5 menambah assignment supervisor/examiner. |

## Outbox Canonical

Pull adapter membaca tabel sumber:

`dosen_integration_outbox`

Kolom minimum:

- `id`
- `event_id`
- `event_type`
- `event_version`
- `source_app`
- `source_record_id`
- `source_revision`
- `occurred_at`
- `payload`
- `created_at`
- `updated_at`

`payload` berisi JSON payload canonical. Adapter tidak membaca token dari source DB dan tidak menulis ke source DB.

## Cursor

`integration_sync_cursors` menyimpan:

- `source_app`
- `adapter`
- `last_synced_at`
- `last_source_id`
- `last_updated_at`
- `status`
- `last_error`

Cursor maju hanya setelah row outbox berhasil diingest. Mode `--dry-run` menghitung row dan tidak membuat `integration_events`.

## Aggregate Mapping

| Event class | Target aggregate |
| --- | --- |
| Assignment supervisor/examiner/preceptor | `inbox_items` + `inbox_recipients` |
| Schedule/reschedule | `calendar_events` + `calendar_event_attendees` |
| Completed/finalized | `calendar_events` completed dan `portfolio_activities` `SYSTEM_VERIFIED` |
| Cancelled | `calendar_events`/`inbox_items` cancelled, history dipertahankan |

Uniqueness memakai `source_app`, `source_entity`, `source_record_id`, `lecturer_core_id`, dan `source_revision`.
