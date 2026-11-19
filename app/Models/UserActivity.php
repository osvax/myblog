<?php

namespace App\Models;

use App\Core\Database;

class UserActivity
{
    /**
     * Разделы сайта, используемые в статистике (sectionCaseSql / sectionOfUrl).
     */
    public const SECTIONS = ['home', 'blog', 'portfolio', 'auth', 'other'];

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
     * Выражение CASE: раздел сайта по URL.
     * Статьи — из БД (va_articles, уникальный индекс на slug) + статичный конфиг
     * как фолбэк; проекты — по префиксу /project- и статичному конфиги.
     */
    public static function sectionCaseSql(string $alias = 't0'): string
    {
        $content = require APP_ROOT . '/config/public-content.php';
        $articleUrls = array_map(
            static function ($slug) { return "'" . $slug . ".html'"; },
            array_map(static function ($slug) { return '/' . $slug; }, array_keys(is_array($content) && isset($content['articles']) ? $content['articles'] : []))
        );
        $projectUrls = array_map(
            static function ($slug) { return "'" . $slug . ".html'"; },
            array_keys(is_array($content) && isset($content['projects']) ? $content['projects'] : [])
        );
        $articleIn = $articleUrls !== [] ? "{$alias}.page_url IN (" . implode(', ', $articleUrls) . ") OR " : '';
        $projectIn = $projectUrls !== [] ? "{$alias}.page_url IN (" . implode(', ', $projectUrls) . ") OR " : '';

        // slug статьи = page_url без ведущего '/' и суффикса '.html' (односегментный URL)
        $articleSlugExpr = "LEFT(SUBSTRING({$alias}.page_url, 2), CHAR_LENGTH({$alias}.page_url) - 6)";
        $dbArticleExists = "({$alias}.page_url LIKE '/%.html' AND {$alias}.page_url NOT LIKE '/%/%' AND EXISTS (SELECT 1 FROM " . db_table('articles') . " a WHERE a.slug = {$articleSlugExpr}))";

        return "CASE
            WHEN {$alias}.page_url IN ('/', '/index.html', '/mvc', '/health') THEN 'home'
            WHEN {$alias}.page_url = '/blog.html' OR {$alias}.page_url LIKE '/category/%' OR {$articleIn}{$dbArticleExists} THEN 'blog'
            WHEN {$projectIn}{$alias}.page_url IN ('/portfolio.html', '/messages.html', '/orders.html', '/settings.html') OR {$alias}.page_url LIKE '/project-%' THEN 'portfolio'
            WHEN {$alias}.page_url IN ('/login.html', '/register.html', '/forgot-password.html', '/profile.html', '/logout.html')
                 OR {$alias}.page_url LIKE '/reset-password%' OR {$alias}.page_url LIKE '/api/auth%' OR {$alias}.page_url LIKE '/api/profile%' THEN 'auth'
            ELSE 'other'
        END";
    }

    /**
     * Раздел сайта по URL (PHP-версия sectionCaseSql, для фильтрации при записи).
     */
    public static function sectionOfUrl(string $url): string
    {
        $path = (string) (parse_url($url, PHP_URL_PATH) ?: $url);
        if ($path !== '/' && substr($path, -1) === '/') {
            $path = rtrim($path, '/');
        }

        $content = require APP_ROOT . '/config/public-content.php';
        $articleFiles = array_map(
            static function ($slug) { return '/' . $slug . '.html'; },
            array_keys(is_array($content) && isset($content['articles']) ? $content['articles'] : [])
        );

        if (in_array($path, ['/', '/index.html', '/mvc', '/health'], true)) {
            return 'home';
        }
        if ($path === '/blog.html' || strpos($path, '/category/') === 0) {
            return 'blog';
        }

        // Статья: slug из односегментного /{slug}.html — сначала БД, потом конфиг
        $slug = '';
        if (strlen($path) > 6 && substr($path, -5) === '.html' && strpos(substr($path, 1), '/') === false) {
            $slug = substr($path, 1, -5);
        }
        if ($slug !== '') {
            try {
                $stmt = Database::connection()->prepare(
                    "SELECT 1 FROM " . db_table('articles') . " WHERE slug = :slug LIMIT 1"
                );
                $stmt->execute([':slug' => $slug]);
                if ($stmt->fetch()) {
                    return 'blog';
                }
            } catch (\Exception $e) {
                // Таблица может отсутствовать — продолжаем по конфиги
            }
            if (in_array($path, $articleFiles, true)) {
                return 'blog';
            }
        }

        if (in_array($path, ['/portfolio.html', '/messages.html', '/orders.html', '/settings.html'], true)
            || strpos($path, '/project-') === 0) {
            return 'portfolio';
        }
        if (in_array($path, ['/login.html', '/register.html', '/forgot-password.html', '/profile.html', '/logout.html'], true)
            || strpos($path, '/reset-password') === 0 || strpos($path, '/api/auth') === 0 || strpos($path, '/api/profile') === 0) {
            return 'auth';
        }
        return 'other';
    }

