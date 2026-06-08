-- Real-time doctor↔secretary chat. Poll-based (no WebSocket): every send/edit/
-- delete/reaction bumps the conversation's rev_counter and stamps the affected
-- message's `rev`, so clients poll `WHERE rev > :cursor` and one response carries
-- new messages AND edits/deletes/reactions. See CHAT_FEATURE_PLAN.md.
SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS `chat_conversations` (
  `id`               bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `type`             enum('dm','group') NOT NULL DEFAULT 'dm',
  `title`            varchar(120) DEFAULT NULL,                 -- group name; NULL for dm
  `clinic_id`        bigint(20) unsigned DEFAULT NULL,          -- scoping hint
  `created_by`       bigint(20) unsigned NOT NULL,
  `dm_key`           varchar(40) DEFAULT NULL,                  -- "minId:maxId" for 1:1 dedupe
  `last_message_id`  bigint(20) unsigned DEFAULT NULL,
  `last_activity_at` timestamp NULL DEFAULT current_timestamp(),
  `rev_counter`      bigint(20) unsigned NOT NULL DEFAULT 0,    -- monotonic per-conversation cursor source
  `created_at`       timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_chat_dm` (`dm_key`),
  KEY `idx_chat_conv_activity` (`last_activity_at`),
  KEY `idx_chat_conv_clinic` (`clinic_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `chat_participants` (
  `id`                   bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `conversation_id`      bigint(20) unsigned NOT NULL,
  `user_id`              bigint(20) unsigned NOT NULL,
  `role`                 enum('member','admin') NOT NULL DEFAULT 'member',
  `last_read_message_id` bigint(20) unsigned DEFAULT NULL,
  `muted`                tinyint(1) NOT NULL DEFAULT 0,
  `joined_at`            timestamp NULL DEFAULT current_timestamp(),
  `left_at`              timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_chat_part` (`conversation_id`,`user_id`),
  KEY `idx_chat_part_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `chat_messages` (
  `id`              bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `conversation_id` bigint(20) unsigned NOT NULL,
  `sender_id`       bigint(20) unsigned NOT NULL,
  `body`            text DEFAULT NULL,                          -- NULL for attachment-only
  `reply_to_id`     bigint(20) unsigned DEFAULT NULL,
  `rev`             bigint(20) unsigned NOT NULL DEFAULT 0,     -- bumped on send/edit/delete/react
  `edited_at`       timestamp NULL DEFAULT NULL,
  `deleted_at`      timestamp NULL DEFAULT NULL,                -- soft-delete (poll delivers the removal)
  `created_at`      timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_chat_msg_cursor` (`conversation_id`,`rev`),
  KEY `idx_chat_msg_created` (`conversation_id`,`created_at`),
  KEY `idx_chat_msg_sender` (`sender_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `chat_attachments` (
  `id`              bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `chat_message_id` bigint(20) unsigned DEFAULT NULL,           -- NULL = staged (not yet sent)
  `user_id`         bigint(20) unsigned NOT NULL,
  `kind`            enum('image','audio','file') NOT NULL,
  `file_path`       varchar(255) NOT NULL,
  `original_name`   varchar(255) DEFAULT NULL,
  `mime_type`       varchar(120) DEFAULT NULL,
  `file_size`       int(10) unsigned DEFAULT NULL,
  `duration_ms`     int(10) unsigned DEFAULT NULL,
  `created_at`      timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_chat_att_msg` (`chat_message_id`),
  KEY `idx_chat_att_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `chat_reactions` (
  `id`         bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `message_id` bigint(20) unsigned NOT NULL,
  `user_id`    bigint(20) unsigned NOT NULL,
  `emoji`      varchar(16) NOT NULL,                            -- '👍' / '❤️' / …
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_chat_react` (`message_id`,`user_id`,`emoji`),
  KEY `idx_chat_react_msg` (`message_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- The doctor's curated contact roster (Chat-Settings window).
CREATE TABLE IF NOT EXISTS `chat_contacts` (
  `id`              bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `owner_user_id`   bigint(20) unsigned NOT NULL,
  `contact_user_id` bigint(20) unsigned NOT NULL,
  `hidden`          tinyint(1) NOT NULL DEFAULT 0,
  `created_at`      timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_chat_contact` (`owner_user_id`,`contact_user_id`),
  KEY `idx_chat_contact_owner` (`owner_user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Ephemeral typing/sending presence (TTL ~6s, expired rows ignored).
CREATE TABLE IF NOT EXISTS `chat_typing` (
  `conversation_id` bigint(20) unsigned NOT NULL,
  `user_id`         bigint(20) unsigned NOT NULL,
  `state`           enum('typing','voice','image','file') NOT NULL DEFAULT 'typing',
  `updated_at`      timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`conversation_id`,`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
