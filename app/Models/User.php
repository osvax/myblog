<?php

namespace App\Models;

use App\Core\Database;

class User
{
    /**
     * Создать нового пользователя.
     *
     * @param string $email
     * @param string $passwordHash
     * @param string $name
     * @param string $phone
     * @param string $position
     * @return array
     */
    public function create(string $email, string $passwordHash, string $name, string $phone = '', string $position = ''): array
    {
        $db = Database::connection();

        // Проверяем, не занят ли email
        $stmt = $db->prepare("SELECT id FROM " . db_table('users') . " WHERE email = :email LIMIT 1");
        $stmt->execute([':email' => $email]);
        if ($stmt->fetch()) {
            return ['success' => false, 'error' => 'Пользователь с таким email уже существует'];
        }

        $stmt = $db->prepare(
            "INSERT INTO " . db_table('users') . " (email, password_hash, name, phone, position, created_at, updated_at)
             VALUES (:email, :password_hash, :name, :phone, :position, NOW(), NOW())"
        );
        $stmt->execute([
            ':email' => $email,
            ':password_hash' => $passwordHash,
            ':name' => $name,
            ':phone' => $phone,
            ':position' => $position,
        ]);

        return ['success' => true, 'user_id' => (int) $db->lastInsertId()];
    }

    /**
     * Найти пользователя по email.
     *
     * @param string $email
     * @return array|null
     */
    public function findByEmail(string $email): ?array
    {
        $db = Database::connection();
        $stmt = $db->prepare("SELECT * FROM " . db_table('users') . " WHERE email = :email LIMIT 1");
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    /**
     * Найти пользователя по ID.
     *
     * @param int $id
     * @return array|null
     */
    public function findById(int $id): ?array
    {
        $db = Database::connection();
        $stmt = $db->prepare(
            "SELECT id, email, name, avatar_path, role, is_active, created_at, updated_at,
                    last_visit, last_ip, phone, about, position, bio, timezone, language,
                    totp_secret, totp_enabled
             FROM " . db_table('users') . " WHERE id = :id LIMIT 1"
        );
        $stmt->execute([':id' => $id]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    /**
     * Проверить учётные данные.
     *
     * @param string $email
     * @param string $password
     * @return array
     */
    public function verifyCredentials(string $email, string $password): array
    {
        $db = Database::connection();
        $stmt = $db->prepare("SELECT * FROM " . db_table('users') . " WHERE email = :email LIMIT 1");
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch();

        if (!$user) {
            return ['valid' => false, 'error' => 'Неверный email или пароль'];
        }

        if (!$user['is_active']) {
            return ['valid' => false, 'error' => 'Аккаунт заблокирован'];
        }

        if (!password_verify($password, $user['password_hash'])) {
            return ['valid' => false, 'error' => 'Неверный email или пароль'];
        }

        return [
            'valid' => true,
            'user' => [
                'id' => (int) $user['id'],
                'email' => $user['email'],
                'name' => $user['name'],
                'avatar_path' => $user['avatar_path'] ?? '',
                'role' => $user['role'],
                'phone' => $user['phone'] ?? '',
                'position' => $user['position'] ?? '',
                'about' => $user['about'] ?? '',
                'bio' => $user['bio'] ?? '',
                'last_visit' => $user['last_visit'] ?? null,
                'last_ip' => $user['last_ip'] ?? '',
                'timezone' => $user['timezone'] ?? '',
                'language' => $user['language'] ?? '',
                'totp_enabled' => !empty($user['totp_enabled']),
            ],
        ];
    }

    /**
     * Найти пользователя по токену сброса пароля.
     *
     * @param string $token
     * @return array|null
     */
    public function findByResetToken(string $token): ?array
    {
        if (!preg_match('/^[a-f0-9]{64}$/', $token)) {
            return null;
        }

        $db = Database::connection();
        $stmt = $db->prepare(
            "SELECT * FROM " . db_table('users') . " WHERE reset_token = :token AND reset_expires > NOW() LIMIT 1"
        );
        $stmt->execute([':token' => $token]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    /**
     * Обновить пароль и очистить токен сброса.
     *
     * @param int $userId
     * @param string $newPasswordHash
     * @return array
     */
    public function updatePassword(int $userId, string $newPasswordHash): array
    {
        $db = Database::connection();
        $stmt = $db->prepare(
            "UPDATE " . db_table('users') . " SET password_hash = :password_hash, reset_token = '', reset_expires = NULL, updated_at = NOW()
             WHERE id = :id"
        );
        $stmt->execute([
            ':password_hash' => $newPasswordHash,
            ':id' => $userId,
        ]);

        return ['success' => true];
    }

    /**
     * Установить токен сброса пароля.
     *
     * @param int $userId
     * @return array
     */
    public function updateResetToken(int $userId): array
    {
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', time() + 3600); // 1 час

        $db = Database::connection();
        $stmt = $db->prepare(
            "UPDATE " . db_table('users') . " SET reset_token = :token, reset_expires = :expires, updated_at = NOW()
             WHERE id = :id"
        );
        $stmt->execute([
            ':token' => $token,
            ':expires' => $expires,
            ':id' => $userId,
        ]);

        return ['success' => true, 'token' => $token, 'expires' => $expires];
    }

    /**
     * Обновить полный профиль пользователя.
     *
     * @param int $id
     * @param string $name
     * @param string $phone
     * @param string $about
     * @param string $position
     * @param string $bio
     * @param string $avatarPath
     * @param string $timezone
     * @param string $language
     * @return array
     */
    public function updateFullProfile(
        int $id,
        string $name,
        string $phone = '',
        string $about = '',
        string $position = '',
        string $bio = '',
        string $avatarPath = '',
        string $timezone = '',
        string $language = ''
    ): array {
        $db = Database::connection();
        $stmt = $db->prepare(
            "UPDATE " . db_table('users') . " SET
                name = :name,
                phone = :phone,
                about = :about,
                position = :position,
                bio = :bio,
                avatar_path = :avatar_path,
                timezone = :timezone,
                language = :language,
                updated_at = NOW()
             WHERE id = :id"
        );
        $stmt->execute([
            ':name' => $name,
            ':phone' => $phone,
            ':about' => $about,
            ':position' => $position,
            ':bio' => $bio,
            ':avatar_path' => $avatarPath,
            ':timezone' => $timezone,
            ':language' => $language,
            ':id' => $id,
        ]);

        return ['success' => true];
    }

    /**
     * Записать посещение и обновить последнее время.
     *
     * @param int $userId
     * @param string $pageUrl
     * @param string $pageTitle
     * @param string $ipAddress
     * @param string $userAgent
     * @return array
     */
    public function logVisit(int $userId, string $pageUrl, string $pageTitle, string $ipAddress, string $userAgent): array
    {
        $db = Database::connection();

        // Обновляем last_visit и last_ip
        try {
            $stmt = $db->prepare(
                "UPDATE " . db_table('users') . " SET last_visit = NOW(), last_ip = :ip_address WHERE id = :id"
            );
            $stmt->execute([
                ':ip_address' => $ipAddress,
                ':id' => $userId,
            ]);
        } catch (\Exception $e) {
            // Поле может не существовать в старых версиях БД
        }

        // Записываем активность
        try {
            $stmt = $db->prepare(
                "INSERT INTO " . db_table('user_activities') . " (user_id, page_url, page_title, ip_address, user_agent, visited_at)
                 VALUES (:user_id, :page_url, :page_title, :ip_address, :user_agent, NOW())"
            );
            $stmt->execute([
                ':user_id' => $userId,
                ':page_url' => $pageUrl,
                ':page_title' => $pageTitle,
                ':ip_address' => $ipAddress,
                ':user_agent' => $userAgent,
            ]);
        } catch (\Exception $e) {
            // Таблица может не существовать
        }

        return ['success' => true];
    }

    /**
     * Обновить данные пользователя (устаревший, используйте updateFullProfile).
     *
     * @param int $id
     * @param string $name
     * @param string $avatarPath
     * @return array
     */
    public function updateProfile(int $id, string $name, string $avatarPath = ''): array
    {
        return $this->updateFullProfile($id, $name);
    }

    /**
     * Сохранить секрет TOTP (двухфакторная аутентификация) и сбросить флаг включения
     * до подтверждения кода из приложения-аутентификатора.
     */
    public function setTotpSecret(int $userId, string $secret): array
    {
        $db = Database::connection();
        $stmt = $db->prepare(
            "UPDATE " . db_table('users') . " SET totp_secret = :secret, totp_enabled = 0, updated_at = NOW() WHERE id = :id"
        );
        $stmt->execute(['secret' => $secret, 'id' => $userId]);
        return ['success' => true];
    }

    /**
     * Включить или выключить двухфакторную аутентификацию.
     */
    public function setTotpEnabled(int $userId, bool $enabled): array
    {
        $db = Database::connection();
        $stmt = $db->prepare(
            "UPDATE " . db_table('users') . " SET totp_enabled = :enabled, updated_at = NOW() WHERE id = :id"
        );
        $stmt->execute([
            'enabled' => $enabled ? 1 : 0,
            'id' => $userId,
        ]);
        return ['success' => true];
    }

    /**
     * Получить всех пользователей.
     *
     * @return array
     */
    public function all(): array
    {
        $db = Database::connection();
        $stmt = $db->query(
            "SELECT id, email, name, avatar_path, role, is_active, created_at, updated_at,
                    last_visit, last_ip, phone, about, position
             FROM " . db_table('users') . " ORDER BY created_at DESC"
        );
        return $stmt->fetchAll();
    }

    /**
     * Проверить, является ли пользователь администратором.
     *
     * @param int $userId
     * @return bool
     */
    public function isAdmin(int $userId): bool
    {
        $user = $this->findById($userId);
        return $user && $user['role'] === 'admin';
    }
}
