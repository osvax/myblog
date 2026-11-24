<?php

namespace App\Controllers\Admin;

use App\Controllers\Controller;
use App\Models\UserRepository;
use App\DTO\UserDTO;

class UsersController extends Controller
{
    private $users;

    public function __construct()
    {
        $this->users = new UserRepository();
    }

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

        // Новый API: используем UserDTO
        $users = $this->users->allAsDto();

        return $this->json([
            'success' => true,
            'users' => array_map(function (UserDTO $user) {
                return $user->toApiArray();
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

        $user = $this->users->findDtoById($id);
        if (!$user) {
            return $this->json(['success' => false, 'error' => 'Пользователь не найден'], 404);
        }

        return $this->json([
            'success' => true,
            'user' => $user->toApiArray(),
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

        // Получаем существующего пользователя
        $existingUser = $this->users->findDtoById($id);
        if (!$existingUser) {
            return $this->json(['success' => false, 'error' => 'Пользователь не найден'], 404);
        }

        // Защита от само-блокировки: свою роль и статус изменить нельзя
        if ($id === \admin_get_user_id()) {
            $currentRole = $existingUser->role;
            $currentActive = $existingUser->isActive;
            $newRole = $input['role'] ?? $currentRole;
            $newActive = isset($input['is_active']) ? filter_var($input['is_active'], FILTER_VALIDATE_BOOLEAN) : $currentActive;

            if ($newRole !== $currentRole || $newActive !== $currentActive) {
                return $this->json(['success' => false, 'error' => 'Нельзя изменить свою роль или заблокировать свой аккаунт'], 400);
            }
        }

        // Создаём DTO из запроса (для обновления пароль не требуется)
        $dto = UserDTO::fromRequest($input, $id);

        // Для обновления используем validateUpdate (без проверки пароля)
        if (!$dto->validateUpdate()) {
            return $this->json(['success' => false, 'error' => 'Ошибки валидации', 'errors' => $dto->getErrors()], 400);
        }

        // Проверяем уникальность email (кроме текущего пользователя)
        if ($this->users->emailExists($dto->email, $id)) {
            return $this->json(['success' => false, 'error' => 'Пользователь с таким email уже существует'], 400);
        }

        // Проверяем, что роль существует
        if (!$this->users->roleExists($dto->role)) {
            return $this->json(['success' => false, 'error' => 'Неверная роль'], 400);
        }

        try {
            $savedId = $this->users->updateUserFromDTO($dto);
            if ($savedId === false) {
                return $this->json(['success' => false, 'error' => 'Пользователь не найден'], 404);
            }
            return $this->json(['success' => true]);
        } catch (\InvalidArgumentException $e) {
            return $this->json(['success' => false, 'error' => $e->getMessage()], 400);
        } catch (\Throwable $e) {
            error_log('User update error: ' . $e->getMessage());
            return $this->json(['success' => false, 'error' => 'Ошибка обновления пользователя'], 500);
        }
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

        $deleted = $this->users->delete($id);

        return $deleted
            ? $this->json(['success' => true])
            : $this->json(['success' => false, 'error' => 'Пользователь не найден'], 404);
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

        // Создаём DTO из запроса
        $dto = UserDTO::fromRequest($input);

        // Валидация для создания (требует пароль)
        if (!$dto->validate()) {
            return $this->json(['success' => false, 'error' => 'Ошибки валидации', 'errors' => $dto->getErrors()], 400);
        }

        // Проверяем, что роль существует
        if (!$this->users->roleExists($dto->role)) {
            return $this->json(['success' => false, 'error' => 'Неверная роль'], 400);
        }

        try {
            $id = $this->users->saveUserFromDTO($dto);
            if ($id === false) {
                return $this->json(['success' => false, 'error' => 'Не удалось создать пользователя'], 500);
            }
            return $this->json(['success' => true, 'user_id' => $id]);
        } catch (\InvalidArgumentException $e) {
            return $this->json(['success' => false, 'error' => $e->getMessage()], 400);
        } catch (\Throwable $e) {
            error_log('User create error: ' . $e->getMessage());
            return $this->json(['success' => false, 'error' => 'Ошибка создания пользователя'], 500);
        }
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
        $oldAvatarPath = $this->users->getAvatarPath($id);
        if (!empty($oldAvatarPath)
            && strpos($oldAvatarPath, '/uploads/profile/') === 0
            && file_exists(APP_ROOT . '/public' . $oldAvatarPath)) {
            unlink(APP_ROOT . '/public' . $oldAvatarPath);
        }

        $avatarPath = '/uploads/profile/' . $filename;
        $updated = $this->users->updateAvatar($id, $avatarPath);

        return $updated
            ? $this->json(['success' => true, 'avatar_path' => $avatarPath])
            : $this->json(['success' => false, 'error' => 'Не удалось обновить аватар в БД'], 500);
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

        $updated = $this->users->updatePassword($id, password_hash($password, PASSWORD_DEFAULT));

        return $updated
            ? $this->json(['success' => true])
            : $this->json(['success' => false, 'error' => 'Пользователь не найден'], 404);
    }
}