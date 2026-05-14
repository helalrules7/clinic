# Financial / Accounting Overhaul — Deferred Plan

**Status:** Deferred. Not active work. Revisit when the user signals readiness.

This document combines two related but distinct workstreams:

1. **Restructuring** the accounting layer so each clinic (الرياض / كفر الشيخ) keeps its own books while doctors retain cross-clinic visibility.
2. **Fixing existing bugs** in the statistics cards / dashboard totals that prevent newly created expenses and payments from showing up correctly.

---

## Context snapshot (when this plan was written)

- Two clinics seeded in `clinics` table: `riyadh` (id=1), `kfs` (id=2).
- Migration **026** added `clinic_id` to `appointments` and `patients`.
- Migration **027** added `clinic_id` to `users`, replaced the old `sec` account with `sec_riyadh` and `sec_kfs` (both clinic-scoped, password `sec123`).
- Per-clinic enforcement is already live for **patient creation, appointment creation, and per-booking actions** (delete / confirm / edit / view). Secretaries see only their own clinic's bookings.
- Patient lists are **intentionally shared** across clinics — a patient registered in Riyadh can also book at Kafr El-Sheikh.
- **The financial tables (`payments`, `expenses`, `invoices`, `daily_balances`, `daily_closures`) have not yet been touched.** They still operate as if there's one clinic.
- All financial tables are nearly empty (`payments` 0, `expenses` 1, `invoices` 0, `daily_balances` 1, `daily_closures` 3). This is the right moment to restructure — risk of data loss is minimal.

---

## Part A — Clinic-scoped accounting (structural restructure)

### A.1 Current schema gaps

| Table | clinic_id? | Today's owner field | Gap |
|---|---|---|---|
| `payments` | ❌ | `received_by` user + optional `appointment_id` | No way to aggregate by clinic without going through appointments (which can also be NULL) |
| `expenses` | ❌ | `created_by` user only | Cannot attribute an expense to a clinic |
| `invoices` | ❌ | `doctor_id` (optional) | Cross-clinic doctors break aggregation |
| `daily_balances` | ❌ | `created_by` only | Opening / additional / withdrawal / closing balance is currently **global** — both clinics share one cash drawer in the data model |
| `daily_closures` | ❌ | UNIQUE on `date` alone | Cannot close two clinics' days separately — the existing UNIQUE constraint actually blocks it |

### A.2 Migration 028 (proposed)

```sql
ALTER TABLE payments
    ADD COLUMN clinic_id BIGINT UNSIGNED NULL AFTER patient_id,
    ADD KEY idx_payments_clinic (clinic_id),
    ADD CONSTRAINT fk_payments_clinic FOREIGN KEY (clinic_id) REFERENCES clinics(id);

ALTER TABLE expenses
    ADD COLUMN clinic_id BIGINT UNSIGNED NULL AFTER category,
    ADD KEY idx_expenses_clinic (clinic_id),
    ADD CONSTRAINT fk_expenses_clinic FOREIGN KEY (clinic_id) REFERENCES clinics(id);

ALTER TABLE invoices
    ADD COLUMN clinic_id BIGINT UNSIGNED NULL AFTER doctor_id,
    ADD KEY idx_invoices_clinic (clinic_id),
    ADD CONSTRAINT fk_invoices_clinic FOREIGN KEY (clinic_id) REFERENCES clinics(id);

ALTER TABLE daily_balances
    ADD COLUMN clinic_id BIGINT UNSIGNED NULL AFTER balance_type,
    ADD KEY idx_daily_balances_clinic (clinic_id),
    ADD CONSTRAINT fk_daily_balances_clinic FOREIGN KEY (clinic_id) REFERENCES clinics(id);

ALTER TABLE daily_closures
    ADD COLUMN clinic_id BIGINT UNSIGNED NULL AFTER doctor_id,
    DROP INDEX date,                                          -- removes the wrong unique constraint
    ADD UNIQUE KEY uq_closure (clinic_id, doctor_id, date),   -- per-clinic, optionally per-doctor
    ADD KEY idx_daily_closures_clinic (clinic_id),
    ADD CONSTRAINT fk_daily_closures_clinic FOREIGN KEY (clinic_id) REFERENCES clinics(id);

-- Backfill existing rows to clinic 1 (Riyadh as the historical default).
UPDATE payments       SET clinic_id = 1 WHERE clinic_id IS NULL;
UPDATE expenses       SET clinic_id = 1 WHERE clinic_id IS NULL;
UPDATE invoices       SET clinic_id = 1 WHERE clinic_id IS NULL;
UPDATE daily_balances SET clinic_id = 1 WHERE clinic_id IS NULL;
UPDATE daily_closures SET clinic_id = 1 WHERE clinic_id IS NULL;
```

