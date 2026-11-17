<?php

namespace App\Controllers\Admin;

use App\Controllers\Controller;
use App\Core\Response;
use App\Models\User;
use App\Models\UserActivity;

class AdminProfileController extends Controller
{
    /**
     * Получить полный профиль пользователя.
     */
    public function show($request, $parameters)
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

        $activities = new UserActivity();

        $pageStats = $activities->getPageStats($id, 5);
        $recentActivities = $activities->getHistory($id, 1, 10);

        return $this->json([
            'success' => true,
            'user' => [
                'id' => (int) $user['id'],
                'email' => $user['email'],
                'name' => $user['name'],
                'role' => $user['role'],
                'is_active' => (bool) $user['is_active'],
                'avatar_path' => $user['avatar_path'] ?? '',
                'phone' => $user['phone'] ?? '',
                'about' => $user['about'] ?? '',
                'position' => $user['position'] ?? '',
                'bio' => $user['bio'] ?? '',
                'timezone' => $user['timezone'] ?? '',
                'language' => $user['language'] ?? '',
                'created_at' => $user['created_at'],
                'updated_at' => $user['updated_at'],
                'last_visit' => $user['last_visit'] ?? null,
                'last_ip' => $user['last_ip'] ?? null,
            ],
            'page_stats' => $pageStats,
            'recent_activities' => $recentActivities,
        ]);
    }

    /**
     * Обновить профиль пользователя.
     */
    public function update($request, $parameters)
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
        $phone = isset($input['phone']) && is_string($input['phone']) ? trim($input['phone']) : '';
        $about = isset($input['about']) && is_string($input['about']) ? trim($input['about']) : '';
        $position = isset($input['position']) && is_string($input['position']) ? trim($input['position']) : '';
        $bio = isset($input['bio']) && is_string($input['bio']) ? trim($input['bio']) : '';
        $timezone = isset($input['timezone']) && is_string($input['timezone']) ? trim($input['timezone']) : '';
        $language = isset($input['language']) && is_string($input['language']) ? trim($input['language']) : '';

        if (strlen($name) < 2 || strlen($name) > 160) {
            return $this->json(['success' => false, 'error' => 'Имя должно содержать от 2 до 160 символов'], 400);
        }

        // Аватар обновляется отдельным запросом, поэтому при сохранении профиля
        // сохраняем текущее значение, а не затираем его пустой строкой.
        $existing = (new User())->findById($id);
        if (!$existing) {
            return $this->json(['success' => false, 'error' => 'Пользователь не найден'], 404);
        }

        $result = (new User())->updateFullProfile(
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
            return $this->json(['success' => false, 'error' => 'Не удалось обновить'], 500);
        }

        return $this->json(['success' => true]);
    }

    /**
     * Загрузить аватар пользователя.
     */
    public function uploadAvatar($request, $parameters)
    {
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
            return $this->json(['success' => false, 'error' => 'Не удалось сохранить файл'], 500);
        }

        // Удаляем старый аватар
        $db = \App\Core\Database::connection();
        $stmt = $db->prepare("SELECT avatar_path FROM users WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $user = $stmt->fetch();
        if (!empty($user['avatar_path']) && file_exists(APP_ROOT . '/public' . $user['avatar_path'])) {
            unlink(APP_ROOT . '/public' . $user['avatar_path']);
        }

        // Обновляем путь в БД
        $avatarPath = '/uploads/profile/' . $filename;
        $stmt = $db->prepare("UPDATE users SET avatar_path = :avatar_path, updated_at = NOW() WHERE id = :id");
        $stmt->execute([
            ':avatar_path' => $avatarPath,
            ':id' => $id,
        ]);

        return $this->json([
            'success' => true,
            'avatar_path' => $avatarPath,
        ]);
    }

    /**
     * Получить историю посещений.
     */
    public function getActivityHistory($request, $parameters)
    {
        if ($request->method() !== 'GET') {
            return $this->json(['success' => false, 'error' => 'Метод не поддерживается'], 405);
        }

        $id = isset($parameters['id']) ? (int) $parameters['id'] : 0;
        if ($id <= 0) {
            return $this->json(['success' => false, 'error' => 'Неверный ID'], 400);
        }

        $page = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
        $perPage = 20;

        $activities = new UserActivity();
        $totalCount = $activities->getTotalCount($id);
        $history = $activities->getHistory($id, $page, $perPage);

        return $this->json([
            'success' => true,
            'activities' => $history,
            'pagination' => [
                'page' => $page,
                'per_page' => $perPage,
                'total' => $totalCount,
                'pages' => ceil($totalCount / $perPage),
            ],
        ]);
    }
}
