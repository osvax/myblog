<?php

namespace App\Controllers\Admin;

use App\Controllers\Controller;
use App\Models\Role;
use App\Models\User;

class UsersController extends Controller
{
    /**
     * Получить список всех пользователей.
     */
    public function users($request)
    {
        if (!\admin_is_authenticated()) {
            return $this->json(['success' => false, 'error' => 'Требуется авторизация'], 401);
        }
        if (!\admin_can_access('users')) {
            return $this->json(['success' => false, 'error' => 'Недостаточно прав'], 403);
        }
        if ($request->method() !== 'GET') {
            return $this->json(['success' => false, 'error' => 'Метод не поддерживается'], 405);
        }

        $users = (new User())->all();
        $roleNames = $this->roleNamesMap();

        return $this->json([
            'success' => true,
            'users' => array_map(function ($user) use ($roleNames) {
                return [
                    'id' => (int) $user['id'],
                    'email' => $user['email'],
                    'name' => $user['name'],
                    'avatar_path' => $user['avatar_path'] ?? '',
                    'role' => $user['role'],
                    'role_name' => $roleNames[$user['role']] ?? $user['role'],
                    'is_active' => (bool) $user['is_active'],
                    'created_at' => $user['created_at'],
                    'last_visit' => $user['last_visit'] ?? null,
                ];
            }, $users),
        ]);
    }

    /**
     * Получить пользователя по ID.
     */
    public function getUser($request, $parameters)
    {
        if (!\admin_is_authenticated()) {
            return $this->json(['success' => false, 'error' => 'Требуется авторизация'], 401);
        }
        if (!\admin_can_access('user-edit')) {
            return $this->json(['success' => false, 'error' => 'Недостаточно прав'], 403);
        }
        if ($request->method() !== 'GET') {
            return $this->json(['success' => false, 'error' => 'Метод не поддерживается'], 405);
        }

        $id = isset($parameters['id']) ? (int) $parameters['id'] : 0;
        if ($id <= 0) {
            return $this->json(['success' => false, 'error' => 'Неверный ID'], 400);
        }

        $user = (new User())->findById($id);
        if (!$user) {
            return $this->json(['success' => false, 'error' => 'Пользователь не найден'], 404);
        }

        return $this->json([
            'success' => true,
            'user' => [
                'id' => (int) $user['id'],
                'email' => $user['email'],
                'name' => $user['name'],
                'avatar_path' => $user['avatar_path'] ?? '',
                'role' => $user['role'],
                'role_name' => $this->roleNamesMap()[$user['role']] ?? $user['role'],
                'is_active' => (bool) $user['is_active'],
                'created_at' => $user['created_at'],
                'updated_at' => $user['updated_at'],
                'last_visit' => $user['last_visit'] ?? null,
            ],
        ]);
    }

    /**
     * Обновить пользователя: имя, email, роль, статус.
     */
    public function updateUser($request, $parameters)
    {
        if (!\admin_is_authenticated()) {
            return $this->json(['success' => false, 'error' => 'Требуется авторизация'], 401);
        }
        if (!\admin_can_access('user-edit')) {
            return $this->json(['success' => false, 'error' => 'Недостаточно прав'], 403);
        }
        if ($request->method() !== 'PUT') {
            return $this->json(['success' => false, 'error' => 'Метод не поддерживается'], 405);
        }

        \admin_require_csrf();

        $id = isset($parameters['id']) ? (int) $parameters['id'] : 0;
        if ($id <= 0) {
            return $this->json(['success' => false, 'error' => 'Неверный ID'], 400);
        }

        $input = $request->json();
        $name = isset($input['name']) && is_string($input['name']) ? trim($input['name']) : '';
        $email = isset($input['email']) && is_string($input['email']) ? trim($input['email']) : '';
        $role = isset($input['role']) && is_string($input['role']) ? $input['role'] : '';
        $isActive = isset($input['is_active']) ? filter_var($input['is_active'], FILTER_VALIDATE_BOOLEAN) : false;

        if (strlen($name) < 2 || strlen($name) > 160) {
            return $this->json(['success' => false, 'error' => 'Имя должно содержать от 2 до 160 символов'], 400);
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->json(['success' => false, 'error' => 'Неверный email'], 400);
        }
        if (!(new Role())->exists($role)) {
            return $this->json(['success' => false, 'error' => 'Неверная роль'], 400);
        }

        // Email должен быть уникальным (кроме текущего пользователя)
        $db = \App\Core\Database::connection();
        $stmt = $db->prepare("SELECT id FROM " . db_table('users') . " WHERE email = :email AND id != :id LIMIT 1");
        $stmt->execute([':email' => $email, ':id' => $id]);
        if ($stmt->fetch()) {
            return $this->json(['success' => false, 'error' => 'Пользователь с таким email уже существует'], 400);
        }

        // Защита от само-блокировки: свою роль и статус изменить нельзя
        if ($id === \admin_get_user_id()) {
            $current = (new User())->findById($id);
            if ($current && ($role !== $current['role'] || !$isActive)) {
                return $this->json(['success' => false, 'error' => 'Нельзя изменить свою роль или заблокировать свой аккаунт'], 400);
            }
        }

        $stmt = $db->prepare(
            "UPDATE " . db_table('users') . " SET name = :name, email = :email, role = :role, is_active = :is_active, updated_at = NOW()
             WHERE id = :id"
        );
        $stmt->execute([
            ':name' => $name,
            ':email' => $email,
            ':role' => $role,
            ':is_active' => $isActive ? 1 : 0,
            ':id' => $id,
        ]);

        return $this->json(['success' => true]);
    }

