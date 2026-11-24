<?php

/**
 * Migration: config_to_settings
 * Created: 2025-11-24 00:00:00
 * Description: Перенос конфигурации в site_settings (template settings)
 * Применяется ПОСЛЕ 004_site_settings_extended
 */

return [
    'up' => function (\PDO $pdo): void {
        $settings = [
            ['adminLoginEmail', 'osvax@yandex.ru', 'email', 'authentication', 1, 'Email для входа в админ-панель', ''],
            ['frontendTemplate', 'default', 'select', 'templates', 1, 'Шаблон публичной части', json_encode(['default', 'modern', 'minimal'], JSON_UNESCAPED_UNICODE)],
            ['adminTemplate', 'default', 'select', 'templates', 2, 'Шаблон админ-панели', json_encode(['default', 'dark', 'compact'], JSON_UNESCAPED_UNICODE)],
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
        $keys = ['frontendTemplate', 'adminTemplate'];
        $placeholders = implode(',', array_fill(0, count($keys), '?'));
        $stmt = $pdo->prepare("DELETE FROM `va_site_settings` WHERE `setting_key` IN ($placeholders)");
        $stmt->execute($keys);
    },

    'description' => 'Move template config to site_settings'
];