Columns stay NULL-able to avoid breaking any forgotten write paths during rollout. After verification, a follow-up migration can flip them to `NOT NULL`.

### A.3 Write-side rules (where does `clinic_id` come from)

| Action | Source of `clinic_id` |
|---|---|
| Payment tied to an appointment | Inherit from `appointment.clinic_id` |
| Manual payment (no appointment) | The secretary's `users.clinic_id` |
| Expense | The secretary's `users.clinic_id` |
| Invoice | From the linked appointment, or from the creator's clinic when manual |
| Daily balance entry (opening / additional / withdrawal / closing) | The secretary's `users.clinic_id` |
| Daily closure | The secretary's `users.clinic_id` |

Enforce on the server (like the existing pattern in `ApiController@createAppointment` and `SecretaryController@createBooking`). Never trust a `clinic_id` sent from the client when the user is a secretary.

### A.4 Read-side rules

**Secretary** — every list and stat is scoped:
- Payments, expenses, invoices, daily balances, daily closures → filtered by their `clinic_id`.
- Dashboard totals → only their clinic.

**Doctor / admin** — full visibility plus a clinic filter:
- Each financial page gets a clinic selector at the top: "كل العيادات / الرياض / كفر الشيخ".
- Reports support `group by clinic` so cross-clinic comparison is possible without leaving the page.
- The daily-closure view shows one card per clinic per day.

### A.5 Edge cases worth deciding

