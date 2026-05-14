-- Migration 027: per-clinic secretary scoping
--
-- 1. Adds `clinic_id` to `users` so a secretary belongs to a single clinic.
--    Doctors/admins keep NULL = access to all clinics (cross-clinic visibility).
-- 2. Replaces the legacy single `sec` account with two clinic-scoped secretaries:
--      sec_riyadh -> clinic 1 (الرياض)
--      sec_kfs    -> clinic 2 (كفر الشيخ)
--    Default password for both: `sec123` (bcrypt below).
--    The old `sec` account has no FK references so deletion is safe.

ALTER TABLE users
    ADD COLUMN clinic_id BIGINT UNSIGNED DEFAULT NULL AFTER role,
    ADD KEY idx_users_clinic (clinic_id),
    ADD CONSTRAINT fk_users_clinic
        FOREIGN KEY (clinic_id) REFERENCES clinics(id)
        ON UPDATE CASCADE ON DELETE RESTRICT;

DELETE FROM users WHERE username = 'sec';

INSERT INTO users (name, username, email, password_hash, role, clinic_id, is_active)
VALUES
    ('سكرتارية - عيادة الرياض',     'sec_riyadh', 'sec.riyadh@roayaclinic.com',
     '$2y$10$K5rpiloGYsLipy5qQJYtaeS9B8F3wt2wqqRYEvUw9bjN2Ka2k04pa',
     'secretary', 1, 1),
    ('سكرتارية - عيادة كفر الشيخ', 'sec_kfs',    'sec.kfs@roayaclinic.com',
     '$2y$10$K5rpiloGYsLipy5qQJYtaeS9B8F3wt2wqqRYEvUw9bjN2Ka2k04pa',
     'secretary', 2, 1);
