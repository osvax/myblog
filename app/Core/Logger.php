<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Простая система логирования событий сайта (таблица logs).
 * Что и сколько хранить настраивается в админке: Настройки сайта → Логи.
 *
 * Типы событий:
 *  - auth      — вход/выход, регистрация, ошибки входа
 *  - forms     — отправленные формы (заявки, сообщения, комментарии)
 *  - security  — блокировки по IP, отказы в доступе к админке
 *  - errors    — внутренние ошибки (500)
 */
final class Logger
{
    public const TYPES = ['auth', 'forms', 'security', 'errors'];

    /**
     * Записать событие в журнал (если включено соответствующим настроек).
     * Ошибки записи молча игнорируются — лог не должен ломать сайт.
     *
     * @param string      $type   Один из auth|forms|security|errors
     * @param string      $message Короткое описание события
     * @param int|null    $userId Пользователь, совершивший действие (0/1 — гость/админка)
     * @param string|null $ip     IP по умолчанию берётся из $_SERVER
     */
    public static function write(string $type, string $message, ?int $userId = null, ?string $ip = null): void
    {
        if (!in_array($type, self::TYPES, true) || trim($message) === '') {
            return;
        }

        try {
            $settings = new \App\Models\SiteSettings();

            if (!(bool) $settings->get('logsEnabled', true)) {
                return;
            }
            // Отдельный тумблер на каждый тип событий
            $typeKey = 'logs' . ucfirst($type);
            if (!(bool) $settings->get($typeKey, true)) {
                return;
            }

            $db = Database::connection();
            $ip = $ip !== null ? mb_substr($ip, 0, 45) : self::clientIp();

            $stmt = $db->prepare(
                'INSERT INTO ' . db_table('logs') . ' (log_type, message, user_id, ip, created_at)
                 VALUES (:type, :message, :user_id, :ip, NOW())'
            );
            $stmt->execute([
                ':type' => $type,
                ':message' => mb_substr($message, 0, 500),
                ':user_id' => $userId !== null ? (int) $userId : null,
                ':ip' => $ip,
            ]);

            self::clean($settings);
        } catch (\Throwable $e) {
            // Логирование не должно влиять на запрос
        }
    }

    /**
     * Очистка старых записей: не чаще раза в сутки (маркер в site_settings)
     * и только при включённом авточистке и положительном сроке хранения.
     */
    private static function clean(\App\Models\SiteSettings $settings): void
    {
        $retention = (int) $settings->get('logsRetentionDays', 30);
        if ($retention < 1 || !(bool) $settings->get('logsAutoClean', true)) {
            return;
        }

        $last = (string) $settings->get('logsLastCleanAt', '');
        $lastTs = $last !== '' ? (int) strtotime($last) : 0;
        if ($lastTs !== 0 && time() - $lastTs < 86400) {
            return;
        }

        $db = Database::connection();
        $stmt = $db->prepare(
            'DELETE FROM ' . db_table('logs') . ' WHERE created_at < DATE_SUB(NOW(), INTERVAL :days DAY)'
        );
        $stmt->execute([':days' => $retention]);

        // Маркер последней очистки
        $marker = $db->prepare(
            'INSERT INTO ' . db_table('site_settings') . " (setting_key, setting_value, setting_type, updated_at)
             VALUES ('logsLastCleanAt', :v, 'text', NOW())
             ON DUPLICATE KEY UPDATE setting_value = :v2, updated_at = NOW()"
        );
        $marker->execute([':v' => date('Y-m-d H:i:s'), ':v2' => date('Y-m-d H:i:s')]);
    }

    /**
     * Последние записи журнала (для вкладки «Логи» в настройках).
     *
     * @return array ['total' => int, 'items' => array<array>]
     */
    public static function recent(int $limit = 50): array
    {
        try {
            $db = Database::connection();
            $table = db_table('logs');

            $total = (int) $db->query("SELECT COUNT(*) FROM `{$table}`")->fetchColumn();
            $rows = $db->query("SELECT id, log_type, message, user_id, ip, created_at FROM `{$table}` ORDER BY id DESC LIMIT " . (int) $limit)->fetchAll();

            return ['total' => $total, 'items' => $rows];
        } catch (\Throwable $e) {
            return ['total' => 0, 'items' => []];
        }
    }

    /**
     * Очистить журнал полностью.
     */
    public static function clear(): int
    {
        $db = Database::connection();
        $stmt = $db->exec('DELETE FROM ' . db_table('logs'));
        return (int) $stmt;
    }

    /**
     * IP посетителя (с учётом прокси-заголовков, как в остальных частях сайта).
     */
    public static function clientIp(): string
    {
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = trim(explode(',', (string) $_SERVER['HTTP_X_FORWARDED_FOR'])[0]);
            if (filter_var($ip, FILTER_VALIDATE_IP)) {
                return $ip;
            }
        }

        return (string) ($_SERVER['REMOTE_ADDR'] ?? '');
    }
}
