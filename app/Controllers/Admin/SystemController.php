<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\Controller;
use App\Core\ApiResource;
use App\Models\SiteSettings;
use App\Models\UserActivity;

final class SystemController extends Controller
{
    private $activity;
    private $settings;

    public function __construct(
        UserActivity $activity,
        SiteSettings $settings
    ) {
        $this->activity = $activity;
        $this->settings = $settings;
    }

    public function info($request, array $params = [])
    {
        if (!\admin_is_authenticated()) {
            return $this->json(ApiResource::error('Требуется авторизация'), 401);
        }
        if ($request->method() !== 'GET') {
            return $this->json(ApiResource::error('Метод не поддерживается'), 405);
        }

        $serverSoftware = isset($_SERVER['SERVER_SOFTWARE']) && is_string($_SERVER['SERVER_SOFTWARE'])
            ? substr(preg_replace('/[\x00-\x1F\x7F]/', '', $_SERVER['SERVER_SOFTWARE']), 0, 120)
            : 'Не определён';

        // База данных: хост из конфигурации, версия — запросом к серверу
        $dbConfig = is_file(APP_ROOT . '/config/database.php') ? require APP_ROOT . '/config/database.php' : [];
        $dbHost = '';
        if (preg_match('/host=([^;]+)/', (string) ($dbConfig['dsn'] ?? ''), $m)) {
            $dbHost = trim($m[1]);
        }
        $dbVersion = '';
        try {
            $row = \App\Core\Database::connection()->query('SELECT VERSION() AS v')->fetch(\PDO::FETCH_ASSOC);
            $dbVersion = $row ? trim((string) $row['v']) : '';
        } catch (\Exception $e) {
            // БД недоступна — показываем только хост
        }

        return $this->json(ApiResource::success([
            'webServer' => $serverSoftware !== '' ? $serverSoftware : 'Не определён',
            'phpVersion' => PHP_VERSION . ' (' . php_sapi_name() . ')',
            'os' => PHP_OS_FAMILY,
            'dbHost' => $dbHost !== '' ? $dbHost : 'Не задан',
            'dbVersion' => $dbVersion !== '' ? $dbVersion : 'Не определена',
            'serverTime' => date('d.m.Y H:i:s'),
            'timezone' => date_default_timezone_get() . ' (UTC' . date('P') . ')',
            'uploadLimit' => ini_get('upload_max_filesize'),
            'memoryLimit' => ini_get('memory_limit'),
        ]));
    }

    /**
     * Статистика посещений для дашборда: онлайн (15 минут),
     * за сегодня и последние визиты (блок «Трафик сайта»).
     *
     * Фильтры трафика (GET): date_from, date_to (YYYY-MM-DD),
     * time_from, time_to (HH:MM[:SS]), browser (chrome|edge|firefox|
     * safari|opera|other), section (home|blog|portfolio|auth|other),
     * page (с 1).
     */
    public function statistics($request)
    {
        if (!\admin_is_authenticated()) {
            return $this->json(ApiResource::error('Требуется авторизация'), 401);
        }
        if ($request->method() !== 'GET') {
            return $this->json(ApiResource::error('Метод не поддерживается'), 405);
        }

        $traffic = $this->activity->traffic($this->validatedTrafficFilters());

        // Интервал автообновления статистики дашборда (настройка раздела «Статистика сайта»)
        $updateInterval = (int) $this->settings->get('statisticsUpdateInterval', 900);
        if ($updateInterval < 5) {
            $updateInterval = 900;
        }

        return $this->json(ApiResource::success([
            'online' => $this->activity->onlineCount(),
            'today' => $this->activity->todayCount(),
            'traffic' => $traffic['rows'],
            'pagination' => $traffic['pagination'],
            'updateInterval' => $updateInterval,
        ]));
    }

    /**
     * «Посетители онлайн» для дашборда — лёгкий эндпоинт для опроса
     * в реальном времени (каждые 3 секунды, без трафика/фильтров).
     */
    public function online($request)
    {
        if (!\admin_is_authenticated()) {
            return $this->json(ApiResource::error('Требуется авторизация'), 401);
        }
        if ($request->method() !== 'GET') {
            return $this->json(ApiResource::error('Метод не поддерживается'), 405);
        }

        return $this->json(ApiResource::success(['online' => $this->activity->onlineCount()]));
    }

    /**
     * Срез статистики для графиков раздела «Статистика сайта».
     */
    public function overview($request)
    {
        if (!\admin_is_authenticated()) {
            return $this->json(ApiResource::error('Требуется авторизация'), 401);
        }
        if ($request->method() !== 'GET') {
            return $this->json(ApiResource::error('Метод не поддерживается'), 405);
        }

        $data = $this->activity->overview();
        $updateInterval = (int) $this->settings->get('statisticsUpdateInterval', 900);
        if ($updateInterval < 5) {
            $updateInterval = 900;
        }
        $data['updateInterval'] = $updateInterval;
        return $this->json(ApiResource::success($data));
    }

    /**
     * Drill-down графика «Активность по часам»: посещения по минутам
     * в пределах заданного часа (GET-параметр hour: 00–23).
     */
    public function hourly($request)
    {
        if (!\admin_is_authenticated()) {
            return $this->json(ApiResource::error('Требуется авторизация'), 401);
        }
        if ($request->method() !== 'GET') {
            return $this->json(ApiResource::error('Метод не поддерживается'), 405);
        }

        $hour = trim((string) ($_GET['hour'] ?? ''));
        if (preg_match('/^(?:[01]\d|2[0-3])$/', $hour) !== 1) {
            return $this->json(ApiResource::error('Не указан час (00–23)'), 400);
        }

        $byMinute = $this->activity->hourDetail((int) $hour);
        return $this->json(ApiResource::success(['hour' => $hour, 'byMinute' => $byMinute]));
    }

    /**
     * Таблица «Трафик сайта» (раздел статистики) — те же фильтры и пагинация,
     * что и блок на дашборде.
     */
    public function traffic($request)
    {
        if (!\admin_is_authenticated()) {
            return $this->json(ApiResource::error('Требуется авторизация'), 401);
        }
        if ($request->method() !== 'GET') {
            return $this->json(ApiResource::error('Метод не поддерживается'), 405);
        }

        $traffic = $this->activity->traffic($this->validatedTrafficFilters());
        return $this->json(ApiResource::success([
            'traffic' => $traffic['rows'],
            'pagination' => $traffic['pagination'],
        ]));
    }

    /**
     * Ручная очистка: удалить записи старше установленного срока хранения.
     */
    public function clean($request)
    {
        if (!\admin_is_authenticated()) {
            return $this->json(ApiResource::error('Требуется авторизация'), 401);
        }
        if ($request->method() !== 'POST') {
            return $this->json(ApiResource::error('Метод не поддерживается'), 405);
        }
        \admin_require_csrf();

        $days = (int) $this->settings->get('statisticsRetentionDays', 90);
        if ($days <= 0) {
            return $this->json(ApiResource::error('Срок хранения «Без ограничения» — данные не удаляются. Установите срок хранения, чтобы очистить старые записи.'));
        }

        $deleted = $this->activity->cleanupOld($days);
        \App\Core\Database::connection()->exec(
            'UPDATE ' . db_table('site_settings') . " SET setting_value = (SELECT NOW()), updated_at = NOW() WHERE setting_key = 'statisticsLastCleanAt'"
        );
        return $this->json(ApiResource::success(['deleted' => $deleted]));
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
        if (in_array($section, ['home', 'blog', 'portfolio', 'auth', 'other'], true)) {
            $filters['section'] = $section;
        }

        return $filters;
    }
}
