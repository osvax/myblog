<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\Controller;
use App\Core\ApiResource;
use App\Models\UserActivity;
use App\Models\UserRepository;
use App\Transformers\UserTransformer;

final class AdminProfileController extends Controller
{
    private $users;
    private $activity;
    private $userTransformer;

    public function __construct(
        UserRepository $users,
        UserActivity $activity,
        UserTransformer $userTransformer
    ) {
        $this->users = $users;
        $this->activity = $activity;
        $this->userTransformer = $userTransformer;
    }

    /**
     * Получить полный профиль пользователя.
     */
    public function show($request, $parameters)
    {
        if (!\admin_is_authenticated()) {
            return $this->json(ApiResource::error('Требуется авторизация'), 401);
        }
        if ($request->method() !== 'GET') {
            return $this->json(ApiResource::error('Метод не поддерживается'), 405);
        }

        $id = isset($parameters['id']) ? (int) $parameters['id'] : 0;
        if ($id <= 0) {
            return $this->json(ApiResource::error('Неверный ID'), 400);
        }

        // Доступен только свой профиль (чужие пользователи — раздел «Пользователи»)
        if ($id !== \admin_get_user_id()) {
            return $this->json(ApiResource::error('Можно просматривать только свой профиль'), 403);
        }

        $userDto = $this->users->findDtoById($id);
        if (!$userDto) {
            return $this->json(ApiResource::error('Пользователь не найден'), 404);
        }

        $pageStats = $this->activity->getPageStats($id, 5);
        $recentActivities = $this->activity->getHistory($id, 1, 10);

        return $this->json(ApiResource::success(
            $this->userTransformer->transformOwnProfile($userDto, $pageStats, $recentActivities)
        ));
    }

    /**
     * Обновить профиль пользователя.
     */
    public function update($request, $parameters)
    {
        if ($request->method() !== 'PUT') {
            return $this->json(ApiResource::error('Метод не поддерживается'), 405);
        }

        \admin_require_csrf();

        $id = isset($parameters['id']) ? (int) $parameters['id'] : 0;
        if ($id <= 0) {
            return $this->json(ApiResource::error('Неверный ID'), 400);
        }

        // Доступен только свой профиль (чужие пользователи — раздел «Пользователи»)
        if ($id !== \admin_get_user_id()) {
            return $this->json(ApiResource::error('Можно менять только свой профиль'), 403);
        }

        $input = $request->json();
        $name = isset($input['name']) && is_string($input['name']) ? trim($input['name']) : '';
        $phone = isset($input['phone']) && is_string($input['phone']) ? trim($input['phone']) : '';
        $about = isset($input['about']) && is_string($input['about']) ? trim($input['about']) : '';
        $position = isset($input['position']) && is_string($input['position']) ? trim($input['position']) : '';
        $bio = isset($input['bio']) && is_string($input['bio']) ? trim($input['bio']) : '';
        $timezone = isset($input['timezone']) && is_string($input['timezone']) ? trim($input['timezone']) : '';
        $language = isset($input['language']) && is_string($input['language']) ? trim($input['language']) : '';

        if (strlen($name) < 2 || strlen($name) > 160) {
            return $this->json(ApiResource::error('Имя должно содержать от 2 до 160 символов'), 400);
        }

        // Аватар обновляется отдельным запросом, поэтому при сохранении профиля
        // сохраняем текущее значение, а не затираем его пустой строкой.
        $existing = $this->users->findById($id);
        if (!$existing) {
            return $this->json(ApiResource::error('Пользователь не найден'), 404);
        }

        $result = $this->users->updateFullProfile(
            $id,
            $name,
            $phone,
            $about,
            $position,
            $bio,
            (string) ($existing['avatar_path'] ?? ''),
            $timezone,
            $language
        );
        if (!$result['success']) {
            return $this->json(ApiResource::error('Не удалось обновить'), 500);
        }

        return $this->json(ApiResource::success());
    }