    /**
     * Удалить пользователя.
     */
    public function deleteUser($request, $parameters)
    {
        if (!\admin_is_authenticated()) {
            return $this->json(['success' => false, 'error' => 'Требуется авторизация'], 401);
        }
        if (!\admin_can_access('users')) {
            return $this->json(['success' => false, 'error' => 'Недостаточно прав'], 403);
        }
        if ($request->method() !== 'DELETE') {
            return $this->json(['success' => false, 'error' => 'Метод не поддерживается'], 405);
        }

        \admin_require_csrf();

        $id = isset($parameters['id']) ? (int) $parameters['id'] : 0;
        if ($id <= 0) {
            return $this->json(['success' => false, 'error' => 'Неверный ID'], 400);
        }

        // Нельзя удалить себя
        if ($id === \admin_get_user_id()) {
            return $this->json(['success' => false, 'error' => 'Нельзя удалить свою учётную запись'], 400);
        }

        $db = \App\Core\Database::connection();
        $stmt = $db->prepare("DELETE FROM " . db_table('users') . " WHERE id = :id");
        $stmt->execute([':id' => $id]);

        return $this->json(['success' => true]);
    }

    /**
     * Создать пользователя (имя, email, пароль, роль).
     */
    public function createUser($request)
    {
        if (!\admin_is_authenticated()) {
            return $this->json(['success' => false, 'error' => 'Требуется авторизация'], 401);
        }
        if (!\admin_can_access('users')) {
            return $this->json(['success' => false, 'error' => 'Недостаточно прав'], 403);
        }
        if ($request->method() !== 'POST') {
            return $this->json(['success' => false, 'error' => 'Метод не поддерживается'], 405);
        }

        \admin_require_csrf();

        $input = $request->json();
        $name = isset($input['name']) && is_string($input['name']) ? trim($input['name']) : '';
        $email = isset($input['email']) && is_string($input['email']) ? trim($input['email']) : '';
        $password = isset($input['password']) && is_string($input['password']) ? $input['password'] : '';
        $role = isset($input['role']) && is_string($input['role']) ? $input['role'] : 'user';

        if (strlen($name) < 2 || strlen($name) > 160) {
            return $this->json(['success' => false, 'error' => 'Имя должно содержать от 2 до 160 символов'], 400);
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->json(['success' => false, 'error' => 'Неверный email'], 400);
        }
        if (strlen($password) < 8) {
            return $this->json(['success' => false, 'error' => 'Пароль должен содержать минимум 8 символов'], 400);
        }
        if (!(new Role())->exists($role)) {
            return $this->json(['success' => false, 'error' => 'Неверная роль'], 400);
        }

        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        $result = (new User())->create($email, $passwordHash, $name);
        if (!$result['success']) {
            return $this->json(['success' => false, 'error' => $result['error']], 400);
        }

        // Роль задаётся при создании (дефолт БД — 'user')
        $db = \App\Core\Database::connection();
        $stmt = $db->prepare("UPDATE " . db_table('users') . " SET role = :role, is_active = 1, updated_at = NOW() WHERE id = :id");
        $stmt->execute([':role' => $role, ':id' => $result['user_id']]);

        return $this->json(['success' => true, 'user_id' => $result['user_id']]);
    }

