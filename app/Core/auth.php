<?php

/**
 * Получить данные текущего авторизованного пользователя.
 *
 * @return array|null
 */
function current_user(): ?array
{
    if (session_status() === PHP_SESSION_NONE) {
        return null;
    }

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
