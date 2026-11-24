<?php

/**
 * Migration: static_pages
 * Created: 2025-11-24 00:00:00
 * Description: Статические страницы (отдельно от landing pages)
 */

return [
    'up' => function (\PDO $pdo): void {
        $pdo->exec(<<<'SQL'
            CREATE TABLE IF NOT EXISTS `va_static_pages` (
                `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
                `slug` VARCHAR(120) NOT NULL COMMENT 'Уникальный ключ страницы',
                `title` VARCHAR(240) NOT NULL DEFAULT '' COMMENT 'Заголовок',
                `content` LONGTEXT NULL COMMENT 'HTML контент',
                `meta_title` VARCHAR(240) NOT NULL DEFAULT '' COMMENT 'SEO title',
                `meta_description` VARCHAR(500) NOT NULL DEFAULT '' COMMENT 'SEO description',
                `meta_keywords` VARCHAR(500) NOT NULL DEFAULT '' COMMENT 'SEO keywords',
                `is_active` TINYINT(1) NOT NULL DEFAULT 1 COMMENT 'Активна',
                `template` VARCHAR(100) NOT NULL DEFAULT 'default' COMMENT 'Шаблон отображения',
                `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                UNIQUE KEY `uq_static_pages_slug` (`slug`),
                KEY `idx_static_pages_active` (`is_active`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        SQL);
    },

    'down' => function (\PDO $pdo): void {
        $pdo->exec('DROP TABLE IF EXISTS `va_static_pages`;');
    },

    'description' => 'Static pages table'
];