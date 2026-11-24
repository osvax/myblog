<?php

/**
 * Migration: category_navigation
 * Created: 2025-11-24 00:00:00
 * Description: Навигация внутри категорий (подменю для рубрик)
 * Применяется ПОСЛЕ 001_initial_schema (va_categories должна существовать)
 */

return [
    'up' => function (\PDO $pdo): void {
        $pdo->exec(<<<'SQL'
            CREATE TABLE IF NOT EXISTS `va_category_navigation` (
                `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
                `category_id` BIGINT UNSIGNED NOT NULL,
                `title` VARCHAR(160) NOT NULL,
                `url` VARCHAR(255) NOT NULL,
                `icon` VARCHAR(80) NOT NULL DEFAULT '',
                `display_order` INT NOT NULL DEFAULT 0,
                `is_active` TINYINT(1) NOT NULL DEFAULT 1,
                `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                KEY `idx_category_navigation_category` (`category_id`, `display_order`),
                CONSTRAINT `fk_category_navigation_category` FOREIGN KEY (`category_id`) REFERENCES `va_categories` (`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        SQL);
    },

    'down' => function (\PDO $pdo): void {
        $pdo->exec('DROP TABLE IF EXISTS `va_category_navigation`;');
    },

    'description' => 'Category navigation (submenu items for categories)'
];