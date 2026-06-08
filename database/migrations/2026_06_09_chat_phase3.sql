-- Chat Phase 3b: pin messages. A pinned message has pinned_at set (and pinned_by
-- = who pinned it). Multiple messages can be pinned per conversation; list them
-- with `WHERE conversation_id = ? AND pinned_at IS NOT NULL`.
ALTER TABLE `chat_messages`
  ADD COLUMN `pinned_at` timestamp NULL DEFAULT NULL AFTER `deleted_at`,
  ADD COLUMN `pinned_by` bigint(20) unsigned DEFAULT NULL AFTER `pinned_at`,
  ADD KEY `idx_chat_msg_pinned` (`conversation_id`, `pinned_at`);
