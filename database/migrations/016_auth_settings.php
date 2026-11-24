<?php

/**
 * Migration: auth_settings
 * Created: 2025-11-24 00:00:00
 * Description: Настройки аутентификации (yandex_id в users + настройки в site_settings)
 * Применяется ПОСЛЕ 002_users и 004_site_settings_extended
 */

return [
    'up' => function (\PDO $pdo): void {
        // yandex_id колонка в users
        $stmt = $pdo->query("SHOW COLUMNS FROM `va_users` LIKE 'yandex_id'");
        if (!$stmt->fetch()) {
            $pdo->exec(<<<'SQL'
                ALTER TABLE `va_users`
                ADD COLUMN `yandex_id` VARCHAR(128) NOT NULL DEFAULT '' AFTER `email`;
            SQL);
        }

        // Настройки в site_settings (INSERT IGNORE)
        $settings = [
            ['adminLoginEmail', 'osvax@yandex.ru', 'email', 'authentication', 1, 'Email для входа в админ-панель', ''],
            ['auth2faEnabled', '0', 'checkbox', 'authentication', 10, '', ''],
            ['yandexAuthEnabled', '0', 'checkbox', 'authentication', 20, '', ''],
            ['yandexClientId', '', 'text', 'authentication', 30, 'ID приложения Яндекс', ''],
            ['yandexClientSecret', '', 'text', 'authentication', 40, 'Секрет приложения Яндекс', ''],
            ['yandexRedirectUri', '', 'url', 'authentication', 50, 'https://example.com/api/auth/yandex/callback', ''],
        ];

        $stmt = $pdo->prepare(<<<'SQL'
            INSERT IGNORE INTO `va_site_settings` 
            (`setting_key`, `setting_value`, `setting_type`, `tab_group`, `display_order`, `placeholder`, `options`)
            VALUES (:key, :value, :type, :tab, :order, :placeholder, :options)
        SQL);

        foreach ($settings as [$key, $value, $type, $tab, $order, $placeholder, $options]) {
            $stmt->execute([
                ':key' => $key,
                ':value' => $value,
                ':type' => $type,
                ':tab' => $tab,
                ':order' => $order,
                ':placeholder' => $placeholder,
                ':options' => $options,
            ]);
        }
    },

    'down' => function (\PDO $pdo): void {
        // Удаляем настройки
        $keys = ['adminLoginEmail', 'auth2faEnabled', 'yandexAuthEnabled', 'yandexClientId', 'yandexClientSecret', 'yandexRedirectUri'];
        $placeholders = implode(',', array_fill(0, count($keys), '?'));
        $stmt = $pdo->prepare("DELETE FROM `va_site_settings` WHERE `setting_key` IN ($placeholders)");
        $stmt->execute($keys);

        // Удаляем колонку yandex_id (если не используется)
        $stmt = $pdo->query("SHOW COLUMNS FROM `va_users` LIKE 'yandex_id'");
        if ($stmt->fetch()) {
            $pdo->exec('ALTER TABLE `va_users` DROP COLUMN `yandex_id`;');
        }
    },

    'description' => 'Auth settings: yandex_id column + site_settings entries for 2FA and Yandex OAuth'
];