    /**
     * Загрузить аватар пользователя (multipart, поле avatar).
     */
    public function uploadAvatar($request, $parameters)
    {
        if (!\admin_is_authenticated()) {
            return $this->json(['success' => false, 'error' => 'Требуется авторизация'], 401);
        }
        if (!\admin_can_access('user-edit')) {
            return $this->json(['success' => false, 'error' => 'Недостаточно прав'], 403);
        }
        if ($request->method() !== 'POST') {
            return $this->json(['success' => false, 'error' => 'Метод не поддерживается'], 405);
        }

        \admin_require_csrf();

        $id = isset($parameters['id']) ? (int) $parameters['id'] : 0;
        if ($id <= 0) {
            return $this->json(['success' => false, 'error' => 'Неверный ID'], 400);
        }

        if (!isset($_FILES['avatar']) || $_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
            return $this->json(['success' => false, 'error' => 'Файл не загружен'], 400);
        }

        $file = $_FILES['avatar'];
        $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
        $maxSize = 2 * 1024 * 1024; // 2MB

        if (!in_array($file['type'], $allowedTypes, true)) {
            return $this->json(['success' => false, 'error' => 'Разрешены только JPEG, PNG, WebP'], 400);
        }
        if ($file['size'] > $maxSize) {
            return $this->json(['success' => false, 'error' => 'Файл слишком большой (макс. 2 МБ)'], 400);
        }

        // Создаём директорию
        $uploadDir = APP_ROOT . '/public/uploads/profile/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $extension = match ($file['type']) {
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            default => 'jpg',
        };
        $filename = md5($id . time() . uniqid()) . '.' . $extension;
        $filepath = $uploadDir . $filename;

        if (!move_uploaded_file($file['tmp_name'], $filepath)) {
            return $this->json(['success' => false, 'error' => 'Не удалось сохранить файл'], 500);
        }

        // Удаляем старый аватар (только из каталога аватаров)
        $db = \App\Core\Database::connection();
        $stmt = $db->prepare("SELECT avatar_path FROM " . db_table('users') . " WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $user = $stmt->fetch();
        if (!empty($user['avatar_path'])
            && strpos($user['avatar_path'], '/uploads/profile/') === 0
            && file_exists(APP_ROOT . '/public' . $user['avatar_path'])) {
            unlink(APP_ROOT . '/public' . $user['avatar_path']);
        }

        $avatarPath = '/uploads/profile/' . $filename;
        $stmt = $db->prepare("UPDATE " . db_table('users') . " SET avatar_path = :avatar_path, updated_at = NOW() WHERE id = :id");
        $stmt->execute([':avatar_path' => $avatarPath, ':id' => $id]);

        return $this->json(['success' => true, 'avatar_path' => $avatarPath]);
    }

    /**
     * Сбросить пароль пользователя (администратор задаёт новый).
     */
    public function resetPassword($request, $parameters)
    {
        if (!\admin_is_authenticated()) {
            return $this->json(['success' => false, 'error' => 'Требуется авторизация'], 401);
        }
        if (!\admin_can_access('user-edit')) {
            return $this->json(['success' => false, 'error' => 'Недостаточно прав'], 403);
        }
        if ($request->method() !== 'POST') {
            return $this->json(['success' => false, 'error' => 'Метод не поддерживается'], 405);
        }

        \admin_require_csrf();

        $id = isset($parameters['id']) ? (int) $parameters['id'] : 0;
        if ($id <= 0) {
            return $this->json(['success' => false, 'error' => 'Неверный ID'], 400);
        }

        // Свой пароль меняется в «Профиле»
        if ($id === \admin_get_user_id()) {
            return $this->json(['success' => false, 'error' => 'Свой пароль можно изменить только в «Профиле»'], 400);
        }

        $input = $request->json();
        $password = isset($input['password']) && is_string($input['password']) ? $input['password'] : '';
        if (strlen($password) < 8) {
            return $this->json(['success' => false, 'error' => 'Пароль должен содержать минимум 8 символов'], 400);
        }

        $stmt = \App\Core\Database::connection()->prepare(
            "UPDATE " . db_table('users') . " SET password_hash = :password_hash, updated_at = NOW() WHERE id = :id"
        );
        $stmt->execute([
            ':password_hash' => password_hash($password, PASSWORD_DEFAULT),
            ':id' => $id,
        ]);

        return $this->json(['success' => true]);
    }

    /**
     * Соответствие role_key → название из таблицы ролей.
     */
    private function roleNamesMap(): array
    {
        $map = [];
        foreach ((new Role())->all() as $role) {
            $map[$role['role_key']] = $role['name'];
        }
        return $map;
    }
}
