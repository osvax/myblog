<?php

require dirname(__DIR__) . '/app/bootstrap.php';

// ---------- Режим обслуживания ----------
// Сайт закрыт (siteStatus=maintenance): посетители, чей IP нет в списке
// maintenanceAllowedIps, перенаправляются на заглушку /maintenance.html.
// Админка, API авторизации и статические файлы работают всегда.
try {
    $maintenanceSettings = new \App\Models\SiteSettings();
    $siteStatus = (string) $maintenanceSettings->get('siteStatus', 'online');
} catch (\Throwable $maintenanceError) {
    $siteStatus = 'online';
}
if ($siteStatus === 'maintenance') {
    $requestPath = '/' . trim((string) (parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/'), '/');
    $maintenanceExempt = $requestPath === '/maintenance.html'
        || strpos($requestPath, '/admin') === 0
        || strpos($requestPath, '/api/admin') === 0
        || strpos($requestPath, '/api/auth') === 0
        || strpos($requestPath, '/css/') === 0
        || strpos($requestPath, '/js/') === 0
        || strpos($requestPath, '/img/') === 0
        || strpos($requestPath, '/uploads/') === 0;
    if (!$maintenanceExempt) {
        $allowedIps = array_filter(array_map('trim', preg_split('/[\s,;]+/u', (string) $maintenanceSettings->get('maintenanceAllowedIps', '')) ?: []));
        $visitorIp = isset($_SERVER['HTTP_X_FORWARDED_FOR']) && $_SERVER['HTTP_X_FORWARDED_FOR'] !== ''
            ? trim(explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0])
            : (string) ($_SERVER['REMOTE_ADDR'] ?? '');
        if (!in_array($visitorIp, $allowedIps, true)) {
            header('Location: /maintenance.html', true, 302);
            exit;
        }
    }
}

$router = new App\Core\Router();
foreach ([APP_ROOT . '/routes/web.php', APP_ROOT . '/routes/api.php'] as $routeFile) {
    $registerRoutes = require $routeFile;
    $registerRoutes($router);
}

$response = $router->dispatch(App\Core\Request::fromGlobals());

// Логируем посещение для авторизованного пользователя
if (function_exists('current_user') && current_user()) {
    $user = current_user();
    $pageUrl = $_SERVER['REQUEST_URI'] ?? '/';
    $pageTitle = 'Пользовательская страница';
    \App\Core\VisitLogger::log('user', $pageUrl, $pageTitle);
}

$response->send();
