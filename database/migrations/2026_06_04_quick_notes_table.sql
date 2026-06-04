-- v11.0.0 — quick notes scratchpad. Powered by the "New note" quick-action
-- button in the notification center footer. Each user owns their own.
-- Idempotent.

CREATE TABLE IF NOT EXISTS quick_notes (
    id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id     INT UNSIGNED NOT NULL,
    title       VARCHAR(200) NULL,
    body        TEXT NOT NULL,
    pinned      TINYINT(1) NOT NULL DEFAULT 0,
    created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
                ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_qn_user_created (user_id, created_at),
    KEY idx_qn_user_pinned  (user_id, pinned, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