    /**
     * Разделы, по которым ведётся статистика (site_settings.statisticsSections).
     * Ключ отсутствует или значение невалидно — включены все разделы.
     *
     * @return string[]
     */
    public static function allowedSections(): array
    {
        try {
            $raw = (string) (new SiteSettings())->get('statisticsSections', '');
        } catch (\Exception $e) {
            return self::SECTIONS;
        }
        if ($raw === '') {
            return self::SECTIONS;
        }
        $decoded = json_decode($raw, true);
        if (!is_array($decoded)) {
            return self::SECTIONS;
        }
        return array_values(array_intersect(self::SECTIONS, $decoded));
    }

    /**
     * SQL-условие «раздел входит в разрешённый список».
     * Пустая строка — если включены все разделы.
     *
     * @param string $alias Алиас таблицы в запросе
     * @param string[] $allowed Разделы из allowedSections()
     * @param array $params Параметры запроса (дополняются)
     */
    private static function sectionConditionSql(string $alias, array $allowed, array &$params): string
    {
        if (count($allowed) === count(self::SECTIONS)) {
            return '';
        }
        $placeholders = [];
        foreach ($allowed as $index => $section) {
            $name = ':stat_section_' . $index;
            $placeholders[] = $name;
            $params[$name] = $section;
        }
        return '(' . self::sectionCaseSql($alias) . ' IN (' . implode(', ', $placeholders) . '))';
    }

    /**
     * SQL-условие «материализованная колонка section входит в разрешённый список»
     * (для внешних запросов, где CASE уже вычислен во вложенном).
     * Пустая строка — если включены все разделы.
     *
     * @param string $alias Алиас таблицы с колонкой section
     * @param string[] $allowed Разделы из allowedSections()
     * @param array $params Параметры запроса (дополняются)
     */
    private static function sectionInSql(string $alias, array $allowed, array &$params): string
    {
        if (count($allowed) === count(self::SECTIONS)) {
            return '';
        }
        $placeholders = [];
        foreach ($allowed as $index => $section) {
            $name = ':stat_section_' . $index;
            $placeholders[] = $name;
            $params[$name] = $section;
        }
        return "{$alias}.section IN (" . implode(', ', $placeholders) . ')';
    }

    /**
     * Счётчики по группам с фильтром включённых разделов.
     *
     * @param string $groupExpr Выражение группировки (алиас t0)
     * @param string $keyAlias Алиас ключа группы
     * @param string|null $extraWhere Дополнительное условие (алиас t0)
     * @param string[] $allowed Разделы из allowedSections()
     * @return array Ключ группы => количество
     */
    private function groupedCounts(string $groupExpr, string $keyAlias, ?string $extraWhere, array $allowed): array
    {
        $db = Database::connection();
        $params = [];
        $where = '1=1';
        $condition = self::sectionConditionSql('t0', $allowed, $params);
        if ($condition !== '') {
            $where .= ' AND ' . $condition;
        }
        if ($extraWhere !== null && $extraWhere !== '') {
            $where .= ' AND (' . $extraWhere . ')';
        }

        $stmt = $db->prepare(
            "SELECT {$groupExpr} AS {$keyAlias}, COUNT(*) AS c
             FROM " . db_table('user_activities') . " t0
             WHERE {$where}
             GROUP BY {$keyAlias}"
        );
        $stmt->execute($params);

        $result = [];
        foreach ($stmt->fetchAll() as $row) {
            $result[(string) $row[$keyAlias]] = (int) $row['c'];
        }
        return $result;
    }