1. **`doctor_schedule` / `doctor_settings`** are not yet clinic-aware. If a doctor works different hours in different clinics, those tables also need `clinic_id`. For now, doctor schedules are global → time slots are globally unique to prevent double-booking the same doctor across clinics.
2. **Discount / exempt approval** (`payments.approval_user_id`) — currently any doctor can approve. Decide whether approval must come from the same clinic.
3. **Inter-clinic transfers** (Riyadh secretary takes 200 EGP from Kafr El-Sheikh's drawer) — out of scope for this iteration. If needed, model as a withdrawal in one + opening in the other with a shared reference.

---

## Part B — Stats cards / dashboard bugs (audit findings)

The user reports that stats cards don't reflect newly added expenses and payments. An audit confirmed several concrete bugs; they're listed below in priority order.

### Bug #1 — Payment type structure mismatch between page render and AJAX refresh ⚠️ HIGH IMPACT

**Where:**
- `app/Controllers/SecretaryController.php:1835-1847` (used on page render — maps `Booking → new_booking`, `FollowUp → followup`, etc.)
- `app/Controllers/ApiController.php:9062` (`getPaymentTypesSummary` — returns raw DB values: `"Booking"`, `"FollowUp"`, …)
- `app/Views/secretary/payments.php:1707` (JS expects normalized keys like `new_booking`)

**What's wrong:** After creating a payment, the frontend calls `/api/dashboard-summary`, which goes to `ApiController`. That endpoint returns raw payment-type strings, but the card element IDs were built from the normalized names. The DOM update silently fails — the cards freeze on the old values.

**Fix:** Apply the same `CASE` mapping used in `SecretaryController` inside `ApiController::getPaymentTypesSummary`. Trivial.

### Bug #2 — Inconsistent payment-type normalization layer

Same root cause as Bug #1 but worth calling out: the mapping should live in **one** helper that both controllers call. Right now there are two slightly different copies.

### Bug #3 — `createPaymentRecord` omits `created_at`; relies on DB default ⚠️ TIMEZONE TRAP

**Where:** `app/Controllers/ApiController.php:2706-2723`

**What's wrong:** The INSERT does not include `created_at`, so MariaDB uses its session default. If the DB session timezone drifts from the app's `Africa/Cairo`, a payment created at 11:30 PM Cairo can land on the next calendar date in storage. Stats queries that filter `WHERE DATE(created_at) = CURDATE()` then silently exclude it.

**Fix:** Either `INSERT … created_at = NOW()` explicitly (uses PHP timezone since PHP issues `NOW()`), or pin the DB session: `SET time_zone = '+02:00'` at connect time. Medium difficulty.

### Bug #4 — `getDailyBalance` SUMs ignore `is_exempt` and `discount_amount`

**Where:**
- `app/Controllers/SecretaryController.php:1776-1782`
- `app/Controllers/ApiController.php:8492-8498`

**What's wrong:** Both queries `SUM(amount)` without subtracting `discount_amount` and without excluding rows with `is_exempt = 1`. A 500 EGP payment with a 100 EGP discount shows on the card as 500, not the net 400 actually received.

**Fix:** Decide the policy with the user (gross vs. net), then update the SUM. Both queries must match. Medium.

### Bug #5 — Expense modal doesn't pre-fill `expense_date`

**Where:** `app/Controllers/ApiController.php:7193`

**What's wrong:** Backend correctly falls back to `NOW()` if the form omits `expense_date`. The modal UI, though, doesn't pre-populate the date input, so users often submit without setting it — fine in isolation, but inconsistent with the daily-balance modal which does pre-fill. Causes user confusion, not a real data bug.

**Fix:** Pre-populate the input. Trivial cosmetic.

### Bug #6 — Stats card SUM filter ≠ transactions list filter

**Where:**
- `app/Views/secretary/payments.php:1464-1476` (transactions list calls `/api/financial-transactions`)
- Stats card calls `/api/dashboard-summary`

**What's wrong:** The two endpoints don't use identical WHERE clauses. Result: the "total" card and the sum of rows in the transactions table can disagree.

**Fix:** Pull the WHERE clause into a shared helper (e.g. `Payments::scopeForDay($date, $clinic = null)`) and call it from both endpoints. Medium.

---

## Part C — Recommended execution order

Phase **0** (no-cost, makes the rest safer):
1. Fix Bugs #1, #2, #3 first — they're hot paths that affect both single-clinic and multi-clinic operation.
2. Decide gross-vs-net policy with the user, then fix Bug #4.

Phase **1** — Migration 028 + write-side enforcement (Part A.2 + A.3). Low risk because financial tables are empty.

Phase **2** — Read-side filtering for secretaries (Part A.4 — secretary half). Hide everything outside their clinic; check that all stat endpoints honor the same filter.

Phase **3** — Doctor-side clinic toggle on every financial page + per-clinic daily closure UI (Part A.4 — doctor half). The bigger UI lift.

Phase **4** — Cleanup pass: flip `clinic_id` to `NOT NULL` on financial tables once Phase 1-3 are verified; remove the doctor-side "all clinics" fallback queries that are no longer needed for backward compat.

---

## Part D — Things explicitly out of scope (for this plan)

- Multi-currency support beyond the existing `currency` column (today everything is EGP).
- Cost-center accounting per doctor (would need its own table and is orthogonal to clinic separation).
- Tax / VAT handling.
- Audit log for financial mutations (the `audit_logs` table exists but isn't used by the financial code paths).

These can be tackled separately if/when needed.
