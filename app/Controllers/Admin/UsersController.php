<?php

namespace App\Controllers\Admin;

use App\Controllers\Controller;
use App\Models\User;

class UsersController extends Controller
{
    /**
     * Получить список всех пользователей.
     */
    public function users($request)
    {
        if ($request->method() !== 'GET') {
            return $this->json(['success' => false, 'error' => 'Метод не поддерживается'], 405);
        }

        $users = (new User())->all();

        return $this->json([
            'success' => true,
            'users' => array_map(function ($user) {
                return [
                    'id' => (int) $user['id'],
                    'email' => $user['email'],
                    'name' => $user['name'],
                    'avatar_path' => $user['avatar_path'] ?? '',
                    'role' => $user['role'],
                    'is_active' => (bool) $user['is_active'],
                    'created_at' => $user['created_at'],
                    'updated_at' => $user['updated_at'],
                ];
            }, $users),
        ]);
    }

    /**
     * Получить пользователя по ID.
     */
    public function getUser($request, $parameters)
    {
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
                'role' => $user['role'],
                'is_active' => (bool) $user['is_active'],
                'created_at' => $user['created_at'],
                'updated_at' => $user['updated_at'],
            ],
        ]);
    }

    /**
     * Обновить пользователя.
     */
    public function updateUser($request, $parameters)
    {
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
        $role = isset($input['role']) && is_string($input['role']) ? $input['role'] : '';
        $isActive = isset($input['is_active']) ? filter_var($input['is_active'], FILTER_VALIDATE_BOOLEAN) : false;

        if (strlen($name) < 2 || strlen($name) > 160) {
            return $this->json(['success' => false, 'error' => 'Имя должно содержать от 2 до 160 символов'], 400);
        }
        if (!in_array($role, ['user', 'editor', 'manager', 'admin'], true)) {
            return $this->json(['success' => false, 'error' => 'Неверная роль'], 400);
        }

        $result = (new User())->updateProfile($id, $name, '');
        if (!$result['success']) {
            return $this->json(['success' => false, 'error' => 'Не удалось обновить'], 500);
        }

        $db = \App\Core\Database::connection();
        $stmt = $db->prepare("UPDATE users SET role = :role, is_active = :is_active, updated_at = NOW() WHERE id = :id");
        $stmt->execute([
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
        if ($request->method() !== 'DELETE') {
            return $this->json(['success' => false, 'error' => 'Метод не поддерживается'], 405);
        }

        \admin_require_csrf();

        $id = isset($parameters['id']) ? (int) $parameters['id'] : 0;
        if ($id <= 0) {
            return $this->json(['success' => false, 'error' => 'Неверный ID'], 400);
        }

        // Нельзя удалить себя
        $adminUserId = $_SESSION['admin_user_id'] ?? 0;
        if ($id == $adminUserId) {
            return $this->json(['success' => false, 'error' => 'Нельзя удалить свою учётную запись'], 400);
        }

        $db = \App\Core\Database::connection();
        $stmt = $db->prepare("DELETE FROM users WHERE id = :id");
        $stmt->execute([':id' => $id]);

        return $this->json(['success' => true]);
    }

    /**
     * Создать пользователя.
     */
    public function createUser($request)
    {
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
        if (!in_array($role, ['user', 'editor', 'manager', 'admin'], true)) {
            return $this->json(['success' => false, 'error' => 'Неверная роль'], 400);
        }

        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        $result = (new User())->create($email, $passwordHash, $name);
        if (!$result['success']) {
            return $this->json(['success' => false, 'error' => $result['error']], 400);
        }

        return $this->json(['success' => true, 'user_id' => $result['user_id']]);
    }
}
