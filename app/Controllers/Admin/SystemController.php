<?php

namespace App\Controllers\Admin;

use App\Controllers\Controller;

class SystemController extends Controller
{
    public function info($request, array $params = [])
    {
        if (!\admin_is_authenticated()) {
            return $this->json(['success' => false, 'error' => 'Требуется авторизация'], 401);
        }
        if ($request->method() !== 'GET') {
            return $this->json(['success' => false, 'error' => 'Метод не поддерживается'], 405);
        }

        $serverSoftware = isset($_SERVER['SERVER_SOFTWARE']) && is_string($_SERVER['SERVER_SOFTWARE'])
            ? substr(preg_replace('/[\x00-\x1F\x7F]/', '', $_SERVER['SERVER_SOFTWARE']), 0, 120)
            : 'Не определён';

        return $this->json([
            'success' => true,
            'webServer' => $serverSoftware !== '' ? $serverSoftware : 'Не определён',
            'phpVersion' => PHP_VERSION,
            'os' => PHP_OS_FAMILY,
            'timezone' => date_default_timezone_get(),
            'uploadLimit' => ini_get('upload_max_filesize'),
            'memoryLimit' => ini_get('memory_limit'),
        ]);
    }

    /**
     * Последняя активность авторизованных пользователей (для дашборда).
     */
    public function activity($request)
    {
        if (!\admin_is_authenticated()) {
            return $this->json(['success' => false, 'error' => 'Требуется авторизация'], 401);
        }
        if ($request->method() !== 'GET') {
            return $this->json(['success' => false, 'error' => 'Метод не поддерживается'], 405);
        }

        $db = \App\Core\Database::connection();
        $rows = $db->query(
            'SELECT ua.page_url, ua.page_title, ua.visited_at, COALESCE(u.name, ' . "'Пользователь'" . ') AS name
             FROM ' . db_table('user_activities') . ' ua LEFT JOIN ' . db_table('users') . ' u ON u.id = ua.user_id
             ORDER BY ua.visited_at DESC, ua.id DESC LIMIT 8'
        )->fetchAll();
        return $this->json(['success' => true, 'activities' => $rows]);
    }

    /**
     * Статистика посещений для дашборда: онлайн (15 минут),
     * за сегодня и последние визиты (трафик сайта).
     */
    public function statistics($request)
    {
        if (!\admin_is_authenticated()) {
            return $this->json(['success' => false, 'error' => 'Требуется авторизация'], 401);
        }
        if ($request->method() !== 'GET') {
            return $this->json(['success' => false, 'error' => 'Метод не поддерживается'], 405);
        }

        $db = \App\Core\Database::connection();
        $table = db_table('user_activities');

        $online = (int) $db->query(
            "SELECT COUNT(DISTINCT ip_address) FROM {$table} WHERE visited_at >= (NOW() - INTERVAL 15 MINUTE)"
        )->fetchColumn();

        $today = (int) $db->query(
            "SELECT COUNT(DISTINCT ip_address) FROM {$table} WHERE visited_at >= CURDATE()"
        )->fetchColumn();

        $traffic = $db->query(
            "SELECT ip_address, visited_at, user_agent, referrer, page_url
             FROM {$table}
             ORDER BY visited_at DESC, id DESC
             LIMIT 15"
        )->fetchAll();

        return $this->json([
            'success' => true,
            'online' => $online,
            'today' => $today,
            'traffic' => $traffic,
        ]);
    }
}
