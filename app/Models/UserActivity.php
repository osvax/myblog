<?php

namespace App\Models;

use App\Core\Database;

class UserActivity
{
    /**
     * Записать посещение страницы пользователем.
     *
     * @param int $userId
     * @param string $pageUrl
     * @param string $pageTitle
     * @param string $ipAddress
     * @param string $userAgent
     * @return bool
     */
    public function logVisit(int $userId, string $pageUrl, string $pageTitle, string $ipAddress, string $userAgent): bool
    {
        $db = Database::connection();
        $stmt = $db->prepare(
            "INSERT INTO " . db_table('user_activities') . " (user_id, page_url, page_title, ip_address, user_agent, visited_at)
             VALUES (:user_id, :page_url, :page_title, :ip_address, :user_agent, NOW())"
        );
        return $stmt->execute([
            ':user_id' => $userId,
            ':page_url' => $pageUrl,
            ':page_title' => $pageTitle,
            ':ip_address' => $ipAddress,
            ':user_agent' => $userAgent,
        ]);
    }

    /**
     * Обновить последнее посещение и IP.
     *
     * @param int $userId
     * @param string $ipAddress
     * @return bool
     */
    public function updateLastVisit(int $userId, string $ipAddress): bool
    {
        $db = Database::connection();
        $stmt = $db->prepare(
            "UPDATE " . db_table('users') . " SET last_visit = NOW(), last_ip = :ip_address WHERE id = :id"
        );
        return $stmt->execute([
            ':ip_address' => $ipAddress,
            ':id' => $userId,
        ]);
    }

