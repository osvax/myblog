<?php

require dirname(__DIR__) . '/app/bootstrap.php';

// ---------- Блокировка по IP (страница 403) ----------
// Посетители из списка blockedIps не получают ничего — ни сайт, ни админку.
try {
    $gateSettings = new \App\Models\SiteSettings();
    $blockedIps = (string) $gateSettings->get('blockedIps', '');
} catch (\Throwable $gateError) {
    $blockedIps = '';
    $gateSettings = null;
}
if (trim($blockedIps) !== '') {
    $gateIp = \App\Core\Logger::clientIp();
    if (\ip_in_list($gateIp, $blockedIps)) {
        \App\Core\Logger::write('security', 'Доступ заблокирован по IP: ' . $gateIp . ' (страница 403)');
        \App\Core\ErrorPages::page(403, 'Доступ запрещён', 'Ваш IP-адрес добавлен в список блокировки этого сайта. Если вы считаете это ошибкой — свяжитесь с владельцем сайта.', '/')->send();
        exit;
    }
}

// ---------- Режим обслуживания ----------
// Сайт закрыт (siteStatus=maintenance): посетители, чей IP нет в списке
// maintenanceAllowedIps, перенаправляются на заглушку /maintenance.html.
// Админка, API авторизации и статические файлы работают всегда.
//
// Режим «от — до» (maintenanceStart/maintenanceEnd):
//  - сайт закрывается автоматически, когда время входит в интервал;
//  - открывается автоматически, когда время прошло.
try {
    $maintenanceSettings = $gateSettings ?? new \App\Models\SiteSettings();
    $siteStatus = (string) $maintenanceSettings->get('siteStatus', 'online');
    // Пустое значение (могло остаться из формы админки) трактуем как online
    $siteStatus = ($siteStatus === 'maintenance') ? 'maintenance' : 'online';
    $maintStart = trim((string) $maintenanceSettings->get('maintenanceStart', ''));
    $maintEnd = trim((string) $maintenanceSettings->get('maintenanceEnd', ''));
} catch (\Throwable $maintenanceError) {
    $siteStatus = 'online';
    $maintStart = '';
    $maintEnd = '';
    $maintenanceSettings = null;
}

$nowTs = time();
$maintenanceStartTs = $maintStart !== '' ? (int) strtotime(str_replace('T', ' ', $maintStart)) : 0;
$maintenanceEndTs = $maintEnd !== '' ? (int) strtotime(str_replace('T', ' ', $maintEnd)) : 0;

// Авто-закрытие по интервалу «от — до»
if ($siteStatus === 'online' && $maintenanceStartTs > 0 && $maintenanceEndTs > $maintenanceStartTs &&
    $nowTs >= $maintenanceStartTs && $nowTs < $maintenanceEndTs) {
    $siteStatus = 'maintenance';
}

// Авто-открытие: время обслуживания вышло
if ($siteStatus === 'maintenance' && $maintenanceEndTs > 0 && $nowTs >= $maintenanceEndTs) {
    $siteStatus = 'online';
    if ($maintenanceSettings !== null) {
        try {
            (new \App\Models\SiteSettings())->saveAllWithType(['siteStatus' => 'online']);
            \App\Core\Logger::write('security', 'Сайт автоматически открыт: время обслуживания (до ' . $maintEnd . ') вышло');
        } catch (\Throwable $autoOpenError) {
            // Не критично: сайт будет открыт на этот запрос
        }
    }
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
        $visitorIp = \App\Core\Logger::clientIp();
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

// ---------- Единая проверка прав для /api/admin/* ----------
// Аутентификация сессии обязательна; для эндпоинтов конкретного раздела
// дополнительно проверяется право роли на этот раздел (roles + role_permissions).
$request = App\Core\Request::fromGlobals();
if (strpos($request->path(), '/api/admin/') === 0) {
    if (!\admin_is_authenticated()) {
        App\Core\Response::json(['success' => false, 'error' => 'Требуется авторизация'], 401)->send();
        exit;
    }
    $apiSection = \admin_api_required_section($request->path());
    if ($apiSection !== '' && !\admin_can_access($apiSection)) {
        App\Core\Response::json(['success' => false, 'error' => 'Нет прав на этот раздел'], 403)->send();
        exit;
    }
}

$response = $router->dispatch($request);

// Логируем посещение для авторизованного пользователя
if (function_exists('current_user') && current_user()) {
    $user = current_user();
    $pageUrl = $_SERVER['REQUEST_URI'] ?? '/';
    $pageTitle = 'Пользовательская страница';
    \App\Core\VisitLogger::log($pageUrl, $pageTitle);
}

$response->send();
