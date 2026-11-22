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
    $https = request_is_https();
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => $https,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();

    // Generate CSRF token if not exists
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
}

/**
 * Получить CSRF токен текущей сессии.
 *
 * @return string
 */
function csrf_token(): string
{
    user_start_session();
    return $_SESSION['csrf_token'];
}

/**
 * Проверить CSRF токен.
 *
 * @param string $token
 * @return bool
 */
function csrf_verify(string $token): bool
{
    user_start_session();
    $expected = $_SESSION['csrf_token'] ?? '';
    return hash_equals($expected, $token);
}

/**
 * Сгенерировать скрытое поле CSRF для форм.
 *
 * @return string
 */
function csrf_field(): string
{
    return '<input type="hidden" name="_token" value="' . htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') . '">';
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
