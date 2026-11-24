<?php

/**
 * Migration: site_settings_extended
 * Created: 2025-11-24 00:00:00
 * Description: Расширение таблицы настроек (setting_type, tab_group, display_order, placeholder, options)
 * Применяется ПОСЛЕ 001_initial_schema (которая создаёт базовую va_site_settings)
 */

return [
    'up' => function (\PDO $pdo): void {
        // Добавляем недостающие колонки к существующей таблице
        // Используем IF NOT EXISTS через проверку INFORMATION_SCHEMA

        // setting_type
        $pdo->exec(<<<'SQL'
            ALTER TABLE `va_site_settings`
            ADD COLUMN IF NOT EXISTS `setting_type` VARCHAR(20) NOT NULL DEFAULT 'text'
                COMMENT 'text, number, checkbox, select, color, textarea, email, tel, url, json'
                AFTER `setting_value`;
        SQL);

        // tab_group
        $pdo->exec(<<<'SQL'
            ALTER TABLE `va_site_settings`
            ADD COLUMN IF NOT EXISTS `tab_group` VARCHAR(50) NOT NULL DEFAULT 'general'
                COMMENT 'group: general, appearance, seo, notifications, consultant, contacts, security, email, statistics, theme, custom'
                AFTER `setting_type`;
        SQL);

        // display_order
        $pdo->exec(<<<'SQL'
            ALTER TABLE `va_site_settings`
            ADD COLUMN IF NOT EXISTS `display_order` INT UNSIGNED NOT NULL DEFAULT 0
                AFTER `tab_group`;
        SQL);

        // placeholder
        $pdo->exec(<<<'SQL'
            ALTER TABLE `va_site_settings`
            ADD COLUMN IF NOT EXISTS `placeholder` VARCHAR(254) DEFAULT ''
                AFTER `display_order`;
        SQL);

        // options (JSON для select)
        $pdo->exec(<<<'SQL'
            ALTER TABLE `va_site_settings`
            ADD COLUMN IF NOT EXISTS `options` TEXT NULL COMMENT 'JSON array for select'
                AFTER `placeholder`;
        SQL);

        // created_at (в baseline не было)
        $pdo->exec(<<<'SQL'
            ALTER TABLE `va_site_settings`
            ADD COLUMN IF NOT EXISTS `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
                AFTER `options`;
        SQL);

        // Индексы
        $pdo->exec(<<<'SQL'
            ALTER TABLE `va_site_settings`
            ADD INDEX IF NOT EXISTS `idx_tab_group` (`tab_group`),
            ADD INDEX IF NOT EXISTS `idx_display_order` (`display_order`);
        SQL);
    },

    'down' => function (\PDO $pdo): void {
        $pdo->exec(<<<'SQL'
            ALTER TABLE `va_site_settings`
            DROP COLUMN IF EXISTS `setting_type`,
            DROP COLUMN IF EXISTS `tab_group`,
            DROP COLUMN IF EXISTS `display_order`,
            DROP COLUMN IF EXISTS `placeholder`,
            DROP COLUMN IF EXISTS `options`,
            DROP COLUMN IF EXISTS `created_at`,
            DROP INDEX IF EXISTS `idx_tab_group`,
            DROP INDEX IF EXISTS `idx_display_order`;
        SQL);
    },

    'description' => 'Extend site_settings with type, tab_group, display_order, placeholder, options, created_at'
];