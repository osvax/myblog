<?php

function admin_start_session()
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    $https = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== '' && strtolower($_SERVER['HTTPS']) !== 'off';
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => $https,
        'httponly' => true,
        'samesite' => 'Strict',
    ]);
    session_start();
}

function admin_is_authenticated()
{
    admin_start_session();
    if (empty($_SESSION['admin_authenticated']) || empty($_SESSION['admin_authenticated_at'])) {
        return false;
    }

    // Срок сессии (часы) — из настроек сайта (Настройки → Безопасность)
    $sessionHours = 8;
    try {
        $sessionHours = (int) (new \App\Models\SiteSettings())->get('adminSessionHours', 8);
    } catch (\Throwable $error) {
        // БД недоступна — оставляем 8 часов
    }
    if ($sessionHours < 1) {
        $sessionHours = 8;
    }

    if (time() - (int) $_SESSION['admin_authenticated_at'] > $sessionHours * 3600) {
        $_SESSION = [];
        session_destroy();
        return false;
    }

    return true;
}

function admin_get_user_role()
{
    admin_start_session();
    return $_SESSION['admin_role'] ?? 'admin';
}

function admin_get_user_id()
{
    admin_start_session();
    return (int) ($_SESSION['admin_user_id'] ?? 0);
}

function admin_require_authentication()
{
    if (admin_is_authenticated()) {
        return;
    }

    http_response_code(401);
    exit;
}

function admin_require_csrf()
{
    admin_start_session();
    $provided = isset($_SERVER['HTTP_X_CSRF_TOKEN']) ? $_SERVER['HTTP_X_CSRF_TOKEN'] : '';
    // Обычные HTML-формы (например, «Выйти» в тулбаре) не могут поставить
    // заголовок — для них токен передаётся скрытым полем csrf_token
    if ($provided === '' && isset($_POST['csrf_token'])) {
        $provided = (string) $_POST['csrf_token'];
    }
    $expected = isset($_SESSION['admin_csrf_token']) ? $_SESSION['admin_csrf_token'] : '';
    if ($provided === '' || $expected === '' || !hash_equals($expected, $provided)) {
        admin_json_response(403, ['success' => false, 'error' => 'Проверка запроса не пройдена. Обновите страницу и попробуйте снова']);
    }
}

function admin_can_access($pageSlug)
{
    $role = admin_get_user_role();
    if ($role === 'admin') {
        return true;
    }

    // Права роли читаются из БД (roles + role_permissions); кэш на время запроса
    static $allowedCache = null;
    if ($allowedCache === null) {
        try {
            $allowedCache = (new \App\Models\Role())->permissionsForKey($role);
        } catch (\Throwable $error) {
            $allowedCache = [];
        }
    }
    return in_array($pageSlug, $allowedCache, true);
}

/**
 * Раздел админки, к которому относится API-эндпоинт /api/admin/*.
 * '' — доступ любой авторизованной админ-сессии (профиль, медиа, дашборд);
 * null — путь не требует проверки раздела (не бывает, т.к. вызывается только для /api/admin/).
 */
function admin_api_required_section(string $path): string
{
    $map = [
        '#^/api/admin/users$#' => 'users',
        '#^/api/admin/users/\d+/(avatar|password)$#' => 'user-edit',
        '#^/api/admin/users/\d+$#' => 'user-edit',
        '#^/api/admin/roles#' => 'roles',
        '#^/api/admin/settings#' => 'settings',
        '#^/api/admin/custom-fields#' => 'custom-fields',
        '#^/api/admin/templates#' => 'templates',
        '#^/api/admin/menus#' => 'menus',
        '#^/api/admin/calculator#' => 'calculator',
        '#^/api/admin/files#' => 'files',
        '#^/api/admin/articles#' => 'blog',
        '#^/api/admin/projects#' => 'portfolio',
        '#^/api/admin/categories#' => 'categories',
        '#^/api/admin/orders#' => 'orders',
        '#^/api/admin/messages#' => 'messages',
        '#^/api/admin/statistics#' => 'statistics',
        '#^/api/admin/landing-page#' => 'landing-pages',
        '#^/api/admin/pages#' => 'landing-pages',
    ];
    foreach ($map as $pattern => $section) {
        if (preg_match($pattern, $path) === 1) {
            return $section;
        }
    }
    return '';
}

function admin_json_response($status, $payload)
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}
