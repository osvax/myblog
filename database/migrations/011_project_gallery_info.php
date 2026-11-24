<?php

/**
 * Migration: project_gallery_info
 * Created: 2025-11-24 00:00:00
 * Description: Галерея проектов и дополнительные поля (ссылки, документы, сертификаты)
 * Применяется ПОСЛЕ 001_initial_schema (va_portfolio_projects должна существовать)
 */

return [
    'up' => function (\PDO $pdo): void {
        // Галерея проектов
        $pdo->exec(<<<'SQL'
            CREATE TABLE IF NOT EXISTS `va_project_gallery` (
                `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
                `project_id` CHAR(32) NOT NULL,
                `path` VARCHAR(255) NOT NULL,
                `description` VARCHAR(500) NOT NULL DEFAULT '',
                `sort_order` INT NOT NULL DEFAULT 0,
                `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                KEY `idx_project_gallery_project` (`project_id`, `sort_order`),
                CONSTRAINT `fk_project_gallery_project` FOREIGN KEY (`project_id`) REFERENCES `va_portfolio_projects` (`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        SQL);

        // Дополнительные поля проекта
        $pdo->exec(<<<'SQL'
            CREATE TABLE IF NOT EXISTS `va_project_info_items` (
                `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
                `project_id` CHAR(32) NOT NULL,
                `kind` VARCHAR(20) NOT NULL COMMENT 'Тип: link, document, certificate, etc.',
                `name` VARCHAR(160) NOT NULL,
                `value` VARCHAR(500) NOT NULL DEFAULT '',
                `file_path` VARCHAR(255) NOT NULL DEFAULT '',
                `sort_order` INT NOT NULL DEFAULT 0,
                `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                KEY `idx_project_info_items_project` (`project_id`, `sort_order`),
                CONSTRAINT `fk_project_info_items_project` FOREIGN KEY (`project_id`) REFERENCES `va_portfolio_projects` (`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        SQL);
    },

    'down' => function (\PDO $pdo): void {
        $pdo->exec('DROP TABLE IF EXISTS `va_project_info_items`;');
        $pdo->exec('DROP TABLE IF EXISTS `va_project_gallery`;');
    },

    'description' => 'Project gallery and info items (links, documents, certificates)'
];