    /**
     * Получить историю посещений пользователя.
     *
     * @param int $userId
     * @param int $page
     * @param int $perPage
     * @return array
     */
    public function getHistory(int $userId, int $page = 1, int $perPage = 20): array
    {
        $db = Database::connection();
        $offset = ($page - 1) * $perPage;

        $stmt = $db->prepare(
            "SELECT id, page_url, page_title, ip_address, user_agent, visited_at
             FROM " . db_table('user_activities') . "
             WHERE user_id = :user_id
             ORDER BY visited_at DESC
             LIMIT :limit OFFSET :offset"
        );
        $stmt->bindValue(':user_id', $userId, \PDO::PARAM_INT);
        $stmt->bindValue(':limit', $perPage, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Получить общее количество записей.
     *
     * @param int $userId
     * @return int
     */
    public function getTotalCount(int $userId): int
    {
        $db = Database::connection();
        $stmt = $db->prepare("SELECT COUNT(*) as count FROM " . db_table('user_activities') . " WHERE user_id = :user_id");
        $stmt->execute([':user_id' => $userId]);
        $result = $stmt->fetch();
        return (int) ($result['count'] ?? 0);
    }

    /**
     * Получить статистику по страницам.
     *
     * @param int $userId
     * @param int $limit
     * @return array
     */
    public function getPageStats(int $userId, int $limit = 10): array
    {
        $db = Database::connection();
        $stmt = $db->prepare(
            "SELECT page_url, page_title, COUNT(*) as visits
             FROM " . db_table('user_activities') . "
             WHERE user_id = :user_id
             GROUP BY page_url, page_title
             ORDER BY visits DESC
             LIMIT :limit"
        );
        $stmt->bindValue(':user_id', $userId, \PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Выражение CASE: браузер по user-agent (для фильтров и графиков).
     */
    public static function browserCaseSql(string $alias = 't0'): string
    {
        return "CASE
                WHEN {$alias}.user_agent LIKE '%Edg/%' THEN 'edge'
                WHEN {$alias}.user_agent LIKE '%OPR/%' OR {$alias}.user_agent LIKE '%Opr/%' THEN 'opera'
                WHEN {$alias}.user_agent LIKE '%Firefox/%' THEN 'firefox'
                WHEN {$alias}.user_agent LIKE '%Chrome/%' THEN 'chrome'
                WHEN {$alias}.user_agent LIKE '%Safari/%' THEN 'safari'
                ELSE 'other'
            END";
    }

    /**
     * Выражение CASE: раздел сайта по URL (слоги статей/проектов из публичного контента).
     */
    public static function sectionCaseSql(string $alias = 't0'): string
    {
        $content = require APP_ROOT . '/config/public-content.php';
        $articleUrls = array_map(
            static function ($slug) { return "'" . $slug . ".html'"; },
            array_keys(is_array($content) && isset($content['articles']) ? $content['articles'] : [])
        );
        $projectUrls = array_map(
            static function ($slug) { return "'" . $slug . ".html'"; },
            array_keys(is_array($content) && isset($content['projects']) ? $content['projects'] : [])
        );
        $articleIn = $articleUrls !== [] ? "{$alias}.page_url IN (" . implode(', ', $articleUrls) . ") OR " : '';
        $projectIn = $projectUrls !== [] ? "{$alias}.page_url IN (" . implode(', ', $projectUrls) . ") OR " : '';

        return "CASE
            WHEN {$alias}.page_url IN ('/', '/index.html', '/mvc', '/health') THEN 'home'
            WHEN {$articleIn}{$alias}.page_url = '/blog.html' OR {$alias}.page_url LIKE '/category/%' THEN 'blog'
            WHEN {$projectIn}{$alias}.page_url IN ('/portfolio.html', '/messages.html', '/orders.html', '/settings.html') OR {$alias}.page_url LIKE '/project-%' THEN 'portfolio'
            WHEN {$alias}.page_url IN ('/login.html', '/register.html', '/forgot-password.html', '/profile.html', '/logout.html')
                 OR {$alias}.page_url LIKE '/reset-password%' OR {$alias}.page_url LIKE '/api/auth%' OR {$alias}.page_url LIKE '/api/profile%' THEN 'auth'
            ELSE 'other'
        END";
    }

    /**
     * Посетители онлайн (уникальные IP за последние 15 минут).
     */
    public function onlineCount(): int
    {
        $db = Database::connection();
        return (int) $db->query(
            'SELECT COUNT(DISTINCT ip_address) FROM ' . db_table('user_activities') . ' WHERE visited_at >= (NOW() - INTERVAL 15 MINUTE)'
        )->fetchColumn();
    }

    /**
     * Посетители за сегодня (уникальные IP).
     */
    public function todayCount(): int
    {
        $db = Database::connection();
        return (int) $db->query(
            'SELECT COUNT(DISTINCT ip_address) FROM ' . db_table('user_activities') . ' WHERE visited_at >= CURDATE()'
        )->fetchColumn();
    }

    /**
     * Таблица трафика с фильтрами и пагинацией.
     *
     * @param array $filters date_from, date_to, time_from, time_to, browser, section, page (уже валидные)
     * @return array{rows: array, pagination: array}
     */
    public function traffic(array $filters): array
    {
        $db = Database::connection();
        $table = db_table('user_activities');

        $where = [];
        $params = [];

        if (!empty($filters['date_from'])) {
            $where[] = 'DATE(t.visited_at) >= :date_from';
            $params[':date_from'] = $filters['date_from'];
        }
        if (!empty($filters['date_to'])) {
            $where[] = 'DATE(t.visited_at) <= :date_to';
            $params[':date_to'] = $filters['date_to'];
        }
        if (!empty($filters['time_from'])) {
            $where[] = 'TIME(t.visited_at) >= :time_from';
            $params[':time_from'] = $filters['time_from'];
        }
        if (!empty($filters['time_to'])) {
            $where[] = 'TIME(t.visited_at) <= :time_to';
            $params[':time_to'] = $filters['time_to'];
        }
        if (!empty($filters['browser'])) {
            $where[] = 't.browser = :browser';
            $params[':browser'] = $filters['browser'];
        }
        if (!empty($filters['section'])) {
            $where[] = 't.section = :section';
            $params[':section'] = $filters['section'];
        }
        $whereSql = $where !== [] ? ' WHERE ' . implode(' AND ', $where) : '';

        $subQuery = "SELECT t0.id, t0.ip_address, t0.visited_at, t0.user_agent, t0.referrer, t0.page_url,
                " . self::browserCaseSql() . " AS browser,
                " . self::sectionCaseSql() . " AS section
            FROM {$table} t0";

        $countStmt = $db->prepare("SELECT COUNT(*) FROM ({$subQuery}) t{$whereSql}");
        $countStmt->execute($params);
        $total = (int) $countStmt->fetchColumn();

        $perPage = 15;
        $page = max(1, (int) ($filters['page'] ?? 1));
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

        return [
            'rows' => $stmt->fetchAll(),
            'pagination' => [
                'page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'pages' => $pages,
            ],
        ];
    }

    /**
     * Срез статистики для графиков раздела «Статистика сайта».
     *
     * @return array{total: int, lastClean: string, retentionDays: int, autoClean: bool,
     *               byDate: array, byMonth: array, byYear: array,
     *               byHour: array, byMinute: array, bySection: array, byBrowser: array}
     */
    public function overview(): array
    {
        $db = Database::connection();
        $t = db_table('user_activities');

        $total = (int) $db->query("SELECT COUNT(*) FROM {$t}")->fetchColumn();

        // По датам: последние 30 дней (пустые дни — нули)
        $byDate = [];
        for ($i = 29; $i >= 0; $i--) {
            $byDate[date('Y-m-d', strtotime("-{$i} days"))] = 0;
        }
        foreach ($db->query("SELECT DATE_FORMAT(visited_at, '%Y-%m-%d') AS d, COUNT(*) AS c FROM {$t} WHERE visited_at >= (CURDATE() - INTERVAL 29 DAY) GROUP BY d")->fetchAll() as $row) {
            if (isset($byDate[$row['d']])) {
                $byDate[$row['d']] = (int) $row['c'];
            }
        }

        // По месяцам: последние 12 месяцев
        $byMonth = [];
        for ($i = 11; $i >= 0; $i--) {
            $byMonth[date('Y-m', strtotime("first day of -{$i} months"))] = 0;
        }
        foreach ($db->query("SELECT DATE_FORMAT(visited_at, '%Y-%m') AS m, COUNT(*) AS c FROM {$t} WHERE visited_at >= (DATE_SUB(CURDATE(), INTERVAL 11 MONTH) + INTERVAL 1 DAY) GROUP BY m")->fetchAll() as $row) {
            if (isset($byMonth[$row['m']])) {
                $byMonth[$row['m']] = (int) $row['c'];
            }
        }

        // По годам, часам и минутам — за всё хранимое время
        $byYear = $db->query("SELECT CAST(YEAR(visited_at) AS CHAR) AS y, COUNT(*) AS c FROM {$t} GROUP BY y ORDER BY y")->fetchAll();

        $byHour = [];
        for ($h = 0; $h < 24; $h++) {
            $byHour[str_pad((string) $h, 2, '0', STR_PAD_LEFT)] = 0;
        }
        foreach ($db->query("SELECT LPAD(HOUR(visited_at), 2, '0') AS h, COUNT(*) AS c FROM {$t} GROUP BY h ORDER BY h")->fetchAll() as $row) {
            $byHour[$row['h']] = (int) $row['c'];
        }

        $byMinute = [];
        for ($m = 0; $m < 60; $m++) {
            $byMinute[str_pad((string) $m, 2, '0', STR_PAD_LEFT)] = 0;
        }
        foreach ($db->query("SELECT LPAD(MINUTE(visited_at), 2, '0') AS m, COUNT(*) AS c FROM {$t} GROUP BY m ORDER BY m")->fetchAll() as $row) {
            $byMinute[$row['m']] = (int) $row['c'];
        }

        $bySection = $db->query("SELECT " . self::sectionCaseSql() . " AS section, COUNT(*) AS c FROM {$t} t0 GROUP BY section ORDER BY c DESC")->fetchAll();
        $byBrowser = $db->query("SELECT " . self::browserCaseSql() . " AS browser, COUNT(*) AS c FROM {$t} t0 GROUP BY browser ORDER BY c DESC")->fetchAll();

        $settings = new SiteSettings();
        $lastClean = (string) $settings->get('statisticsLastCleanAt', '');

        return [
            'total' => $total,
            'lastClean' => $lastClean,
            'retentionDays' => (int) $settings->get('statisticsRetentionDays', 90),
            'autoClean' => (string) $settings->get('statisticsAutoClean', '1') !== '0',
            'byDate' => $byDate,
            'byMonth' => $byMonth,
            'byYear' => $byYear,
            'byHour' => $byHour,
            'byMinute' => $byMinute,
            'bySection' => $bySection,
            'byBrowser' => $byBrowser,
        ];
    }

    /**
     * Удалить записи посещений старше $days дней.
     *
     * @return int количество удалённых записей
     */
    public function cleanupOld(int $days): int
    {
        $db = Database::connection();
        $days = max(1, $days);
        $stmt = $db->prepare(
            'DELETE FROM ' . db_table('user_activities') . ' WHERE visited_at < (NOW() - INTERVAL ' . $days . ' DAY)'
        );
        $stmt->execute();
        return $stmt->rowCount();
    }

    /**
     * Автоматическая очистка по настройкам сайта (не чаще раза в сутки).
     */
    public function autoClean(): void
    {
        $settings = new SiteSettings();
        if ((string) $settings->get('statisticsAutoClean', '1') === '0') {
            return;
        }
        $days = (int) $settings->get('statisticsRetentionDays', 90);
        if ($days <= 0) {
            return;
        }

        $db = Database::connection();
        $table = db_table('site_settings');

        // Чистим, только если последней очистки не было или она старше суток
        $stmt = $db->prepare(
            "SELECT COUNT(*) FROM {$table}
             WHERE setting_key = 'statisticsLastCleanAt'
               AND setting_value <> ''
               AND setting_value >= (NOW() - INTERVAL 24 HOUR)"
        );
        $stmt->execute();
        if ((int) $stmt->fetchColumn() > 0) {
            return;
        }

        $this->cleanupOld($days);
        $db->exec(
            "UPDATE {$table} SET setting_value = (SELECT NOW()), updated_at = NOW() WHERE setting_key = 'statisticsLastCleanAt'"
        );
    }
}
