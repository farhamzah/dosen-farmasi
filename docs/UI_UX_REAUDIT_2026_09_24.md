# UI/UX Re-audit - 24 September 2026

## Scope

Improve existing lecturer and administrator workflows without adding a module,
changing KP/TA integration contracts, or touching production data. Work is local,
uncommitted, and not deployed. Visual acceptance remains with the project manager.

## Findings Addressed

- Oversized dashboard and page headers pushed useful content below the fold.
  Replaced them with compact headings, actionable metrics, and task lists.
- Profile sections formed a long, confusing page. Each navigation item now opens
  one section, with direct edit links and a visible three-template CV picker.
- Profile saves returned to the summary. Education, scientific identifiers,
  and visibility now return to their corresponding section.
- Validation could hide the failed form and lose the selected visibility.
  Forms recover their own values, expand the affected section, and show Indonesian
  field errors. Delete operations use a shared confirmation dialog.
- Mobile navigation omitted useful destinations. The menu now exposes the complete
  lecturer navigation and role switching for authorized multi-role users.
- Automatic table/card mode and its selected control could disagree. Desktop and
  tablet default to tables, mobile to cards, with explicit alternatives retained.
- Filter panels lacked reliable modal behavior and labels. Native dialogs provide
  focus containment, Escape dismissal, and focus return. Applied filters have
  human-readable removable chips.
- Empty activity groups could render an empty table instead of an empty state.
- Tridharma summary counts could disagree with filters and count manual demo rows
  as system rows. Summary queries now share the activity filters and exact source type.
- Document totals were calculated from one page. Totals now cover the owner's full
  document set; search and pagination remain scoped to that owner.
- Notes/source identifiers appeared under SKS, indexing, HKI numbers, or ISBN.
  Those misleading fallbacks were removed from tables and exports. Missing values
  remain blank markers; no domain schema or integration payload was expanded.
- Admin navigation lacked grouping and used oversized nested surfaces. Existing
  resources are grouped by responsibility and use a compact shared theme.
- CV previews now use restrained document layouts, distinct template typography,
  a direct return to the template picker, and print-specific A4 styles.

## Automated Verification

| Check | Result |
| --- | --- |
| composer validate --no-check-publish | Passed |
| Changed PHP files: Pint and Pint --test | Passed |
| Full php artisan test | 91 passed, 586 assertions |
| route:list --except-vendor | Passed, 95 routes |
| npm run build | Passed |
| git diff --check | Passed |
| Changed-file credential-pattern scan | No matches |
| QA database, screenshots, logs, .env, vendor, node_modules ignore check | Passed |

Regression coverage includes summary filtering/source counts, all domain table row
structures, document ownership/counts/search, validation recovery, CV templates,
filter labels, and suppression of private notes/source IDs in domain exports.
Existing authorization, profile visibility, and integration lifecycle tests pass.

## Browser Verification

Browser used an isolated local SQLite database on port 3004. The ordinary local
database and all source/production databases were not changed. Dummy seed data was
used only in this isolated environment; QA files stay under ignored storage/app.

Populated-page checks at 1440x900, 768x1024, and 390x844 covered:

- Lecturer dashboard, Tridharma summary, education, research, community service.
- Portfolio index and create form.
- Documents, inbox, agenda, notifications, academic profile.

All 36 page/viewport checks reached the expected page without horizontal page
overflow. Additional checks covered admin applications at all three sizes,
admin dashboard, role switching, portfolio row action/detail/edit, all three CV
previews, and mobile CV layout. Final captured browser console had no errors or
warnings. This is not a complete production network trace or a live Core audit.

Interaction checks:

- Profile tabs, edit disclosure, and CV return link reach the intended section.
- Invalid education year keeps entered fields and PUBLIC visibility; the Indonesian
  validation message is visible. Correcting and saving returns to Education.
- Mobile defaults to cards; explicit table mode remains usable without page overflow.
- Filter dialog contains keyboard focus, closes with Escape, and returns focus.
- Mobile navigation contains all destinations and the authorized role switch.
- Row action popovers provide detail/edit without clipping inside the table scroller.
- Empty dashboard/profile states were checked before seeding; empty table and
  filtered document behavior also have automated coverage.

## Evidence and Limits

Local screenshots (intentionally excluded from Git):

- storage/app/ui-reaudit/desktop-profile-cv.png
- storage/app/ui-reaudit/desktop-penelitian-table.png
- storage/app/ui-reaudit/desktop-admin-applications.png
- storage/app/ui-reaudit/mobile-penelitian-cards.png
- storage/app/ui-reaudit/mobile-filter-drawer.png

No production login, production CRUD, live source synchronization, or real-data
export was performed. Print CSS was added, but generated PDF pagination has not
been independently visually approved. Not every Filament resource CRUD action was
repeated in the browser; existing feature tests cover authorization and page loading.
Some domain-specific metadata still uses the existing generic activity model;
adding dedicated funding/publication/rights metadata is outside this UI-only scope.

Commit: not created. Push: not performed. Deployment: not performed.
