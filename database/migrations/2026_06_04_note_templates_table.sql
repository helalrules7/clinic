-- v11.0.0 — per-user phrase library / note templates. Inserted into the
-- consultation textarea via the "Insert template" dropdown. Managed in
-- Settings → Templates. Idempotent.

CREATE TABLE IF NOT EXISTS note_templates (
    id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id     INT UNSIGNED NOT NULL,
    title       VARCHAR(120) NOT NULL,
    body        TEXT NOT NULL,
    category    VARCHAR(40) NULL,
    sort_order  SMALLINT NOT NULL DEFAULT 0,
    use_count   INT UNSIGNED NOT NULL DEFAULT 0,
    last_used_at DATETIME NULL,
    created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
                ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_nt_user_sort       (user_id, sort_order, title),
    KEY idx_nt_user_last_used  (user_id, last_used_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