    /**
     * Сменить пароль своего аккаунта (только для владельца сессии).
     *
     * В разделе «Пользователи» сброс своего пароля запрещён
     * (защита от само-блокировки), поэтому у владельца есть
     * отдельный путь — через свой профиль.
     */
    public function changePassword($request, $parameters)
    {
        if (!\admin_is_authenticated()) {
            return $this->json(ApiResource::error('Требуется авторизация'), 401);
        }
        if ($request->method() !== 'POST') {
            return $this->json(ApiResource::error('Метод не поддерживается'), 405);
        }

        \admin_require_csrf();

        $id = isset($parameters['id']) ? (int) $parameters['id'] : 0;
        if ($id <= 0) {
            return $this->json(ApiResource::error('Неверный ID'), 400);
        }

        // Менять можно только свой пароль
        if ($id !== \admin_get_user_id()) {
            return $this->json(ApiResource::error('Можно менять только свой пароль'), 403);
        }

        $input = $request->json();
        $oldPassword = isset($input['old_password']) && is_string($input['old_password']) ? $input['old_password'] : '';
        $newPassword = isset($input['new_password']) && is_string($input['new_password']) ? $input['new_password'] : '';
        $newPasswordConfirm = isset($input['new_password_confirm']) && is_string($input['new_password_confirm']) ? $input['new_password_confirm'] : '';

        $db = \App\Core\Database::connection();
        $stmt = $db->prepare('SELECT password_hash FROM ' . db_table('users') . ' WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($oldPassword, $user['password_hash'])) {
            return $this->json(ApiResource::error('Неверный текущий пароль'), 400);
        }
        if (strlen($newPassword) < 8) {
            return $this->json(ApiResource::error('Новый пароль должен содержать минимум 8 символов'), 400);
        }
        if ($newPassword !== $newPasswordConfirm) {
            return $this->json(ApiResource::error('Новые пароли не совпадают'), 400);
        }

        $stmt = $db->prepare('UPDATE ' . db_table('users') . ' SET password_hash = :hash, updated_at = NOW() WHERE id = :id');
        $stmt->execute([
            ':hash' => password_hash($newPassword, PASSWORD_DEFAULT),
            ':id' => $id,
        ]);

        return $this->json(ApiResource::success());
    }

    /**
     * Загрузить аватар пользователя.
     */
    public function uploadAvatar($request, $parameters)
    {
        if ($request->method() !== 'POST') {
            return $this->json(ApiResource::error('Метод не поддерживается'), 405);
        }

        \admin_require_csrf();

        $id = isset($parameters['id']) ? (int) $parameters['id'] : 0;
        if ($id <= 0) {
            return $this->json(ApiResource::error('Неверный ID'), 400);
        }

        // Доступен только свой профиль (чужие аватары загружаются в разделе «Пользователи»)
        if ($id !== \admin_get_user_id()) {
            return $this->json(ApiResource::error('Можно менять только свой аватар'), 403);
        }

        if (!isset($_FILES['avatar']) || $_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
            return $this->json(ApiResource::error('Файл не загружен'), 400);
        }

        $file = $_FILES['avatar'];
        $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
        $maxSize = 2 * 1024 * 1024; // 2MB

        if (!in_array($file['type'], $allowedTypes, true)) {
            return $this->json(ApiResource::error('Разрешены только JPEG, PNG, WebP'), 400);
        }
        if ($file['size'] > $maxSize) {
            return $this->json(ApiResource::error('Файл слишком большой (макс. 2 МБ)'), 400);
        }

        // Создаём директорию
        $uploadDir = APP_ROOT . '/public/uploads/profile/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // Генерируем уникальное имя файла (хэш)
        $extension = match ($file['type']) {
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            default => 'jpg',
        };
        $filename = md5($id . time() . uniqid()) . '.' . $extension;
        $filepath = $uploadDir . $filename;

        if (!move_uploaded_file($file['tmp_name'], $filepath)) {
            return $this->json(ApiResource::error('Не удалось сохранить файл'), 500);
        }

        // Удаляем старый аватар
        $db = \App\Core\Database::connection();
        $stmt = $db->prepare('SELECT avatar_path FROM ' . db_table('users') . ' WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $user = $stmt->fetch();
        if (!empty($user['avatar_path']) && file_exists(APP_ROOT . '/public' . $user['avatar_path'])) {
            unlink(APP_ROOT . '/public' . $user['avatar_path']);
        }

        // Обновляем путь в БД
        $avatarPath = '/uploads/profile/' . $filename;
        $stmt = $db->prepare('UPDATE ' . db_table('users') . ' SET avatar_path = :avatar_path, updated_at = NOW() WHERE id = :id');
        $stmt->execute([
            ':avatar_path' => $avatarPath,
            ':id' => $id,
        ]);

        return $this->json(ApiResource::success(['avatar_path' => $avatarPath]));
    }
}
