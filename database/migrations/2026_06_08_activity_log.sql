-- Real activity log: records every appointment action with the ACTUAL actor
-- (who performed it), so the activity feed can attribute correctly ("You" for the
-- logged-in actor, incl. secretaries) and so a hard-deleted appointment still leaves
-- an audit trail. Replaces the derived-from-appointments feed for appointment events.
CREATE TABLE IF NOT EXISTS activity_log (
    id            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    actor_user_id BIGINT UNSIGNED NULL,                 -- who did it (users.id)
    action        VARCHAR(40)     NOT NULL,             -- booked / status_changed / deleted / rescheduled / edited / checked_in
    entity_type   VARCHAR(30)     NOT NULL DEFAULT 'appointment',
    entity_id     BIGINT UNSIGNED NULL,                 -- appointment id (may be gone after a hard delete)
    patient_id    BIGINT UNSIGNED NULL,
    doctor_id     BIGINT UNSIGNED NULL,                 -- doctors.id (assigned doctor, for display)
    clinic_id     BIGINT UNSIGNED NULL,                 -- for clinic scoping of the feed
    detail        VARCHAR(255)    NULL,                 -- e.g. the new status, or "to 2026-06-08 14:00"
    created_at    TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_clinic_created (clinic_id, created_at),
    KEY idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
