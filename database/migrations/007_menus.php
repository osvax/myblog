<?php

/**
 * Migration: menus
 * Created: 2025-11-24 00:00:00
 * Description: Меню и пункты меню
 */

return [
    'up' => function (\PDO $pdo): void {
        $pdo->exec(<<<'SQL'
            CREATE TABLE IF NOT EXISTS `va_menus` (
                `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
                `menu_name` VARCHAR(80) NOT NULL COMMENT 'Идентификатор меню (для render_menu)',
                `title` VARCHAR(160) NOT NULL DEFAULT '' COMMENT 'Название в админке',
                `css_class` VARCHAR(200) NOT NULL DEFAULT '' COMMENT 'CSS-классы контейнера',
                `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                UNIQUE KEY `uq_menus_name` (`menu_name`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        SQL);

        $pdo->exec(<<<'SQL'
            CREATE TABLE IF NOT EXISTS `va_menu_items` (
                `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
                `menu_id` INT UNSIGNED NOT NULL COMMENT 'Меню',
                `parent_id` INT UNSIGNED NULL COMMENT 'Родительский пункт (подменю)',
                `title` VARCHAR(160) NOT NULL COMMENT 'Текст пункта',
                `url` VARCHAR(255) NOT NULL DEFAULT '/' COMMENT 'Ссылка',
                `icon` VARCHAR(80) NOT NULL DEFAULT '' COMMENT 'Иконка Bootstrap Icons',
                `css_class` VARCHAR(200) NOT NULL DEFAULT '' COMMENT 'CSS-классы пункта',
                `display_order` INT NOT NULL DEFAULT 0,
                `is_active` TINYINT(1) NOT NULL DEFAULT 1 COMMENT 'Активен',
                `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                KEY `idx_menu_items_menu` (`menu_id`, `display_order`),
                KEY `idx_menu_items_parent` (`parent_id`),
                CONSTRAINT `fk_menu_items_menu` FOREIGN KEY (`menu_id`) REFERENCES `va_menus` (`id`) ON DELETE CASCADE,
                CONSTRAINT `fk_menu_items_parent` FOREIGN KEY (`parent_id`) REFERENCES `va_menu_items` (`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        SQL);

        // Seed data: главное меню
        $pdo->exec(<<<'SQL'
            INSERT IGNORE INTO `va_menus` (`id`, `menu_name`, `title`, `css_class`) VALUES (1, 'main', 'Главное меню', '');
        SQL);

        $pdo->exec(<<<'SQL'
            INSERT IGNORE INTO `va_menu_items` (`menu_id`, `parent_id`, `title`, `url`, `icon`, `css_class`, `display_order`, `is_active`) VALUES
                (1, NULL, 'Главная', '/', '', '', 1, 1),
                (1, NULL, 'Блог', '/blog.html', '', '', 2, 1),
                (1, NULL, 'Портфолио', '/portfolio.html', '', '', 3, 1),
                (1, NULL, 'Заказать', '/order.html', '', '', 4, 1),
                (1, NULL, 'Контакты', '/contacts.html', '', '', 5, 1);
        SQL);
    },

    'down' => function (\PDO $pdo): void {
        $pdo->exec('DROP TABLE IF EXISTS `va_menu_items`;');
        $pdo->exec('DROP TABLE IF EXISTS `va_menus`;');
    },

    'description' => 'Menus and menu items with seed data'
];