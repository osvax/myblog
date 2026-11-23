<?php

namespace App\Core;

/**
 * Middleware для автоматической записи посещений страниц.
 * Вызывается в конце обработки запроса для любых посетителей
 * публичной части сайта (включая гостей; админ-панель и API-запросы
 * в статистику не записываются).
 */
class VisitLogger
{
    /**
     * Окно дедупликации повторных загрузок (секунды): повторное открытие
     * той же страницы тем же посетителем в пределах окна не считается
     * новым визитом (F5, автообновление страницы).
     */
    private const RELOAD_WINDOW = 60;

    /**
     * Записать посещение страницы (авторизованный пользователь или гость, user_id = 0).
     *
     * @param string $pageUrl URL страницы
     * @param string $pageTitle Заголовок страницы
     * @return void
     */
    public static function log(string $pageUrl, string $pageTitle): void
    {
        try {
            // Статистика учитывает только «входы» на сайт — загрузку страниц:
            // админ-панель и API-запросы (XHR/fetch: виджет консультанта,
            // csrf, формы) визитами не являются и не записываются
            $path = (string) (parse_url($pageUrl, PHP_URL_PATH) ?: $pageUrl);
            if ($path === '/admin' || strpos($path, '/admin/') === 0
                || strpos($path, '/api/') === 0 || $path === '/mvc/admin') {
                return;
            }

            $userId = 0;

            if (session_status() === PHP_SESSION_ACTIVE) {
                $userId = (int) ($_SESSION['user_id'] ?? 0);
            }

            $ipAddress = self::getIpAddress();
            $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
            // Страница входа (откуда пришёл посетитель) — для блока «Трафик сайта»
            $referrer = isset($_SERVER['HTTP_REFERER']) && is_string($_SERVER['HTTP_REFERER'])
                ? substr($_SERVER['HTTP_REFERER'], 0, 500)
                : null;

            $db = Database::connection();

            // Обновляем last_visit и last_ip в users (только для авторизованных)
            if ($userId > 0) {
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
            }

            // Записываем активность (только разделы, включённые в статистику)
            try {
                $section = \App\Models\UserActivity::sectionOfUrl($pageUrl);
                if (in_array($section, \App\Models\UserActivity::allowedSections(), true)) {
                    $recentId = self::recentVisitId($db, $userId, $ipAddress, $userAgent, $pageUrl);
                    if ($recentId !== null) {
                        // Повторная загрузка страницы в пределах окна дедупликации —
                        // не новый визит: продлеваем время последней активности,
                        // чтобы счётчик «посетители онлайн» продолжал работать
                        $stmt = $db->prepare(
                            'UPDATE ' . db_table('user_activities') . ' SET visited_at = NOW() WHERE id = :id'
                        );
                        $stmt->execute([':id' => $recentId]);
                    } else {
                        $stmt = $db->prepare(
                            "INSERT INTO " . db_table('user_activities') . " (user_id, page_url, page_title, ip_address, user_agent, referrer, visited_at)
                             VALUES (:user_id, :page_url, :page_title, :ip_address, :user_agent, :referrer, NOW())"
                        );
                        $stmt->execute([
                            ':user_id' => $userId,
                            ':page_url' => $pageUrl,
                            ':page_title' => $pageTitle,
                            ':ip_address' => $ipAddress,
                            ':user_agent' => $userAgent,
                            ':referrer' => $referrer,
                        ]);
                    }
                }
            } catch (\Exception $e) {
                // Таблица может не существовать
            }

            // Автоочистка старых записей статистики (не чаще раза в сутки)
            try {
                (new \App\Models\UserActivity())->autoClean();
            } catch (\Exception $e) {
                // Не ломаем запись посещения
            }
        } catch (\Exception $e) {
            // Ничего не делаем при ошибке — не должны ломать страницу
        }
    }

    /**
     * ID последней записи того же посетителя (user_id + IP + user-agent)
     * для той же страницы в пределах окна дедупликации; null — если её нет.
     */
    private static function recentVisitId(\PDO $db, int $userId, string $ipAddress, string $userAgent, string $pageUrl): ?int
    {
        $stmt = $db->prepare(
            'SELECT id FROM ' . db_table('user_activities') . "
             WHERE user_id = :user_id AND ip_address = :ip AND user_agent = :ua
               AND page_url = :url AND visited_at >= (NOW() - INTERVAL " . self::RELOAD_WINDOW . ' SECOND)
             ORDER BY id DESC LIMIT 1'
        );
        $stmt->execute([
            ':user_id' => $userId,
            ':ip' => $ipAddress,
            ':ua' => $userAgent,
            ':url' => $pageUrl,
        ]);
        $id = $stmt->fetchColumn();
        return $id === false ? null : (int) $id;
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
