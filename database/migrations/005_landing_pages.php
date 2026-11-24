<?php

/**
 * Migration: landing_pages
 * Created: 2025-11-24 00:00:00
 * Description: Landing pages (посадочные страницы)
 */

return [
    'up' => function (\PDO $pdo): void {
        $pdo->exec(<<<'SQL'
            CREATE TABLE IF NOT EXISTS `va_landing_pages` (
                `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
                `slug` VARCHAR(100) NOT NULL DEFAULT 'home' COMMENT 'Уникальный ключ страницы',
                `title` VARCHAR(254) NOT NULL DEFAULT '' COMMENT 'Заголовок страницы',
                `html_content` LONGTEXT NULL COMMENT 'Сгенерированный HTML',
                `is_active` TINYINT(1) NOT NULL DEFAULT 1 COMMENT 'Активна',
                `generated_at` DATETIME NULL COMMENT 'Дата генерации',
                `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                UNIQUE KEY `uq_landing_pages_slug` (`slug`),
                KEY `idx_landing_pages_active` (`is_active`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        SQL);
    },

    'down' => function (\PDO $pdo): void {
        $pdo->exec('DROP TABLE IF EXISTS `va_landing_pages`;');
    },

    'description' => 'Landing pages table'
];