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
     * за сегодня и последние визиты (блок «Трафик сайта»).
     *
     * Фильтры трафика (GET): date_from, date_to (YYYY-MM-DD),
     * time_from, time_to (HH:MM[:SS]), browser (chrome|edge|firefox|
     * safari|opera|other), section (home|blog|portfolio|auth|admin|other),
     * page (с 1).
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

        // ---------- Фильтры (строго валидуются, допустимы только свои значения) ----------
        $where = [];
        $params = [];

        $dateFrom = trim((string) ($_GET['date_from'] ?? ''));
        if ($dateFrom !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateFrom) === 1) {
            $where[] = 'DATE(t.visited_at) >= :date_from';
            $params[':date_from'] = $dateFrom;
        }
        $dateTo = trim((string) ($_GET['date_to'] ?? ''));
        if ($dateTo !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateTo) === 1) {
            $where[] = 'DATE(t.visited_at) <= :date_to';
            $params[':date_to'] = $dateTo;
        }
        $timeFrom = trim((string) ($_GET['time_from'] ?? ''));
        if ($timeFrom !== '' && preg_match('/^\d{2}:\d{2}(:\d{2})?$/', $timeFrom) === 1) {
            $where[] = 'TIME(t.visited_at) >= :time_from';
            $params[':time_from'] = $timeFrom;
        }
        $timeTo = trim((string) ($_GET['time_to'] ?? ''));
        if ($timeTo !== '' && preg_match('/^\d{2}:\d{2}(:\d{2})?$/', $timeTo) === 1) {
            $where[] = 'TIME(t.visited_at) <= :time_to';
            $params[':time_to'] = $timeTo;
        }
        $browser = trim((string) ($_GET['browser'] ?? ''));
        if (in_array($browser, ['chrome', 'edge', 'firefox', 'safari', 'opera', 'other'], true)) {
            $where[] = 't.browser = :browser';
            $params[':browser'] = $browser;
        }
        $section = trim((string) ($_GET['section'] ?? ''));
        if (in_array($section, ['home', 'blog', 'portfolio', 'auth', 'admin', 'other'], true)) {
            $where[] = 't.section = :section';
            $params[':section'] = $section;
        }
        $whereSql = $where !== [] ? ' WHERE ' . implode(' AND ', $where) : '';

        // Разделы сайта определяются по URL; слогии статей и проектов — из публичного контента
        $content = require APP_ROOT . '/config/public-content.php';
        $articleUrls = array_map(
            static function ($slug) { return "'" . $slug . ".html'"; },
            array_keys(is_array($content) && isset($content['articles']) ? $content['articles'] : [])
        );
        $projectUrls = array_map(
            static function ($slug) { return "'" . $slug . ".html'"; },
            array_keys(is_array($content) && isset($content['projects']) ? $content['projects'] : [])
        );
        $articleIn = $articleUrls !== [] ? 't0.page_url IN (' . implode(', ', $articleUrls) . ") OR " : '';
        $projectIn = $projectUrls !== [] ? 't0.page_url IN (' . implode(', ', $projectUrls) . ") OR " : '';

        $subQuery = "SELECT t0.id, t0.ip_address, t0.visited_at, t0.user_agent, t0.referrer, t0.page_url,
                CASE
                    WHEN t0.user_agent LIKE '%Edg/%' THEN 'edge'
                    WHEN t0.user_agent LIKE '%OPR/%' OR t0.user_agent LIKE '%Opr/%' THEN 'opera'
                    WHEN t0.user_agent LIKE '%Firefox/%' THEN 'firefox'
                    WHEN t0.user_agent LIKE '%Chrome/%' THEN 'chrome'
                    WHEN t0.user_agent LIKE '%Safari/%' THEN 'safari'
                    ELSE 'other'
                END AS browser,
                CASE
                    WHEN t0.page_url LIKE '/admin%' OR t0.page_url LIKE '/api/admin%' OR t0.page_url = '/mvc/admin' THEN 'admin'
                    WHEN t0.page_url IN ('/', '/index.html', '/mvc', '/health') THEN 'home'
                    WHEN {$articleIn}t0.page_url = '/blog.html' OR t0.page_url LIKE '/category/%' THEN 'blog'
                    WHEN {$projectIn}t0.page_url IN ('/portfolio.html', '/messages.html', '/orders.html', '/settings.html') OR t0.page_url LIKE '/project-%' THEN 'portfolio'
                    WHEN t0.page_url IN ('/login.html', '/register.html', '/forgot-password.html', '/profile.html', '/logout.html')
                         OR t0.page_url LIKE '/reset-password%' OR t0.page_url LIKE '/api/auth%' OR t0.page_url LIKE '/api/profile%' THEN 'auth'
                    ELSE 'other'
                END AS section
            FROM {$table} t0";

        $countStmt = $db->prepare("SELECT COUNT(*) FROM ({$subQuery}) t{$whereSql}");
        $countStmt->execute($params);
        $total = (int) $countStmt->fetchColumn();

        // ---------- Пагинация ----------
        $perPage = 15;
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $pages = max(1, (int) ceil($total / $perPage));
        if ($page > $pages) {
            $page = $pages;
        }
        $offset = ($page - 1) * $perPage;

        $stmt = $db->prepare(
            "SELECT t.ip_address, t.visited_at, t.user_agent, t.referrer, t.page_url
             FROM ({$subQuery}) t{$whereSql}
             ORDER BY t.visited_at DESC, t.id DESC
             LIMIT {$perPage} OFFSET {$offset}"
        );
        $stmt->execute($params);
        $traffic = $stmt->fetchAll();

        return $this->json([
            'success' => true,
            'online' => $online,
            'today' => $today,
            'traffic' => $traffic,
            'pagination' => [
                'page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'pages' => $pages,
            ],
        ]);
    }
}
