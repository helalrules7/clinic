# v12 — Backup hub (doctor + admin Settings)

**Date:** 2026-06-12 · **Branch:** v_12_perf · **Shipped to prod:** yes

Three backup types, generated SERVER-SIDE into a private folder and listed for download. **No restore.**
Replaces the old `/admin/backup` page (now redirects to `/doctor/settings#backupSection`).

## Types
1. **Database** — main DB + drugs DB. A modal first lets you pick scope: **main / drugs / both**.
2. **Database + uploads** — both DBs + all attachments/uploaded files (`app/storage/uploads`, `public/uploads`, `uploads`).
3. **Full system** — both DBs (in `database/`) + the entire `public_html/` + a **cron-jobs.txt** snapshot, compressed.

## How it works
- **`bin/backup-run.php`** (background CLI) does the heavy lifting so big archives (prod ~1.1 GB) never time out a
  web request. SQL dumps are **pure PHP/PDO** (mysqldump CLI fails for the app DB user on prod; PDO works).
  Writes progress to `private/backups/.status/<job>.json` and **notifies every doctor/admin** when ready.
- **`BackupController`** (`/api/backup/*`, doctor+admin, CSRF): `create` spawns the runner (auto-detects the php
  CLI via `command -v php`), `status` (progress polling), `list`, `download` (path-traversal guarded streaming),
  `delete`.
- **UI** (`doctor/settings.php` → Backup section + `settings.js`): 3 cards, the DB-scope modal, a progress bar
  (polls `status`), and a list of available backups with download/delete.

## SECURITY — backups live OUTSIDE the web docroot
nginx serves the docroot and **ignores `.htaccess`**, and the panel locks the parent dir read-only, so a
`public_html/backups` folder would be publicly downloadable. The panel's **`private/`** dir is in the FPM
`open_basedir`, writable by the web user, and **not** served by nginx → backups are stored in
`…/roaya.hclinic.clinic/private/backups`. Verified: that path returns 404 on the web; downloads work only via the
authenticated PHP endpoint. (Local dev falls back to `<app>/backups`, which is outside the local `/public` docroot.)

### One-time cleanup done at deploy
The old `public_html/backups/` held the pre-deploy DB dumps **and was publicly downloadable**. They were moved to
`private/backups/` and `public_html/backups` was removed.
**ACTION FOR THE OWNER:** purge the Cloudflare cache for `/backups/*` — one old dump
(`predeploy_db_2026-05-23_022019.sql.gz`) is still served from Cloudflare's edge cache
(`cf-cache-status: HIT`, max-age 10y) even though the origin now returns 404.

## Files
NEW: `bin/backup-run.php`, `app/Controllers/BackupController.php`, Backup section in `doctor/settings.php`,
backup JS in `settings.js`. Routes in `public/index.php` (adopted as prod root `index.php`).
Changed: `AdminController::backup()` → redirect.

## Follow-ups
- **prod env gotcha:** `shell_exec`/`proc_open`/`popen`/`system` are **disabled** on prod (only `exec` is
  allowed) — the php-CLI probe + the background spawn use `exec()`. And the prod `.env` has **no `DRUGS_DB_*`
  keys**, so the runner's drugs defaults must match `DoctorController::getDrugsDatabaseConnection()`
  (`hclinic_drugs` / `Carmen@1230` / `hclinic_drugs`) — not the main user.
- **Stuck-job cleanup:** a runner that dies before updating its status used to leave a permanent "in progress"
  entry. `list()` now drops any `running` status file untouched for >2h (and stale ones were cleared on the
  server). `.part` (incomplete) archives are excluded from the list.
- **UI dialogs:** the Backup section's errors and the delete confirmation now use the app modal kit
  (`mkAlertModal` / `mkConfirmModal`) instead of native `alert()` / `confirm()`.
