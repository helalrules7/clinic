-- Server-side link-preview cache for chat. One row per fetched URL, deduped by
-- url_hash = sha256(normalized url). TTL is enforced in PHP against fetched_at
-- (success ~7 days, error ~30 min) — no DB event. Idempotent / re-runnable.
CREATE TABLE IF NOT EXISTS `chat_link_previews` (
  `id`          bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `url_hash`    char(64) NOT NULL,                 -- sha256(normalized url), hex
  `url`         varchar(2048) NOT NULL,
  `title`       varchar(512)  DEFAULT NULL,
  `description` varchar(1024) DEFAULT NULL,
  `image`       varchar(2048) DEFAULT NULL,
  `site_name`   varchar(255)  DEFAULT NULL,
  `status`      enum('ok','error') NOT NULL DEFAULT 'ok',
  `fetched_at`  timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_chat_link_url` (`url_hash`),
  KEY `idx_chat_link_fetched` (`fetched_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
