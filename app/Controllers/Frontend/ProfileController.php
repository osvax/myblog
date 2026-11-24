<?php

declare(strict_types=1);

namespace App\Controllers\Frontend;

use App\Controllers\Controller;
use App\Core\Response;
use App\Models\SiteSettings;
use App\Models\UserRepository;

final class ProfileController extends Controller
{
    private $users;
    private $settings;

    public function __construct(
        UserRepository $users,
        SiteSettings $settings
    ) {
        $this->users = $users;
        $this->settings = $settings;
    }

    public function show($request)
    {
        $user = current_user();
        if (!$user) {
            return Response::redirect('/login.html');
        }

        $full = $this->users->findById((int) $user['id']);

        if (is_array($full) && preg_match('#^/api/admin/avatar/([a-f0-9]{32}\.(?:jpg|png|webp))$#', (string) ($full['avatar_path'] ?? ''), $avatarMatch)) {
            $full['avatar_path'] = '/uploads/profile/' . $avatarMatch[1];
        }

        return $this->page('frontend', 'profile', [
            'title' => 'Профиль',
            'activePage' => 'profile',
            'user' => $full ?: $user,
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

        $result = $this->users->updateProfile((int) $user['id'], $name, $user['avatar_path'] ?? '');
        if (!$result['success']) {
            return $this->json(['success' => false, 'error' => 'Не удалось обновить профиль'], 500);
        }

        $_SESSION['user_name'] = $name;

        return $this->json(['success' => true]);
    }

    public function uploadAvatar($request)
    {
        $user = current_user();
        if (!$user) {
            return $this->json(['success' => false, 'error' => 'Не авторизован'], 401);
        }
        if ($request->method() !== 'POST') {
            return $this->json(['success' => false, 'error' => 'Метод не поддерживается'], 405);
        }
        if (!isset($_FILES['avatar']) || !is_array($_FILES['avatar'])) {
            return $this->json(['success' => false, 'error' => 'Выберите изображение аватара'], 400);
        }
        $file = $_FILES['avatar'];
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK || !is_uploaded_file($file['tmp_name'] ?? '')) {
            return $this->json(['success' => false, 'error' => 'Не удалось принять изображение'], 400);
        }
        if ((int) ($file['size'] ?? 0) < 1 || (int) $file['size'] > 2 * 1024 * 1024) {
            return $this->json(['success' => false, 'error' => 'Изображение должно быть не больше 2 МБ'], 400);
        }
        if (!extension_loaded('fileinfo') || !function_exists('getimagesize')) {
            return $this->json(['success' => false, 'error' => 'На сервере не настроена проверка изображений'], 500);
        }
        $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
        $extensions = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
        $image = @getimagesize($file['tmp_name']);
        if (!is_string($mime) || !isset($extensions[$mime]) || $image === false || ($image['mime'] ?? '') !== $mime) {
            return $this->json(['success' => false, 'error' => 'Разрешены только изображения JPEG, PNG и WebP'], 400);
        }
        if ((int) $image[0] < 1 || (int) $image[1] < 1 || (int) $image[0] > 5000 || (int) $image[1] > 5000 || (int) $image[0] * (int) $image[1] > 20000000) {
            return $this->json(['success' => false, 'error' => 'Слишком большой размер изображения'], 400);
        }

        $root = APP_ROOT . '/public/uploads';
        $directory = $root . '/profile';
        if (is_link($root) || is_link($directory)) {
            return $this->json(['success' => false, 'error' => 'Некорректная папка для загрузки'], 500);
        }
        foreach ([$root, $directory] as $path) {
            if (!is_dir($path) && !mkdir($path, 0755) && !is_dir($path)) {
                return $this->json(['success' => false, 'error' => 'Не удалось создать папку для аватаров'], 500);
            }
        }
        $filename = bin2hex(random_bytes(16)) . '.' . $extensions[$mime];
        $target = $directory . '/' . $filename;
        if (!move_uploaded_file($file['tmp_name'], $target)) {
            return $this->json(['success' => false, 'error' => 'Не удалось сохранить изображение'], 500);
        }

        $oldAvatar = (string) ($user['avatar_path'] ?? '');
        $avatarPath = '/uploads/profile/' . $filename;
        if (!$this->users->updateAvatar((int) $user['id'], $avatarPath)) {
            @unlink($target);
            return $this->json(['success' => false, 'error' => 'Не удалось обновить профиль'], 500);
        }
        if (preg_match('#^/uploads/profile/[a-f0-9]{32}\.(?:jpg|png|webp)$#', $oldAvatar)) {
            $oldPath = APP_ROOT . '/public' . $oldAvatar;
            if (is_file($oldPath) && !is_link($oldPath)) {
                @unlink($oldPath);
            }
        }
        $_SESSION['user_avatar_path'] = $avatarPath;
        return $this->json(['success' => true, 'avatar_path' => $avatarPath]);
    }

    public function serveAvatar($request, array $params = [])
    {
        if (!in_array($request->method(), ['GET', 'HEAD'], true)) {
            return new Response('', 405, ['Allow' => 'GET, HEAD']);
        }
        $filename = (string) ($params['filename'] ?? '');
        if (!preg_match('/^[a-f0-9]{32}\.(jpg|png|webp)$/', $filename)) {
            return new Response('', 404);
        }
        $path = APP_ROOT . '/public/uploads/profile/' . $filename;
        $realDirectory = realpath(APP_ROOT . '/public/uploads/profile');
        $realPath = realpath($path);
        if ($realDirectory === false || $realPath === false || dirname($realPath) !== $realDirectory || is_link($realPath) || !is_file($realPath)) {
            return new Response('', 404);
        }
        $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($realPath);
        $types = ['jpg' => 'image/jpeg', 'png' => 'image/png', 'webp' => 'image/webp'];
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        if (!is_string($mime) || ($types[$extension] ?? '') !== $mime) {
            return new Response('', 404);
        }
        $body = $request->method() === 'HEAD' ? '' : file_get_contents($realPath);
        return new Response($body === false ? '' : $body, 200, [
            'Content-Type' => $mime,
            'Content-Length' => (string) filesize($realPath),
            'Cache-Control' => 'public, max-age=3600',
            'X-Content-Type-Options' => 'nosniff',
        ]);
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
        $stmt = $db->prepare('SELECT password_hash FROM ' . db_table('users') . ' WHERE id = :id LIMIT 1');
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
        $stmt = $db->prepare('UPDATE ' . db_table('users') . ' SET password_hash = :hash, updated_at = NOW() WHERE id = :id');
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
        $stmt = $db->prepare('SELECT password_hash FROM ' . db_table('users') . ' WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $user['id']]);
        $dbUser = $stmt->fetch();

        if (!$dbUser || !password_verify($password, $dbUser['password_hash'])) {
            return $this->json(['success' => false, 'error' => 'Неверный пароль'], 400);
        }

        $stmt = $db->prepare('DELETE FROM ' . db_table('users') . ' WHERE id = :id');
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
