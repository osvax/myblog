<?php

/**
 * Migration: user_activities
 * Created: 2025-11-24 00:00:00
 * Description: Таблица активности пользователей (посещения страниц)
 */

return [
    'up' => function (\PDO $pdo): void {
        $pdo->exec(<<<'SQL'
            CREATE TABLE IF NOT EXISTS `va_user_activities` (
                `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                `user_id` BIGINT UNSIGNED NOT NULL,
                `page_url` VARCHAR(500) NOT NULL DEFAULT '',
                `page_title` VARCHAR(255) NOT NULL DEFAULT '',
                `ip_address` VARCHAR(45) NOT NULL DEFAULT '',
                `user_agent` TEXT,
                `visited_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                KEY `idx_user_activities_user_id` (`user_id`),
                KEY `idx_user_activities_visited_at` (`visited_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        SQL);
    },

    'down' => function (\PDO $pdo): void {
        $pdo->exec('DROP TABLE IF EXISTS `va_user_activities`;');
    },

    'description' => 'User activities table (page visits tracking)'
];