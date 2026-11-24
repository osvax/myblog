<?php

/**
 * Migration: two_factor
 * Created: 2025-11-24 00:00:00
 * Description: Колонки для двухфакторной аутентификации (TOTP)
 * Применяется ПОСЛЕ 002_users
 */

return [
    'up' => function (\PDO $pdo): void {
        // totp_secret
        $stmt = $pdo->query("SHOW COLUMNS FROM `va_users` LIKE 'totp_secret'");
        if (!$stmt->fetch()) {
            $pdo->exec(<<<'SQL'
                ALTER TABLE `va_users`
                ADD COLUMN `totp_secret` VARCHAR(64) NOT NULL DEFAULT '' COMMENT 'Секрет TOTP (base32)' AFTER `language`;
            SQL);
        }

        // totp_enabled
        $stmt = $pdo->query("SHOW COLUMNS FROM `va_users` LIKE 'totp_enabled'");
        if (!$stmt->fetch()) {
            $pdo->exec(<<<'SQL'
                ALTER TABLE `va_users`
                ADD COLUMN `totp_enabled` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Двухфакторная аутентификация включена' AFTER `totp_secret`;
            SQL);
        }
    },

    'down' => function (\PDO $pdo): void {
        $stmt = $pdo->query("SHOW COLUMNS FROM `va_users` LIKE 'totp_enabled'");
        if ($stmt->fetch()) {
            $pdo->exec('ALTER TABLE `va_users` DROP COLUMN `totp_enabled`;');
        }
        $stmt = $pdo->query("SHOW COLUMNS FROM `va_users` LIKE 'totp_secret'");
        if ($stmt->fetch()) {
            $pdo->exec('ALTER TABLE `va_users` DROP COLUMN `totp_secret`;');
        }
    },

    'description' => 'Add TOTP 2FA columns to users'
];