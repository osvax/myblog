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

        $activity = new \App\Models\UserActivity();
        $traffic = $activity->traffic($this->validatedTrafficFilters());

        return $this->json([
            'success' => true,
            'online' => $activity->onlineCount(),
            'today' => $activity->todayCount(),
            'traffic' => $traffic['rows'],
            'pagination' => $traffic['pagination'],
        ]);
    }

    /**
     * Срез статистики для графиков раздела «Статистика сайта».
     */
    public function overview($request)
    {
        if (!\admin_is_authenticated()) {
            return $this->json(['success' => false, 'error' => 'Требуется авторизация'], 401);
        }
        if ($request->method() !== 'GET') {
            return $this->json(['success' => false, 'error' => 'Метод не поддерживается'], 405);
        }

        $data = (new \App\Models\UserActivity())->overview();
        return $this->json(array_merge(['success' => true], $data));
    }

    /**
     * Таблица «Трафик сайта» (раздел статистики) — те же фильтры и пагинация,
     * что и блок на дашборде.
     */
    public function traffic($request)
    {
        if (!\admin_is_authenticated()) {
            return $this->json(['success' => false, 'error' => 'Требуется авторизация'], 401);
        }
        if ($request->method() !== 'GET') {
            return $this->json(['success' => false, 'error' => 'Метод не поддерживается'], 405);
        }

        $traffic = (new \App\Models\UserActivity())->traffic($this->validatedTrafficFilters());
        return $this->json([
            'success' => true,
            'traffic' => $traffic['rows'],
            'pagination' => $traffic['pagination'],
        ]);
    }

    /**
     * Ручная очистка: удалить записи старше установленного срока хранения.
     */
    public function clean($request)
    {
        if (!\admin_is_authenticated()) {
            return $this->json(['success' => false, 'error' => 'Требуется авторизация'], 401);
        }
        if ($request->method() !== 'POST') {
            return $this->json(['success' => false, 'error' => 'Метод не поддерживается'], 405);
        }
        \admin_require_csrf();

        $settings = new \App\Models\SiteSettings();
        $days = (int) $settings->get('statisticsRetentionDays', 90);
        if ($days <= 0) {
            return $this->json(['success' => false, 'error' => 'Срок хранения «Без ограничения» — данные не удаляются. Установите срок хранения, чтобы очистить старые записи.']);
        }

        $deleted = (new \App\Models\UserActivity())->cleanupOld($days);
        \App\Core\Database::connection()->exec(
            "UPDATE " . db_table('site_settings') . " SET setting_value = (SELECT NOW()), updated_at = NOW() WHERE setting_key = 'statisticsLastCleanAt'"
        );
        return $this->json(['success' => true, 'deleted' => $deleted]);
    }

    /**
     * Валидные фильтры трафика из GET: мусор и инъекции просто игнорируются.
     *
     * @return array
     */
    private function validatedTrafficFilters(): array
    {
        $filters = ['page' => max(1, (int) ($_GET['page'] ?? 1))];

        $dateFrom = trim((string) ($_GET['date_from'] ?? ''));
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateFrom) === 1) {
            $filters['date_from'] = $dateFrom;
        }
        $dateTo = trim((string) ($_GET['date_to'] ?? ''));
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateTo) === 1) {
            $filters['date_to'] = $dateTo;
        }
        $timeFrom = trim((string) ($_GET['time_from'] ?? ''));
        if (preg_match('/^\d{2}:\d{2}(:\d{2})?$/', $timeFrom) === 1) {
            $filters['time_from'] = $timeFrom;
        }
        $timeTo = trim((string) ($_GET['time_to'] ?? ''));
        if (preg_match('/^\d{2}:\d{2}(:\d{2})?$/', $timeTo) === 1) {
            $filters['time_to'] = $timeTo;
        }
        $browser = trim((string) ($_GET['browser'] ?? ''));
        if (in_array($browser, ['chrome', 'edge', 'firefox', 'safari', 'opera', 'other'], true)) {
            $filters['browser'] = $browser;
        }
        $section = trim((string) ($_GET['section'] ?? ''));
        if (in_array($section, ['home', 'blog', 'portfolio', 'auth', 'admin', 'other'], true)) {
            $filters['section'] = $section;
        }

        return $filters;
    }
}