    /**
     * Посетители онлайн (уникальные IP за последние 15 минут),
     * только по включённым в статистику разделам.
     */
    public function onlineCount(): int
    {
        $db = Database::connection();
        $params = [];
        $condition = self::sectionConditionSql('t0', self::allowedSections(), $params);
        $sql = 'SELECT COUNT(DISTINCT t0.ip_address) FROM ' . db_table('user_activities') . ' t0'
            . ' WHERE t0.visited_at >= (NOW() - INTERVAL 15 MINUTE)';
        if ($condition !== '') {
            $sql .= ' AND ' . $condition;
        }
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Посетители за сегодня (уникальные IP), только по включённым разделам.
     */
    public function todayCount(): int
    {
        $db = Database::connection();
        $params = [];
        $condition = self::sectionConditionSql('t0', self::allowedSections(), $params);
        $sql = 'SELECT COUNT(DISTINCT t0.ip_address) FROM ' . db_table('user_activities') . ' t0'
            . ' WHERE t0.visited_at >= CURDATE()';
        if ($condition !== '') {
            $sql .= ' AND ' . $condition;
        }
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
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
        // Только включённые в статистику разделы (по колонке section из вложенного запроса)
        $allowedIn = self::sectionInSql('t', self::allowedSections(), $params);
        if ($allowedIn !== '') {
            $where[] = $allowedIn;
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
     * Самая ранняя дата ('day', Y-m-d) или месяц ('month', Y-m) хранимых записей
     * по включённым разделам. Пустая строка — записей нет.
     *
     * @param string[] $allowed Разделы из allowedSections()
     * @param string $mode 'day' или 'month'
     */
    private function firstRangeValue(array $allowed, string $mode): string
    {
        $db = Database::connection();
        $params = [];
        $condition = self::sectionConditionSql('t0', $allowed, $params);
        $format = $mode === 'month' ? '%Y-%m' : '%Y-%m-%d';
        $stmt = $db->prepare(
            "SELECT MIN(DATE_FORMAT(t0.visited_at, '{$format}')) FROM " . db_table('user_activities') . " t0"
            . ($condition !== '' ? ' WHERE ' . $condition : '')
        );
        $stmt->execute($params);
        $value = $stmt->fetchColumn();
        return $value === null ? '' : (string) $value;
    }

    /**
     * Срез статистики для графиков раздела «Статистика сайта».
     * Учитываются только включённые в статистику разделы.
     *
     * @return array{total: int, lastClean: string, retentionDays: int, autoClean: bool,
     *               statisticsSections: array, byDate: array, byMonth: array, byYear: array,
     *               byHour: array, byMinute: array, bySection: array, byBrowser: array}
     */
    public function overview(): array
    {
        $db = Database::connection();
        $allowed = self::allowedSections();

        $totalParams = [];
        $totalCondition = self::sectionConditionSql('t0', $allowed, $totalParams);
        $totalStmt = $db->prepare(
            "SELECT COUNT(*) FROM " . db_table('user_activities') . " t0"
            . ($totalCondition !== '' ? ' WHERE ' . $totalCondition : '')
        );
        $totalStmt->execute($totalParams);
        $total = (int) $totalStmt->fetchColumn();

        // По дням: всё хранимое время (от самой ранней записи до сегодня; без данных — последние 30 дней)
        $endTs = strtotime(date('Y-m-d'));
        $firstDay = $this->firstRangeValue($allowed, 'day');
        if ($firstDay === '' || strtotime($firstDay) > $endTs) {
            $firstDay = date('Y-m-d', strtotime('-29 days'));
        }
        $byDate = [];
        for ($ts = strtotime($firstDay); $ts <= $endTs; $ts = strtotime('+1 day', $ts)) {
            $byDate[date('Y-m-d', $ts)] = 0;
        }
        foreach ($this->groupedCounts("DATE_FORMAT(t0.visited_at, '%Y-%m-%d')", 'd', null, $allowed) as $day => $count) {
            if (isset($byDate[$day])) {
                $byDate[$day] = $count;
            }
        }

        // По месяцам: всё хранимое время (без данных — последние 12 месяцев)
        $firstMonth = $this->firstRangeValue($allowed, 'month');
        if ($firstMonth === '' || strtotime($firstMonth . '-01') > strtotime(date('Y-m-01'))) {
            $firstMonth = date('Y-m', strtotime('first day of -11 months'));
        }
        $byMonth = [];
        $cursor = $firstMonth . '-01';
        while (strtotime($cursor) <= strtotime(date('Y-m-01'))) {
            $byMonth[date('Y-m', strtotime($cursor))] = 0;
            $cursor = date('Y-m-01', strtotime('+1 month', strtotime($cursor)));
        }
        foreach ($this->groupedCounts("DATE_FORMAT(t0.visited_at, '%Y-%m')", 'm', null, $allowed) as $month => $count) {
            if (isset($byMonth[$month])) {
                $byMonth[$month] = $count;
            }
        }

        // По годам — за всё хранимое время
        $byYear = [];
        foreach ($this->groupedCounts('CAST(YEAR(t0.visited_at) AS CHAR)', 'y', null, $allowed) as $year => $count) {
            $byYear[] = ['y' => $year, 'c' => $count];
        }
        usort($byYear, static function ($a, $b) { return strcmp($a['y'], $b['y']); });

        $byHour = [];
        for ($h = 0; $h < 24; $h++) {
            $byHour[str_pad((string) $h, 2, '0', STR_PAD_LEFT)] = 0;
        }
        foreach ($this->groupedCounts("LPAD(HOUR(t0.visited_at), 2, '0')", 'h', null, $allowed) as $hour => $count) {
            if (isset($byHour[$hour])) {
                $byHour[$hour] = $count;
            }
        }

        $byMinute = [];
        for ($m = 0; $m < 60; $m++) {
            $byMinute[str_pad((string) $m, 2, '0', STR_PAD_LEFT)] = 0;
        }
        foreach ($this->groupedCounts("LPAD(MINUTE(t0.visited_at), 2, '0')", 'm', null, $allowed) as $minute => $count) {
            if (isset($byMinute[$minute])) {
                $byMinute[$minute] = $count;
            }
        }

        // По разделам (только включённые) и браузерам
        $bySection = [];
        foreach ($this->groupedCounts(self::sectionCaseSql('t0'), 'section', null, $allowed) as $section => $count) {
            $bySection[] = ['section' => $section, 'c' => $count];
        }
        usort($bySection, static function ($a, $b) { return $b['c'] <=> $a['c']; });

        $byBrowser = [];
        foreach ($this->groupedCounts(self::browserCaseSql('t0'), 'browser', null, $allowed) as $browser => $count) {
            $byBrowser[] = ['browser' => $browser, 'c' => $count];
        }
        usort($byBrowser, static function ($a, $b) { return $b['c'] <=> $a['c']; });

        $settings = new SiteSettings();
        $lastClean = (string) $settings->get('statisticsLastCleanAt', '');

        return [
            'total' => $total,
            'lastClean' => $lastClean,
            'retentionDays' => (int) $settings->get('statisticsRetentionDays', 90),
            'autoClean' => (string) $settings->get('statisticsAutoClean', '1') !== '0',
            'statisticsSections' => $allowed,
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
     * Посещения по минутам в пределах одного часа (drill-down для графика «по часам»).
     *
     * @param int $hour Час суток 0–23
     * @return array Минута ('00'–'59') => количество посещений
     */
    public function hourDetail(int $hour): array
    {
        $hour = max(0, min(23, $hour));
        $byMinute = [];
        for ($m = 0; $m < 60; $m++) {
            $byMinute[str_pad((string) $m, 2, '0', STR_PAD_LEFT)] = 0;
        }
        foreach ($this->groupedCounts(
            "LPAD(MINUTE(t0.visited_at), 2, '0')", 'm',
            'HOUR(t0.visited_at) = ' . $hour,
            self::allowedSections()
        ) as $minute => $count) {
            if (isset($byMinute[$minute])) {
                $byMinute[$minute] = $count;
            }
        }
        return $byMinute;
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
