<?php

/**
 * Migration: logs
 * Created: 2025-11-24 00:00:00
 * Description: Журнал событий сайта (auth, forms, security, errors)
 */

return [
    'up' => function (\PDO $pdo): void {
        $pdo->exec(<<<'SQL'
            CREATE TABLE IF NOT EXISTS `va_logs` (
                `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
                `log_type` VARCHAR(20) NOT NULL COMMENT 'Тип: auth, forms, security, errors',
                `message` VARCHAR(500) NOT NULL DEFAULT '',
                `user_id` INT UNSIGNED NULL COMMENT 'ID пользователя (va_users)',
                `ip` VARCHAR(45) NOT NULL DEFAULT '',
                `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                KEY `idx_logs_type_created` (`log_type`, `created_at`),
                KEY `idx_logs_created` (`created_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        SQL);
    },

    'down' => function (\PDO $pdo): void {
        $pdo->exec('DROP TABLE IF EXISTS `va_logs`;');
    },

    'description' => 'Site event logs (auth, forms, security, errors)'
];