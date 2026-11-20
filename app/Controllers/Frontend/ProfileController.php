<?php

namespace App\Controllers\Frontend;

use App\Controllers\Controller;
use App\Core\Response;
use App\Models\User;

class ProfileController extends Controller
{
    public function show($request)
    {
        $user = current_user();
        if (!$user) {
            return Response::redirect('/login.html');
        }

        $full = (new User())->findById((int) $user['id']);

        return $this->page('frontend', 'profile', [
            'title' => 'Профиль',
            'activePage' => 'profile',
            'user' => $user,
            'totpEnabled' => $full !== null && !empty($full['totp_enabled']),
            'isAdmin' => $user['role'] === 'admin',
            'includeAuthScript' => true,
        ]);
    }

    public function updateProfile($request)
    {
        $user = current_user();
        if (!$user) {
            return $this->json(['success' => false, 'error' => 'Не авторизован'], 401);
        }

        if ($request->method() !== 'POST') {
            return $this->json(['success' => false, 'error' => 'Метод не поддерживается'], 405);
        }

        $input = $request->json();
        $name = isset($input['name']) && is_string($input['name']) ? trim($input['name']) : '';

        if (strlen($name) < 2 || strlen($name) > 160) {
            return $this->json(['success' => false, 'error' => 'Имя должно содержать от 2 до 160 символов'], 400);
        }

        $result = (new User())->updateProfile((int) $user['id'], $name, $user['avatar_path'] ?? '');
        if (!$result['success']) {
            return $this->json(['success' => false, 'error' => 'Не удалось обновить профиль'], 500);
        }

        $_SESSION['user_name'] = $name;

        return $this->json(['success' => true]);
    }

    public function changePassword($request)
    {
        $user = current_user();
        if (!$user) {
            return $this->json(['success' => false, 'error' => 'Не авторизован'], 401);
        }

        if ($request->method() !== 'POST') {
            return $this->json(['success' => false, 'error' => 'Метод не поддерживается'], 405);
        }

        $input = $request->json();
        $oldPassword = isset($input['old_password']) && is_string($input['old_password']) ? $input['old_password'] : '';
        $newPassword = isset($input['new_password']) && is_string($input['new_password']) ? $input['new_password'] : '';
        $newPasswordConfirm = isset($input['new_password_confirm']) && is_string($input['new_password_confirm']) ? $input['new_password_confirm'] : '';

        $db = \App\Core\Database::connection();
        $stmt = $db->prepare("SELECT password_hash FROM " . db_table('users') . " WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $user['id']]);
        $dbUser = $stmt->fetch();

        if (!$dbUser || !password_verify($oldPassword, $dbUser['password_hash'])) {
            return $this->json(['success' => false, 'error' => 'Неверный текущий пароль'], 400);
        }

        if (strlen($newPassword) < 8) {
            return $this->json(['success' => false, 'error' => 'Новый пароль должен содержать минимум 8 символов'], 400);
        }
        if ($newPassword !== $newPasswordConfirm) {
            return $this->json(['success' => false, 'error' => 'Новые пароли не совпадают'], 400);
        }

        $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $db->prepare("UPDATE " . db_table('users') . " SET password_hash = :hash, updated_at = NOW() WHERE id = :id");
        $stmt->execute([':hash' => $passwordHash, ':id' => $user['id']]);

        return $this->json(['success' => true]);
    }

    public function deleteAccount($request)
    {
        $user = current_user();
        if (!$user) {
            return $this->json(['success' => false, 'error' => 'Не авторизован'], 401);
        }

        if ($request->method() !== 'POST') {
            return $this->json(['success' => false, 'error' => 'Метод не поддерживается'], 405);
        }

        $input = $request->json();
        $password = isset($input['password']) && is_string($input['password']) ? $input['password'] : '';

        $db = \App\Core\Database::connection();
        $stmt = $db->prepare("SELECT password_hash FROM " . db_table('users') . " WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $user['id']]);
        $dbUser = $stmt->fetch();

        if (!$dbUser || !password_verify($password, $dbUser['password_hash'])) {
            return $this->json(['success' => false, 'error' => 'Неверный пароль'], 400);
        }

        $stmt = $db->prepare("DELETE FROM " . db_table('users') . " WHERE id = :id");
        $stmt->execute([':id' => $user['id']]);

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', [
                'expires' => time() - 42000,
                'path' => $params['path'],
                'domain' => $params['domain'],
                'secure' => $params['secure'],
                'httponly' => $params['httponly'],
                'samesite' => 'Lax',
            ]);
        }

        session_destroy();

        return $this->json(['success' => true]);
    }
}
