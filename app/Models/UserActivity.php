<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\QueryBuilder;
use App\DTO\UserActivityDTO;
use PDO;

/**
 * UserActivity — активность пользователей (статистика посещений).
 *
 * === МИГРАЦИЯ ===
 * - Конструктор принимает PDO (для тестов — SQLite :memory:)
 * - QueryBuilder для простых запросов, raw PDO для сложных CASE/GROUP BY
 * - Возвращает UserActivityDTO вместо массивов
 * - Статические методы-обёртки для обратной совместимости
 */
final class UserActivity
{
    private PDO $db;
    private string $table;

    /**
     * @param PDO|null $db PDO соединение (по умолчанию Database::connection())
     */
    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? \App\Core\Database::connection();
        $this->table = db_table('user_activities');
    }

    /** @var string[] Разделы сайта, используемые в статистике */
    public const SECTIONS = ['home', 'blog', 'portfolio', 'auth', 'other'];

    // ===================== ОБРАТНАЯ СОВМЕСТИМОСТЬ (статические) =====================

    /** @return bool */
    public static function logVisit(
        int $userId,
        string $pageUrl,
        string $pageTitle,
        string $ipAddress,
        string $userAgent
    ): bool {
        return (new self())->logVisitNew($userId, $pageUrl, $pageTitle, $ipAddress, $userAgent);
    }

    /** @return bool */
    public static function updateLastVisit(int $userId, string $ipAddress): bool
    {
        return (new self())->updateLastVisitNew($userId, $ipAddress);
    }

    /** @return array<int, array<string, mixed>> */
    public static function getHistory(int $userId, int $page = 1, int $perPage = 20): array
    {
        return array_map(
            fn (UserActivityDTO $dto) => $dto->toApiArray(),
            (new self())->getHistoryNew($userId, $page, $perPage)
        );
    }

    /** @return int */
    public static function getTotalCount(int $userId): int
    {
        return (new self())->getTotalCountNew($userId);
    }

    /** @return array<int, array<string, mixed>> */
    public static function getPageStats(int $userId, int $limit = 10): array
    {
        return array_map(
            fn (UserActivityDTO $dto) => $dto->toApiArray(),
            (new self())->getPageStatsNew($userId, $limit)
        );
    }

    /** @return string */
    public static function browserCaseSql(string $alias = 't0'): string
    {
        return (new self())->browserCaseSqlNew($alias);
    }

    /** @return string */
    public static function sectionCaseSql(string $alias = 't0'): string
    {
        return (new self())->sectionCaseSqlNew($alias);
    }

    /** @return string */
    public static function sectionOfUrl(string $url): string
    {
        return (new self())->sectionOfUrlNew($url);
    }

    /** @return string[] */
    public static function parseSections($raw): array
    {
        return (new self())->parseSectionsNew($raw);
    }

    /** @return string[] */
    public static function allowedSections(): array
    {
        return (new self())->allowedSectionsNew();
    }

    /** @return int */
    public static function deleteSections(array $sections): int
    {
        return (new self())->deleteSectionsNew($sections);
    }

    /** @return int */
    public static function onlineCount(): int
    {
        return (new self())->onlineCountNew();
    }

    /** @return int */
    public static function todayCount(): int
    {
        return (new self())->todayCountNew();
    }

    /** @return array{rows: array<int, array<string, mixed>>, pagination: array<string, mixed>} */
    public static function traffic(array $filters): array
    {
        $result = (new self())->trafficNew($filters);
        return [
            'rows' => array_map(fn (UserActivityDTO $dto) => $dto->toApiArray(), $result['rows']),
            'pagination' => $result['pagination'],
        ];
    }

    /** @return array{total: int, lastClean: string, retentionDays: int, autoClean: bool,
        statisticsSections: array<int, string>, byDate: array<string, int>, byWeek: array<string, int>,
        byMonth: array<string, int>, byYear: array<string, int>, byHour: array<string, int>,
        byMinute: array<string, int>, bySection: array<int, array<string, mixed>>,
        byBrowser: array<int, array<string, mixed>>} */
    public static function overview(): array
    {
        return (new self())->overviewNew();
    }

    /** @return array<string, int> */
    public static function hourDetail(int $hour): array
    {
        return (new self())->hourDetailNew($hour);
    }

    /** @return int */
    public static function cleanupOld(int $days): int
    {
        return (new self())->cleanupOldNew($days);
    }

    /** @return void */
    public static function autoClean(): void
    {
        (new self())->autoCleanNew();
    }

    // ===================== НОВЫЙ API (DTO-based) =====================

    /** @return bool Записать посещение страницы пользователем */
    public function logVisitNew(
        int $userId,
        string $pageUrl,
        string $pageTitle,
        string $ipAddress,
        string $userAgent
    ): bool {
        $dto = UserActivityDTO::create($userId, $pageUrl, $pageTitle, $ipAddress, $userAgent);
        $data = $dto->toArray();

        $columns = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));
        $sql = "INSERT INTO {$this->table} ($columns) VALUES ($placeholders)";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute($data);
    }

    /** @return bool Обновить последнее посещение и IP пользователя */
    public function updateLastVisitNew(int $userId, string $ipAddress): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE ' . db_table('users') . ' SET last_visit = NOW(), last_ip = :ip_address WHERE id = :id'
        );
        return $stmt->execute([
            ':ip_address' => $ipAddress,
            ':id' => $userId,
        ]);
    }

    /** @return UserActivityDTO[] История посещений пользователя */
    public function getHistoryNew(int $userId, int $page = 1, int $perPage = 20): array
    {
        $offset = ($page - 1) * $perPage;

        $rows = (new QueryBuilder($this->db, $this->table))
            ->select(['id', 'page_url', 'page_title', 'ip_address', 'user_agent', 'referrer', 'visited_at'])
            ->where('user_id', '=', $userId)
            ->orderBy('visited_at', 'DESC')
            ->limit($perPage)
            ->offset($offset)
            ->get();

        return array_map([UserActivityDTO::class, 'fromDatabase'], $rows);
    }

    /** @return int Общее количество записей пользователя */
    public function getTotalCountNew(int $userId): int
    {
        $row = (new QueryBuilder($this->db, $this->table))
            ->select(['COUNT(*) as count'])
            ->where('user_id', '=', $userId)
            ->first();

        return (int) ($row['count'] ?? 0);
    }

    /** @return UserActivityDTO[] Статистика по страницам пользователя */
    public function getPageStatsNew(int $userId, int $limit = 10): array
    {
        $rows = (new QueryBuilder($this->db, $this->table))
            ->select(['page_url', 'page_title', 'COUNT(*) as visits'])
            ->where('user_id', '=', $userId)
            ->groupBy('page_url, page_title')
            ->orderBy('visits', 'DESC')
            ->limit($limit)
            ->get();

        return array_map([UserActivityDTO::class, 'fromDatabase'], $rows);
    }

    /** @return string Выражение CASE: браузер по user-agent */
    public function browserCaseSqlNew(string $alias = 't0'): string
    {
        return self::browserCaseSql($alias);
    }

    /** @return string Выражение CASE: раздел сайта по URL */
    public function sectionCaseSqlNew(string $alias = 't0'): string
    {
        // slug статьи = page_url без ведущего '/' и суффикса '.html' (односегментный URL)
        $articleSlugExpr = "LEFT(SUBSTRING({$alias}.page_url, 2), CHAR_LENGTH({$alias}.page_url) - 6)";
        $dbArticleExists = "({$alias}.page_url LIKE '/%.html' AND {$alias}.page_url NOT LIKE '/%/%' AND EXISTS (SELECT 1 FROM " . db_table('articles') . " a WHERE a.slug = {$articleSlugExpr}))";
        $dbProjectExists = "({$alias}.page_url LIKE '/project-%.html' AND EXISTS (SELECT 1 FROM " . db_table('portfolio_projects') . " p WHERE CONCAT('/', p.slug, '.html') = {$alias}.page_url))";

        return "CASE
            WHEN {$alias}.page_url IN ('/', '/index.html', '/mvc', '/health') THEN 'home'
            WHEN {$alias}.page_url = '/blog.html' OR {$alias}.page_url LIKE '/category/%' OR {$dbArticleExists} THEN 'blog'
            WHEN {$dbProjectExists} OR {$alias}.page_url IN ('/portfolio.html', '/messages.html', '/orders.html', '/settings.html') THEN 'portfolio'
            WHEN {$alias}.page_url IN ('/login.html', '/register.html', '/forgot-password.html', '/profile.html', '/logout.html')
                 OR {$alias}.page_url LIKE '/reset-password%' OR {$alias}.page_url LIKE '/api/auth%' OR {$alias}.page_url LIKE '/api/profile%' THEN 'auth'
            ELSE 'other'
        END";
    }

    /** @return string Раздел сайта по URL (PHP версия) */
    public function sectionOfUrlNew(string $url): string
    {
        return self::sectionOfUrl($url);
    }

    /** @return string[] Разобрать значение настройки statisticsSections */
    public function parseSectionsNew($raw): array
    {
        return self::parseSections($raw);
    }

    /** @return string[] Разделы, по которым ведётся статистика */
    public function allowedSectionsNew(): array
    {
        try {
            $raw = (new SiteSettings())->get('statisticsSections', '');
        } catch (\Exception $e) {
            return self::SECTIONS;
        }
        return self::parseSections($raw);
    }

    /** @return int Удалить все записи посещений по указанным разделам */
    public function deleteSectionsNew(array $sections): int
    {
        $sections = array_values(array_intersect(self::SECTIONS, $sections));
        if ($sections === []) {
            return 0;
        }

        $params = [];
        $in = [];
        foreach ($sections as $index => $section) {
            $name = ':stat_section_del_' . $index;
            $in[] = $name;
            $params[$name] = $section;
        }

        $stmt = $this->db->prepare(
            'DELETE t0 FROM ' . $this->table . ' t0
             WHERE (' . self::sectionCaseSql('t0') . ') IN (' . implode(', ', $in) . ')'
        );
        $stmt->execute($params);
        return (int) $stmt->rowCount();
    }

    /** @return int Посетители онлайн (уникальные IP за последние 15 минут) */
    public function onlineCountNew(): int
    {
        $params = [];
        $condition = $this->sectionConditionSql('t0', self::allowedSections(), $params);
        $sql = 'SELECT COUNT(DISTINCT t0.ip_address) FROM ' . $this->table . ' t0'
            . ' WHERE t0.visited_at >= (NOW() - INTERVAL 15 MINUTE)';
        if ($condition !== '') {
            $sql .= ' AND ' . $condition;
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    /** @return int Посетители за сегодня (уникальные IP) */
    public function todayCountNew(): int
    {
        $params = [];
        $condition = $this->sectionConditionSql('t0', self::allowedSections(), $params);
        $sql = 'SELECT COUNT(DISTINCT t0.ip_address) FROM ' . $this->table . ' t0'
            . ' WHERE t0.visited_at >= CURDATE()';
        if ($condition !== '') {
            $sql .= ' AND ' . $condition;
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    /** @return array{rows: UserActivityDTO[], pagination: array<string, mixed>} Таблица трафика с фильтрами и пагинацией */
    public function trafficNew(array $filters): array
    {
        $table = $this->table;

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
        $allowedIn = $this->sectionInSql('t', self::allowedSections(), $params);
        if ($allowedIn !== '') {
            $where[] = $allowedIn;
        }
        $whereSql = $where !== [] ? ' WHERE ' . implode(' AND ', $where) : '';

        $subQuery = 'SELECT t0.id, t0.ip_address, t0.visited_at, t0.user_agent, t0.referrer, t0.page_url,
                ' . self::browserCaseSql() . ' AS browser,
                ' . self::sectionCaseSql() . " AS section
            FROM {$table} t0";

        $countStmt = $this->db->prepare("SELECT COUNT(*) FROM ({$subQuery}) t{$whereSql}");
        $countStmt->execute($params);
        $total = (int) $countStmt->fetchColumn();

        $perPage = 15;
        $page = max(1, (int) ($filters['page'] ?? 1));
        $pages = max(1, (int) ceil($total / $perPage));
        if ($page > $pages) {
            $page = $pages;
        }
        $offset = ($page - 1) * $perPage;

        $stmt = $this->db->prepare(
            "SELECT t.ip_address, t.visited_at, t.user_agent, t.referrer, t.page_url,
                    t.browser, t.section
             FROM ({$subQuery}) t{$whereSql}
             ORDER BY t.visited_at DESC, t.id DESC
             LIMIT {$perPage} OFFSET {$offset}"
        );
        $stmt->execute($params);

        $rows = array_map([UserActivityDTO::class, 'fromDatabase'], $stmt->fetchAll());

        return [
            'rows' => $rows,
            'pagination' => [
                'page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'pages' => $pages,
            ],
        ];
    }

    /** @return array Срез статистики для графиков раздела «Статистика сайта» */
    public function overviewNew(): array
    {
        $allowed = self::allowedSections();

        $totalParams = [];
        $totalCondition = $this->sectionConditionSql('t0', $allowed, $totalParams);
        $totalStmt = $this->db->prepare(
            'SELECT COUNT(*) FROM ' . $this->table . ' t0'
            . ($totalCondition !== '' ? ' WHERE ' . $totalCondition : '')
        );
        $totalStmt->execute($totalParams);
        $total = (int) $totalStmt->fetchColumn();

        // По дням: всё хранимое время (от самой ранней записи до сегодня; без данных — последние 30 дней)
        $dayExpr = "DATE_FORMAT(t0.visited_at, '%Y-%m-%d')";
        $endTs = strtotime(date('Y-m-d'));
        $firstDay = $this->firstRangeValue($allowed, $dayExpr);
        if ($firstDay === '' || strtotime($firstDay) > $endTs) {
            $firstDay = date('Y-m-d', strtotime('-29 days'));
        }
        $byDate = [];
        for ($ts = strtotime($firstDay); $ts <= $endTs; $ts = strtotime('+1 day', $ts)) {
            $byDate[date('Y-m-d', $ts)] = 0;
        }
        foreach ($this->groupedCounts($dayExpr, 'd', null, $allowed) as $day => $count) {
            if (isset($byDate[$day])) {
                $byDate[$day] = $count;
            }
        }

        // По неделям: ключ — понедельник недели (без данных — последние 4 недели)
        $weekExpr = "DATE_FORMAT(DATE_SUB(t0.visited_at, INTERVAL WEEKDAY(t0.visited_at) DAY), '%Y-%m-%d')";
        $thisWeekMonday = strtotime(date('Y-m-d', strtotime('monday this week')));
        $firstWeek = $this->firstRangeValue($allowed, $weekExpr);
        if ($firstWeek === '' || strtotime($firstWeek) > $thisWeekMonday) {
            $firstWeek = date('Y-m-d', strtotime('monday this week -3 weeks'));
        }
        $byWeek = [];
        for ($ts = strtotime($firstWeek); $ts <= $thisWeekMonday; $ts = strtotime('+1 week', $ts)) {
            $byWeek[date('Y-m-d', $ts)] = 0;
        }
        foreach ($this->groupedCounts($weekExpr, 'w', null, $allowed) as $week => $count) {
            if (isset($byWeek[$week])) {
                $byWeek[$week] = $count;
            }
        }

        // По месяцам: всё хранимое время (без данных — последние 12 месяцев)
        $monthExpr = "DATE_FORMAT(t0.visited_at, '%Y-%m')";
        $firstMonth = $this->firstRangeValue($allowed, $monthExpr);
        if ($firstMonth === '' || strtotime($firstMonth . '-01') > strtotime(date('Y-m-01'))) {
            $firstMonth = date('Y-m', strtotime('first day of -11 months'));
        }
        $byMonth = [];
        $cursor = $firstMonth . '-01';
        while (strtotime($cursor) <= strtotime(date('Y-m-01'))) {
            $byMonth[date('Y-m', strtotime($cursor))] = 0;
            $cursor = date('Y-m-01', strtotime('+1 month', strtotime($cursor)));
        }
        foreach ($this->groupedCounts($monthExpr, 'm', null, $allowed) as $month => $count) {
            if (isset($byMonth[$month])) {
                $byMonth[$month] = $count;
            }
        }

        // По годам: всё хранимое время (без данных — текущий год)
        $firstYear = $this->firstRangeValue($allowed, 'YEAR(t0.visited_at)');
        if ($firstYear === '' || (int) $firstYear > (int) date('Y')) {
            $firstYear = (string) date('Y');
        }
        $byYear = [];
        for ($year = (int) $firstYear; $year <= (int) date('Y'); $year++) {
            $byYear[(string) $year] = 0;
        }
        foreach ($this->groupedCounts('YEAR(t0.visited_at)', 'y', null, $allowed) as $year => $count) {
            $year = (string) $year;
            if (isset($byYear[$year])) {
                $byYear[$year] = $count;
            }
        }

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
        usort($bySection, static function ($a, $b) {
            return $b['c'] <=> $a['c'];
        });

        $byBrowser = [];
        foreach ($this->groupedCounts(self::browserCaseSql('t0'), 'browser', null, $allowed) as $browser => $count) {
            $byBrowser[] = ['browser' => $browser, 'c' => $count];
        }
        usort($byBrowser, static function ($a, $b) {
            return $b['c'] <=> $a['c'];
        });

        $settings = new SiteSettings();
        $lastClean = (string) $settings->get('statisticsLastCleanAt', '');

        return [
            'total' => $total,
            'lastClean' => $lastClean,
            'retentionDays' => (int) $settings->get('statisticsRetentionDays', 90),
            'autoClean' => (string) $settings->get('statisticsAutoClean', '1') !== '0',
            'statisticsSections' => $allowed,
            'byDate' => $byDate,
            'byWeek' => $byWeek,
            'byMonth' => $byMonth,
            'byYear' => $byYear,
            'byHour' => $byHour,
            'byMinute' => $byMinute,
            'bySection' => $bySection,
            'byBrowser' => $byBrowser,
        ];
    }

    /** @return array<string, int> Посещения по минутам в пределах одного часа */
    public function hourDetailNew(int $hour): array
    {
        $hour = max(0, min(23, $hour));
        $byMinute = [];
        for ($m = 0; $m < 60; $m++) {
            $byMinute[str_pad((string) $m, 2, '0', STR_PAD_LEFT)] = 0;
        }
        foreach ($this->groupedCounts(
            "LPAD(MINUTE(t0.visited_at), 2, '0')",
            'm',
            'HOUR(t0.visited_at) = ' . $hour,
            self::allowedSections()
        ) as $minute => $count) {
            if (isset($byMinute[$minute])) {
                $byMinute[$minute] = $count;
            }
        }
        return $byMinute;
    }

    /** @return int Удалить записи посещений старше $days дней */
    public function cleanupOldNew(int $days): int
    {
        $days = max(1, $days);
        $stmt = $this->db->prepare(
            'DELETE FROM ' . $this->table . ' WHERE visited_at < (NOW() - INTERVAL ' . $days . ' DAY)'
        );
        $stmt->execute();
        return $stmt->rowCount();
    }

    /** @return void Автоматическая очистка по настройкам сайта (не чаще раза в сутки) */
    public function autoCleanNew(): void
    {
        $settings = new SiteSettings();
        if ((string) $settings->get('statisticsAutoClean', '1') === '0') {
            return;
        }
        $days = (int) $settings->get('statisticsRetentionDays', 90);
        if ($days <= 0) {
            return;
        }

        $table = db_table('site_settings');

        // Чистим, только если последней очистки не было или она старше суток
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM {$table}
             WHERE setting_key = 'statisticsLastCleanAt'
               AND setting_value <> ''
               AND setting_value >= (NOW() - INTERVAL 24 HOUR)"
        );
        $stmt->execute();
        if ((int) $stmt->fetchColumn() > 0) {
            return;
        }

        $this->cleanupOldNew($days);
        $this->db->exec(
            "UPDATE {$table} SET setting_value = (SELECT NOW()), updated_at = NOW() WHERE setting_key = 'statisticsLastCleanAt'"
        );
    }

    // ===================== PRIVATE HELPERS =====================

    /**
     * SQL-условие «раздел входит в разрешённый список».
     * Пустая строка — если включены все разделы.
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
     * @return array<string, int> Ключ группы => количество
     */
    private function groupedCounts(string $groupExpr, string $keyAlias, ?string $extraWhere, array $allowed): array
    {
        $params = [];
        $where = '1=1';
        $condition = self::sectionConditionSql('t0', $allowed, $params);
        if ($condition !== '') {
            $where .= ' AND ' . $condition;
        }
        if ($extraWhere !== null && $extraWhere !== '') {
            $where .= ' AND (' . $extraWhere . ')';
        }

        $stmt = $this->db->prepare(
            "SELECT {$groupExpr} AS {$keyAlias}, COUNT(*) AS c
             FROM {$this->table} t0
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
     * Минимальное значение группировочного выражения по включённым разделам.
     * Пустая строка — записей нет.
     */
    private function firstRangeValue(array $allowed, string $expr): string
    {
        $params = [];
        $condition = self::sectionConditionSql('t0', $allowed, $params);
        $stmt = $this->db->prepare(
            'SELECT MIN(' . $expr . ') FROM ' . $this->table . ' t0'
            . ($condition !== '' ? ' WHERE ' . $condition : '')
        );
        $stmt->execute($params);
        $value = $stmt->fetchColumn();
        return $value === null ? '' : (string) $value;
    }
}
