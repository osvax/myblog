ALTER TABLE users
    ADD COLUMN IF NOT EXISTS totp_secret VARCHAR(64) NOT NULL DEFAULT '' COMMENT 'Секрет TOTP (base32)' AFTER language,
    ADD COLUMN IF NOT EXISTS totp_enabled TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Двухфакторная аутентификация включена' AFTER totp_secret;
