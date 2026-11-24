<?php

/**
 * Migration: comment_replies
 * Created: 2025-11-24 00:00:00
 * Description: Колонки для ответов на комментарии (parent_id, is_staff)
 * Применяется ПОСЛЕ 001_initial_schema (va_messages должна существовать)
 */

return [
    'up' => function (\PDO $pdo): void {
        // parent_id
        $stmt = $pdo->query("SHOW COLUMNS FROM `va_messages` LIKE 'parent_id'");
        if (!$stmt->fetch()) {
            $pdo->exec(<<<'SQL'
                ALTER TABLE `va_messages`
                ADD COLUMN `parent_id` INT UNSIGNED NULL COMMENT 'Родительский комментарий (ответ)' AFTER `article_slug`;
            SQL);
        }

        // is_staff
        $stmt = $pdo->query("SHOW COLUMNS FROM `va_messages` LIKE 'is_staff'");
        if (!$stmt->fetch()) {
            $pdo->exec(<<<'SQL'
                ALTER TABLE `va_messages`
                ADD COLUMN `is_staff` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Ответ от имени автора сайта' AFTER `parent_id`;
            SQL);
        }

        // Индекс для parent_id
        $stmt = $pdo->query("SHOW INDEX FROM `va_messages` WHERE Key_name = 'idx_messages_parent'");
        if (!$stmt->fetch()) {
            $pdo->exec(<<<'SQL'
                ALTER TABLE `va_messages`
                ADD INDEX `idx_messages_parent` (`parent_id`);
            SQL);
        }
    },

    'down' => function (\PDO $pdo): void {
        $stmt = $pdo->query("SHOW INDEX FROM `va_messages` WHERE Key_name = 'idx_messages_parent'");
        if ($stmt->fetch()) {
            $pdo->exec('ALTER TABLE `va_messages` DROP INDEX `idx_messages_parent`;');
        }
        $stmt = $pdo->query("SHOW COLUMNS FROM `va_messages` LIKE 'is_staff'");
        if ($stmt->fetch()) {
            $pdo->exec('ALTER TABLE `va_messages` DROP COLUMN `is_staff`;');
        }
        $stmt = $pdo->query("SHOW COLUMNS FROM `va_messages` LIKE 'parent_id'");
        if ($stmt->fetch()) {
            $pdo->exec('ALTER TABLE `va_messages` DROP COLUMN `parent_id`;');
        }
    },

    'description' => 'Add parent_id and is_staff to messages for comment replies'
];