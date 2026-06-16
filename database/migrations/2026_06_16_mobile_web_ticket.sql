-- ---------------------------------------------------------------------
-- Mobile → web auto-login: add a short-lived, single-use "web_ticket" token
-- type to mobile_auth_tokens. The app mints one (Bearer-authed) and hands it
-- to /mobile/web-login, so the real access token never travels in a URL /
-- access log. Reuses all the existing token machinery (hash, expiry, revoke).
-- ---------------------------------------------------------------------
ALTER TABLE mobile_auth_tokens
    MODIFY type ENUM('access','refresh','web_ticket') NOT NULL;
