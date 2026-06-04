-- v11.0.0 — personal to-do list per user with multi-list support.
-- Every todo belongs to one list (list_id, FK to todo_lists). Optional
-- links to patient_id / appointment_id. Lead-time + at-due reminders
-- driven by a 5-minute cron (see bin/cron/todo-reminders.php).
--
-- Note: list_id is INT UNSIGNED NULL — nullable for the brief window
-- between create-todo and create-default-list (the controller wraps
-- both in the same transaction; nullable lets us defer the FK without
-- requiring a chicken-and-egg setup migration).
--
-- Idempotent via CREATE TABLE IF NOT EXISTS.

CREATE TABLE IF NOT EXISTS todos (
    id                      INT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id                 INT UNSIGNED NOT NULL,
    list_id                 INT UNSIGNED NULL,
    title                   VARCHAR(200) NOT NULL,
    description             TEXT NULL,
    patient_id              INT UNSIGNED NULL,
    appointment_id          INT UNSIGNED NULL,
    due_at                  DATETIME NULL,
    -- NULL = no lead-time reminder. Allowed values: 15, 60, 240, 1440 minutes.
    remind_before_minutes   SMALLINT UNSIGNED NULL,
    -- Cron sets these when notifications are dispatched so we never double-send.
    todo_notified_at        DATETIME NULL,
    todo_reminded_at        DATETIME NULL,
    status                  ENUM('open','in_progress','done','cancelled')
                            NOT NULL DEFAULT 'open',
    priority                ENUM('low','med','high')
                            NOT NULL DEFAULT 'med',
    sort_order              SMALLINT NOT NULL DEFAULT 0,
    completed_at            DATETIME NULL,
    created_at              DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at              DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
                            ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_todos_user_list_status (user_id, list_id, status, sort_order),
    KEY idx_todos_user_status_due  (user_id, status, due_at),
    KEY idx_todos_user_patient     (user_id, patient_id),
    KEY idx_todos_due_remind       (due_at, remind_before_minutes, todo_reminded_at),
    KEY idx_todos_list             (list_id, status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
