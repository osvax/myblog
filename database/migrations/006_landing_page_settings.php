<?php

/**
 * Migration: landing_page_settings
 * Created: 2025-11-24 00:00:00
 * Description: Настройки секций лендинг-страниц
 */

return [
    'up' => function (\PDO $pdo): void {
        $pdo->exec(<<<'SQL'
            CREATE TABLE IF NOT EXISTS `va_landing_page_settings` (
                `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
                `section` VARCHAR(100) NOT NULL DEFAULT '' COMMENT 'Название секции/блока',
                `section_key` VARCHAR(100) NOT NULL DEFAULT '' COMMENT 'Уникальный ключ секции',
                `field_name` VARCHAR(100) NOT NULL DEFAULT '' COMMENT 'Имя поля',
                `field_value` TEXT NULL COMMENT 'Значение поля',
                `field_type` ENUM('text','textarea','number','email','tel','url','image','video','audio','color','checkbox','select','rich_text') NOT NULL DEFAULT 'text' COMMENT 'Тип поля',
                `field_group` VARCHAR(50) NOT NULL DEFAULT 'general' COMMENT 'Группа: hero, features, about, contacts, etc.',
                `display_order` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Порядок отображения',
                `placeholder` VARCHAR(254) DEFAULT '' COMMENT 'Подсказка',
                `options` TEXT NULL COMMENT 'JSON массив для select',
                `is_active` TINYINT(1) NOT NULL DEFAULT 1 COMMENT 'Активно',
                `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                KEY `idx_section_key` (`section_key`),
                KEY `idx_field_group` (`field_group`),
                KEY `idx_display_order` (`display_order`),
                KEY `idx_field_name` (`field_name`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        SQL);
    },

    'down' => function (\PDO $pdo): void {
        $pdo->exec('DROP TABLE IF EXISTS `va_landing_page_settings`;');
    },

    'description' => 'Landing page sections settings'
];