<?php

/**
 * Migration: user_activities_referrer
 * Created: 2025-11-24 00:00:00
 * Description: Колонка referrer в таблице user_activities (для блока "Трафик сайта")
 * Применяется ПОСЛЕ 003_user_activities
 */

return [
    'up' => function (\PDO $pdo): void {
        // Проверяем, существует ли колонка
        $stmt = $pdo->query("SHOW COLUMNS FROM `va_user_activities` LIKE 'referrer'");
        if (!$stmt->fetch()) {
            $pdo->exec(<<<'SQL'
                ALTER TABLE `va_user_activities`
                ADD COLUMN `referrer` VARCHAR(500) NULL DEFAULT NULL COMMENT 'Страница входа (HTTP_REFERER)' AFTER `user_agent`;
            SQL);
        }
    },

    'down' => function (\PDO $pdo): void {
        $stmt = $pdo->query("SHOW COLUMNS FROM `va_user_activities` LIKE 'referrer'");
        if ($stmt->fetch()) {
            $pdo->exec('ALTER TABLE `va_user_activities` DROP COLUMN `referrer`;');
        }
    },

    'description' => 'Add referrer column to user_activities for traffic analytics'
];