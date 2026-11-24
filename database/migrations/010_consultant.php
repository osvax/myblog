<?php

/**
 * Migration: consultant
 * Created: 2025-11-24 00:00:00
 * Description: Таблицы онлайн-консультанта (чат)
 */

return [
    'up' => function (\PDO $pdo): void {
        // Пользователи консультанта (посетители)
        $pdo->exec(<<<'SQL'
            CREATE TABLE IF NOT EXISTS `va_consultant_users` (
                `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
                `session_id` VARCHAR(128) NOT NULL COMMENT 'ID сессии посетителя',
                `name` VARCHAR(160) NOT NULL DEFAULT '' COMMENT 'Имя посетителя',
                `email` VARCHAR(254) NOT NULL DEFAULT '' COMMENT 'Email посетителя',
                `ip` VARCHAR(45) NOT NULL DEFAULT '' COMMENT 'IP адрес',
                `user_agent` TEXT NULL COMMENT 'User Agent',
                `current_page` VARCHAR(500) NOT NULL DEFAULT '' COMMENT 'Текущая страница',
                `referrer` VARCHAR(500) NOT NULL DEFAULT '' COMMENT 'Реферер',
                `country` VARCHAR(100) NOT NULL DEFAULT '' COMMENT 'Страна (GeoIP)',
                `city` VARCHAR(100) NOT NULL DEFAULT '' COMMENT 'Город (GeoIP)',
                `is_online` TINYINT(1) NOT NULL DEFAULT 1 COMMENT 'В сети',
                `last_activity` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Последняя активность',
                `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                UNIQUE KEY `uq_consultant_users_session` (`session_id`),
                KEY `idx_consultant_users_online` (`is_online`, `last_activity`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        SQL);

        // Сообщения консультанта
        $pdo->exec(<<<'SQL'
            CREATE TABLE IF NOT EXISTS `va_consultant_messages` (
                `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
                `user_id` INT UNSIGNED NOT NULL COMMENT 'Посетитель (va_consultant_users)',
                `admin_id` BIGINT UNSIGNED NULL COMMENT 'Админ (va_users), NULL = от посетителя',
                `message` TEXT NOT NULL COMMENT 'Текст сообщения',
                `type` ENUM('text','file','system') NOT NULL DEFAULT 'text',
                `file_path` VARCHAR(500) NOT NULL DEFAULT '' COMMENT 'Путь к файлу (если type=file)',
                `is_read` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Прочитано админом',
                `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                KEY `idx_consultant_messages_user` (`user_id`, `created_at`),
                KEY `idx_consultant_messages_admin` (`admin_id`, `is_read`),
                CONSTRAINT `fk_consultant_messages_user` FOREIGN KEY (`user_id`) REFERENCES `va_consultant_users` (`id`) ON DELETE CASCADE,
                CONSTRAINT `fk_consultant_messages_admin` FOREIGN KEY (`admin_id`) REFERENCES `va_users` (`id`) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        SQL);

        // Прочитанные сообщения (для маркера "прочитано")
        $pdo->exec(<<<'SQL'
            CREATE TABLE IF NOT EXISTS `va_consultant_message_reads` (
                `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
                `message_id` INT UNSIGNED NOT NULL,
                `user_id` INT UNSIGNED NOT NULL COMMENT 'Кто прочитал (va_consultant_users или va_users)',
                `user_type` ENUM('visitor','admin') NOT NULL DEFAULT 'visitor',
                `read_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                UNIQUE KEY `uq_consultant_message_reads` (`message_id`, `user_id`, `user_type`),
                KEY `idx_consultant_message_reads_user` (`user_id`, `user_type`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        SQL);

        // Настройки консультанта
        $pdo->exec(<<<'SQL'
            CREATE TABLE IF NOT EXISTS `va_consultant_settings` (
                `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
                `setting_key` VARCHAR(100) NOT NULL,
                `setting_value` TEXT NULL,
                `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                UNIQUE KEY `uq_consultant_settings_key` (`setting_key`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        SQL);

        // Статусы "печатает..."
        $pdo->exec(<<<'SQL'
            CREATE TABLE IF NOT EXISTS `va_consultant_typing` (
                `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
                `user_id` INT UNSIGNED NOT NULL,
                `user_type` ENUM('visitor','admin') NOT NULL DEFAULT 'visitor',
                `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                UNIQUE KEY `uq_consultant_typing` (`user_id`, `user_type`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        SQL);

        // Счётчики прочитанных (для быстрых запросов)
        $pdo->exec(<<<'SQL'
            CREATE TABLE IF NOT EXISTS `va_consultant_reads` (
                `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
                `user_id` INT UNSIGNED NOT NULL COMMENT 'va_consultant_users',
                `last_read_message_id` INT UNSIGNED NOT NULL DEFAULT 0,
                `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                UNIQUE KEY `uq_consultant_reads_user` (`user_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        SQL);

        // Дефолтные настройки
        $pdo->exec(<<<'SQL'
            INSERT IGNORE INTO `va_consultant_settings` (`setting_key`, `setting_value`) VALUES
                ('welcome_message', 'Здравствуйте! Чем могу помочь?'),
                ('offline_message', 'Сейчас все операторы заняты. Оставьте сообщение, мы ответим позже.'),
                ('enable_sound', '1'),
                ('enable_desktop_notifications', '1'),
                ('auto_assign', '0'),
                ('max_chats_per_admin', '5');
        SQL);
    },

    'down' => function (\PDO $pdo): void {
        $pdo->exec('DROP TABLE IF EXISTS `va_consultant_reads`;');
        $pdo->exec('DROP TABLE IF EXISTS `va_consultant_typing`;');
        $pdo->exec('DROP TABLE IF EXISTS `va_consultant_settings`;');
        $pdo->exec('DROP TABLE IF EXISTS `va_consultant_message_reads`;');
        $pdo->exec('DROP TABLE IF EXISTS `va_consultant_messages`;');
        $pdo->exec('DROP TABLE IF EXISTS `va_consultant_users`;');
    },

    'description' => 'Consultant (online chat) tables'
];