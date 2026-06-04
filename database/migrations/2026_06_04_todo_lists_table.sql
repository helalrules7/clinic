-- v11.0.0 — multi-list to-do support. Each user can create any number of
-- named task lists ("Personal", "Patient follow-ups", "Inventory", etc.),
-- each with its own colour + icon + progress meter. Every todo belongs
-- to exactly one list (FK lives on the todos table — see todos migration).
--
-- A "Default" list is auto-created on first todo for any user (handled by
-- TodoController, not by this migration — keeps the migration cheap).
--
-- Idempotent.

CREATE TABLE IF NOT EXISTS todo_lists (
    id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id     INT UNSIGNED NOT NULL,
    name        VARCHAR(80) NOT NULL,
    color       VARCHAR(16) NOT NULL DEFAULT 'indigo',
    -- bi-* icon name without the prefix (e.g. "list-task", "stars", "heart-pulse")
    icon        VARCHAR(40) NOT NULL DEFAULT 'list-task',
    -- gamification copy shown next to the progress bar at different completion ratios:
    -- "Let's go!" (0–24%), "Nice start" (25–49%), "Keep it up!" (50–74%), "Almost there!" (75–99%), "All done!" (100%)
    -- Picked client-side from the static set; no copy column needed here.
    sort_order  SMALLINT NOT NULL DEFAULT 0,
    is_default  TINYINT(1) NOT NULL DEFAULT 0,
    archived_at DATETIME NULL,
    created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
                ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_tl_user_sort     (user_id, sort_order, name),
    KEY idx_tl_user_archived (user_id, archived_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
