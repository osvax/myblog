<?php

/**
 * Стартовать сессию пользователя с безопасными параметрами cookie.
 * Используется на публичных страницах и в API авторизации пользователей.
 */
function user_start_session(): void
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
        'samesite' => 'Lax',
    ]);
    session_start();
}

/**
 * Получить данные текущего авторизованного пользователя.
 *
 * Сессия стартуется лениво: публичные страницы вызывают функцию напрямую,
 * и ссылка «Профиль» корректно заменяет «Войти» после авторизации.
 *
 * @return array|null
 */
function current_user(): ?array
{
    user_start_session();

    if (!empty($_SESSION['user_authenticated'])) {
        return [
            'id' => (int) ($_SESSION['user_id'] ?? 0),
            'email' => $_SESSION['user_email'] ?? '',
            'name' => $_SESSION['user_name'] ?? '',
            'role' => $_SESSION['user_role'] ?? 'user',
        ];
    }

    return null;
}
