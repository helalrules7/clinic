-- =====================================================================
-- Mobile API: token auth + push device registry + login rate limiting
-- 2026-06-14 (feature/mobile-api)
--
-- Adds a NATIVE mobile authentication layer that runs in PARALLEL to the
-- existing PHP-session web login (which is left completely untouched).
--
-- Security model mirrors prescription_share_tokens (2026_06_13):
--   * 256-bit random secret per token; ONLY its sha256(raw) hex is stored.
--   * Raw tokens live only on the device (secure storage) + the Bearer header.
--   * Access tokens are short-lived; refresh tokens rotate with family-based
--     reuse detection (a replayed/revoked refresh kills the whole family).
--   * Tokens are revocable server-side (logout / password change / admin).
-- =====================================================================

SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- Opaque bearer tokens (both access + refresh live here, by `type`).
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS mobile_auth_tokens (
    id             BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id        BIGINT UNSIGNED NOT NULL,
    token_hash     CHAR(64) NOT NULL,                 -- sha256(raw token) hex
    type           ENUM('access','refresh') NOT NULL,
    family_id      CHAR(36) NOT NULL,                 -- groups one login's lineage
    parent_id      BIGINT UNSIGNED NULL,              -- refresh this was rotated from
    clinic_id      BIGINT UNSIGNED NULL,              -- snapshot (NULL = all clinics)
    platform       VARCHAR(20)  NULL,                 -- ios | android | web
    device_id      VARCHAR(128) NULL,
    device_name    VARCHAR(128) NULL,
    user_agent     VARCHAR(255) NULL,
    ip_address     VARCHAR(64)  NULL,
    revoked        TINYINT(1) NOT NULL DEFAULT 0,
    revoked_reason VARCHAR(40) NULL,                  -- logout|rotated|reuse_detected|password_change|expired
    expires_at     TIMESTAMP NULL DEFAULT NULL,
    last_used_at   TIMESTAMP NULL DEFAULT NULL,
    created_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_token_hash (token_hash),
    KEY idx_user (user_id),
    KEY idx_family (family_id),
    KEY idx_type_exp (type, expires_at),
    KEY idx_user_revoked (user_id, revoked)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- Push device registry (Expo push tokens -> FCM/APNs). One row per
-- (user, device token). Multi-device by design.
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS mobile_device_tokens (
    id           BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id      BIGINT UNSIGNED NOT NULL,
    clinic_id    BIGINT UNSIGNED NULL,
    expo_token   VARCHAR(255) NOT NULL,               -- ExponentPushToken[...]
    platform     VARCHAR(20)  NULL,                   -- ios | android
    device_id    VARCHAR(128) NULL,
    revoked      TINYINT(1) NOT NULL DEFAULT 0,
    last_seen_at TIMESTAMP NULL DEFAULT NULL,
    created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_user_token (user_id, expo_token),
    KEY idx_user (user_id),
    KEY idx_revoked (revoked)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- DB-backed login rate limiting (the web throttle lives in $_SESSION and
-- is bypassable by a cookieless client, so mobile login needs its own).
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS mobile_login_attempts (
    id         BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    ip_address VARCHAR(64)  NULL,
    username   VARCHAR(150) NULL,
    outcome    VARCHAR(20)  NOT NULL,                 -- success | fail
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    KEY idx_ip_time (ip_address, created_at),
    KEY idx_user_time (username, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
