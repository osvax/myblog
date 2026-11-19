<?php

namespace App\Core;

/**
 * Middleware для автоматической записи посещений страниц.
 * Вызывается в конце обработки запроса для logged-in пользователей.
 */
class VisitLogger
{
    /**
     * Записать посещение для авторизованного пользователя.
     *
     * @param string $userType 'admin' или 'user'
     * @param string $pageUrl URL страницы
     * @param string $pageTitle Заголовок страницы
     * @return void
     */
    public static function log(string $userType, string $pageUrl, string $pageTitle): void
    {
        try {
            $userId = null;
            $sessionId = null;

            if ($userType === 'admin') {
                admin_start_session();
                $userId = admin_get_user_id();
                $sessionId = session_id();
            } elseif ($userType === 'user') {
                if (session_status() === PHP_SESSION_ACTIVE) {
                    $userId = (int) ($_SESSION['user_id'] ?? 0);
                    $sessionId = session_id();
                }
            }

            if (!$userId) {
                return;
            }

            $ipAddress = self::getIpAddress();
            $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';

            $db = Database::connection();

            // Обновляем last_visit и last_ip в users
            try {
                $stmt = $db->prepare(
                    "UPDATE " . db_table('users') . " SET last_visit = NOW(), last_ip = :ip WHERE id = :id"
                );
                $stmt->execute([
                    ':ip' => $ipAddress,
                    ':id' => $userId,
                ]);
            } catch (\Exception $e) {
                // Поле может не существовать
            }

            // Записываем активность
            try {
                $stmt = $db->prepare(
                    "INSERT INTO " . db_table('user_activities') . " (user_id, page_url, page_title, ip_address, user_agent, visited_at)
                     VALUES (:user_id, :page_url, :page_title, :ip_address, :user_agent, NOW())"
                );
                $stmt->execute([
                    ':user_id' => $userId,
                    ':page_url' => $pageUrl,
                    ':page_title' => $pageTitle,
                    ':ip_address' => $ipAddress,
                    ':user_agent' => $userAgent,
                ]);
            } catch (\Exception $e) {
                // Таблица может не существовать
            }
        } catch (\Exception $e) {
            // Ничего не делаем при ошибке — не должны ломать страницу
        }
    }

    /**
     * Получить реальный IP-адрес клиента.
     *
     * @return string
     */
    private static function getIpAddress(): string
    {
        $headers = [
            'HTTP_CF_CONNECTING_IP',   // Cloudflare
            'HTTP_X_FORWARDED_FOR',    // Proxy
            'HTTP_X_REAL_IP',          // Nginx
        ];

        foreach ($headers as $header) {
            if (!empty($_SERVER[$header])) {
                $ips = explode(',', $_SERVER[$header]);
                $ip = trim($ips[0]);
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }

        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }
}
