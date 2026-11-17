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

    if (time() - (int) $_SESSION['admin_authenticated_at'] > 28800) {
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

    if ($role === 'editor') {
        $allowedPages = [
            'index',        // Дашборд (только просмотр)
            'blog',         // Блог
            'portfolio',    // Портфолио
            'files',        // Файлы
            'profile',      // Профиль
            'user-edit',    // Карточка пользователя (просмотр)
        ];
        return in_array($pageSlug, $allowedPages, true);
    }

    if ($role === 'manager') {
        $allowedPages = [
            'index',            // Дашборд
            'consultant',       // Консультант
            'orders',           // Заказы
            'current-orders',   // Текущие
            'new-orders',       // Новые
            'calculator',       // Калькулятор
            'profile',          // Профиль
            'user-edit',        // Карточка пользователя (просмотр)
        ];
        return in_array($pageSlug, $allowedPages, true);
    }

    return false;
}

function admin_json_response($status, $payload)
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}
