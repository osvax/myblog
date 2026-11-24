<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\QueryBuilder;
use App\DTO\UserDTO;
use PDO;

final class UserRepository
{
    private $db;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? \App\Core\Database::connection();
    }

    /**
     * Базовый QueryBuilder для users с JOIN на roles для получения названия роли.
     */
    private function baseQuery(): QueryBuilder
    {
        return (new QueryBuilder($this->db, 'users u'))
            ->select([
                'u.*',
                'r.name AS role_name',
                'r.description AS role_description',
            ])
            ->leftJoin('roles r', 'r.role_key', '=', 'u.role');
    }

    /**
     * Все пользователи (для обратной совместимости — возвращает массивы).
     *
     * @return array
     */
    public function all(): array
    {
        $rows = $this->baseQuery()
            ->orderBy('u.created_at', 'DESC')
            ->get();

        return $rows;
    }

    /**
     * Пользователь по ID (для обратной совместимости — возвращает массив).
     *
     * @param int $id
     * @return array|null
     */
    public function findById(int $id): ?array
    {
        $row = $this->baseQuery()
            ->where('u.id', '=', $id)
            ->first();

        return $row ?: null;
    }

    /**
     * Пользователь по email (для обратной совместимости — возвращает массив).
     *
     * @param string $email
     * @return array|null
     */
    public function findByEmail(string $email): ?array
    {
        $row = $this->baseQuery()
            ->where('u.email', '=', $email)
            ->first();

        return $row ?: null;
    }

    /**
     * Все пользователи как массив UserDTO (новый API).
     *
     * @return UserDTO[]
     */
    public function allAsDto(): array
    {
        $rows = $this->baseQuery()
            ->orderBy('u.created_at', 'DESC')
            ->get();

        return array_map(function ($row) {
            return UserDTO::fromDatabase($row);
        }, $rows);
    }

    /**
     * Пользователь по ID как UserDTO (новый API).
     *
     * @param int $id
     * @return UserDTO|null
     */
    public function findDtoById(int $id): ?UserDTO
    {
        $row = $this->baseQuery()
            ->where('u.id', '=', $id)
            ->first();

        return $row ? UserDTO::fromDatabase($row) : null;
    }

    /**
     * Пользователь по email как UserDTO (новый API).
     *
     * @param string $email
     * @return UserDTO|null
     */
    public function findDtoByEmail(string $email): ?UserDTO
    {
        $row = $this->baseQuery()
            ->where('u.email', '=', $email)
            ->first();

        return $row ? UserDTO::fromDatabase($row) : null;
    }

    /**
     * Сохраняет пользователя из UserDTO (новый предпочтительный способ).
     *
     * @param UserDTO $dto
     * @return int|false ID сохранённого пользователя или false при ошибке
     */
    public function saveUserFromDTO(UserDTO $dto)
    {
        // Валидируем DTO перед сохранением
        if (!$dto->validate()) {
            throw new \InvalidArgumentException('UserDTO validation failed: ' . json_encode($dto->getErrors()));
        }

        // Хешируем пароль если задан
        $dto->hashPassword();

        $data = $dto->toArray();
        $table = db_table('users');

        if ($dto->id === null) {
            // INSERT — новый пользователь
            unset($data['id']);
            $columns = array_keys($data);
            $placeholders = implode(',', array_fill(0, count($columns), '?'));
            $colList = implode(',', $columns);

            $sql = "INSERT INTO $table ($colList) VALUES ($placeholders)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(array_values($data));

            return (int)$this->db->lastInsertId();
        } else {
            // UPDATE — существующий пользователь
            $updateParts = [];
            foreach ($data as $col => $val) {
                if ($col !== 'id') {
                    $updateParts[] = "$col = ?";
                }
            }
            $sql = "UPDATE $table SET " . implode(', ', $updateParts) . ' WHERE id = ?';

            $values = [];
            foreach ($data as $col => $val) {
                if ($col !== 'id') {
                    $values[] = $val;
                }
            }
            $values[] = $dto->id;

            $stmt = $this->db->prepare($sql);
            $stmt->execute($values);

            // Проверяем, существует ли запись (rowCount=0 может быть если данные не изменились)
            if ($stmt->rowCount() === 0) {
                $exists = $this->db->prepare("SELECT 1 FROM $table WHERE id = ?");
                $exists->execute([$dto->id]);
                return $exists->fetchColumn() !== false ? (int)$dto->id : false;
            }

            return (int)$dto->id;
        }
    }

    /**
     * Обновляет пользователя из UserDTO (для PUT — без пароля).
     *
     * @param UserDTO $dto
     * @return int|false
     */
    public function updateUserFromDTO(UserDTO $dto)
    {
        if (!$dto->validateUpdate()) {
            throw new \InvalidArgumentException('UserDTO validation failed: ' . json_encode($dto->getErrors()));
        }

        // Пароль не обновляется здесь — отдельный метод
        $dto->hashPassword(); // не сделает ничего, если plainPassword пустой

        $data = $dto->toArray();
        $table = db_table('users');

        $updateParts = [];
        foreach ($data as $col => $val) {
            if ($col !== 'id' && $col !== 'password_hash') { // password_hash не обновляем здесь
                $updateParts[] = "$col = ?";
            }
        }
        // Всегда обновляем updated_at
        $updateParts[] = 'updated_at = NOW()';

        $sql = "UPDATE $table SET " . implode(', ', $updateParts) . ' WHERE id = ?';

        $values = [];
        foreach ($data as $col => $val) {
            if ($col !== 'id' && $col !== 'password_hash') {
                $values[] = $val;
            }
        }
        $values[] = $dto->id;

        $stmt = $this->db->prepare($sql);
        $stmt->execute($values);

        if ($stmt->rowCount() === 0) {
            $exists = $this->db->prepare("SELECT 1 FROM $table WHERE id = ?");
            $exists->execute([$dto->id]);
            return $exists->fetchColumn() !== false ? (int)$dto->id : false;
        }

        return (int)$dto->id;
    }

    /**
     * Создаёт пользователя из массива (обратная совместимость).
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
        // Проверяем, не занят ли email
        $stmt = $this->db->prepare('SELECT id FROM ' . db_table('users') . ' WHERE email = :email LIMIT 1');
        $stmt->execute([':email' => $email]);
        if ($stmt->fetch()) {
            return ['success' => false, 'error' => 'Пользователь с таким email уже существует'];
        }

        $dto = new UserDTO();
        $dto->email = $email;
        $dto->passwordHash = $passwordHash;
        $dto->name = $name;
        $dto->phone = $phone;
        $dto->position = $position;
        $dto->role = 'user'; // дефолтная роль
        $dto->isActive = true;

        $id = $this->saveUserFromDTO($dto);
        if ($id === false) {
            return ['success' => false, 'error' => 'Не удалось создать пользователя'];
        }

        return ['success' => true, 'user_id' => $id];
    }

    /**
     * Удаляет пользователя.
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM ' . db_table('users') . ' WHERE id = :id');
        $stmt->execute(['id' => $id]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Смена пароля пользователя.
     *
     * @param int $id
     * @param string $newPasswordHash
     * @return bool
     */
    public function updatePassword(int $id, string $newPasswordHash): bool
    {
        $stmt = $this->db->prepare('UPDATE ' . db_table('users') . ' SET password_hash = :password_hash, updated_at = NOW() WHERE id = :id');
        $stmt->execute([
            ':password_hash' => $newPasswordHash,
            ':id' => $id,
        ]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Обновляет аватар пользователя.
     *
     * @param int $id
     * @param string $avatarPath
     * @return bool
     */
    public function updateAvatar(int $id, string $avatarPath): bool
    {
        $stmt = $this->db->prepare('UPDATE ' . db_table('users') . ' SET avatar_path = :avatar_path, updated_at = NOW() WHERE id = :id');
        $stmt->execute([
            ':avatar_path' => $avatarPath,
            ':id' => $id,
        ]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Получает путь к аватару пользователя.
     *
     * @param int $id
     * @return string|null
     */
    public function getAvatarPath(int $id): ?string
    {
        $stmt = $this->db->prepare('SELECT avatar_path FROM ' . db_table('users') . ' WHERE id = :id');
        $stmt->execute([':id' => $id]);
        return $stmt->fetchColumn() ?: null;
    }

    /**
     * Обновляет последние данные визита.
     *
     * @param int $id
     * @param string $ip
     * @return bool
     */
    public function updateLastVisit(int $id, string $ip): bool
    {
        $stmt = $this->db->prepare('UPDATE ' . db_table('users') . ' SET last_visit = NOW(), last_ip = :ip WHERE id = :id');
        $stmt->execute([
            ':ip' => $ip,
            ':id' => $id,
        ]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Проверяет, существует ли email (кроме указанного ID).
     *
     * @param string $email
     * @param int|null $excludeId
     * @return bool
     */
    public function emailExists(string $email, ?int $excludeId = null): bool
    {
        $sql = 'SELECT id FROM ' . db_table('users') . ' WHERE email = :email';
        $params = [':email' => $email];

        if ($excludeId !== null) {
            $sql .= ' AND id != :id';
            $params[':id'] = $excludeId;
        }
        $sql .= ' LIMIT 1';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch() !== false;
    }

    /**
     * Проверяет, существует ли роль в таблице roles.
     *
     * @param string $roleKey
     * @return bool
     */
    public function roleExists(string $roleKey): bool
    {
        $stmt = $this->db->prepare('SELECT 1 FROM ' . db_table('roles') . ' WHERE role_key = :role_key LIMIT 1');
        $stmt->execute([':role_key' => $roleKey]);
        return $stmt->fetch() !== false;
    }

    /**
     * Пользователи по роли.
     *
     * @param string $role
     * @return UserDTO[]
     */
    public function usersByRole(string $role): array
    {
        $rows = $this->baseQuery()
            ->where('u.role', '=', $role)
            ->orderBy('u.name', 'ASC')
            ->get();

        return array_map(function ($row) {
            return UserDTO::fromDatabase($row);
        }, $rows);
    }

    /**
     * Активные пользователи.
     *
     * @return UserDTO[]
     */
    public function activeUsers(): array
    {
        $rows = $this->baseQuery()
            ->where('u.is_active', '=', 1)
            ->orderBy('u.name', 'ASC')
            ->get();

        return array_map(function ($row) {
            return UserDTO::fromDatabase($row);
        }, $rows);
    }

    /**
     * Общее количество пользователей.
     *
     * @return int
     */
    public function count(): int
    {
        $stmt = $this->db->query('SELECT COUNT(*) FROM ' . db_table('users'));
        return (int) $stmt->fetchColumn();
    }

    // ===================== НОВЫЕ МЕТОДЫ (из User.php) =====================

    /**
     * Найти пользователя по Яндекс ID (обратная совместимость — возвращает массив).
     *
     * @param string $yandexId
     * @return array|null
     */
    public function findByYandexId(string $yandexId): ?array
    {
        $row = $this->baseQuery()
            ->where('u.yandex_id', '=', $yandexId)
            ->first();

        return $row ?: null;
    }

    /**
     * Найти пользователя по Яндекс ID (новый API — возвращает UserDTO).
     *
     * @param string $yandexId
     * @return UserDTO|null
     */
    public function findDtoByYandexId(string $yandexId): ?UserDTO
    {
        $row = $this->baseQuery()
            ->where('u.yandex_id', '=', $yandexId)
            ->first();

        return $row ? UserDTO::fromDatabase($row) : null;
    }

    /**
     * Связать пользователя с Яндекс ID.
     *
     * @param int $userId
     * @param string $yandexId
     * @return void
     */
    public function linkYandex(int $userId, string $yandexId): void
    {
        $stmt = $this->db->prepare(
            'UPDATE ' . db_table('users') . ' SET yandex_id = :id, updated_at = NOW() WHERE id = :user'
        );
        $stmt->execute(['id' => $yandexId, 'user' => $userId]);
    }

    /**
     * Установить роль пользователя.
     *
     * @param int $userId
     * @param string $role
     * @return void
     */
    public function setRole(int $userId, string $role): void
    {
        $allowed = ['user', 'editor', 'manager', 'admin'];
        if (!in_array($role, $allowed, true)) {
            $role = 'user';
        }
        $stmt = $this->db->prepare(
            'UPDATE ' . db_table('users') . ' SET role = :role, updated_at = NOW() WHERE id = :id'
        );
        $stmt->execute(['role' => $role, 'id' => $userId]);
    }

    /**
     * Проверить учётные данные (логин).
     *
     * @param string $email
     * @param string $password
     * @return array ['valid' => bool, 'user' => UserDTO|null, 'error' => string|null]
     */
    public function verifyCredentials(string $email, string $password): array
    {
        $userDto = $this->findDtoByEmail($email);

        if (!$userDto) {
            return ['valid' => false, 'user' => null, 'error' => 'Неверный email или пароль'];
        }

        if (!$userDto->isActive) {
            return ['valid' => false, 'user' => null, 'error' => 'Аккаунт заблокирован'];
        }

        if (!password_verify($password, $userDto->passwordHash)) {
            return ['valid' => false, 'user' => null, 'error' => 'Неверный email или пароль'];
        }

        return [
            'valid' => true,
            'user' => $userDto,
            'error' => null,
        ];
    }

    /**
     * Найти пользователя по токену сброса пароля (обратная совместимость — возвращает массив).
     *
     * @param string $token
     * @return array|null
     */
    public function findByResetToken(string $token): ?array
    {
        if (!preg_match('/^[a-f0-9]{64}$/', $token)) {
            return null;
        }

        $row = $this->baseQuery()
            ->where('u.reset_token', '=', $token)
            ->where('u.reset_expires', '>', date('Y-m-d H:i:s'))
            ->first();

        return $row ?: null;
    }

    /**
     * Найти пользователя по токену сброса пароля (новый API — возвращает UserDTO).
     *
     * @param string $token
     * @return UserDTO|null
     */
    public function findDtoByResetToken(string $token): ?UserDTO
    {
        if (!preg_match('/^[a-f0-9]{64}$/', $token)) {
            return null;
        }

        $row = $this->baseQuery()
            ->where('u.reset_token', '=', $token)
            ->where('u.reset_expires', '>', date('Y-m-d H:i:s'))
            ->first();

        return $row ? UserDTO::fromDatabase($row) : null;
    }

    /**
     * Обновить пароль и очистить токен сброса.
     *
     * @param int $userId
     * @param string $newPasswordHash
     * @return bool
     */
    public function updatePasswordWithToken(int $userId, string $newPasswordHash): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE ' . db_table('users') . " SET password_hash = :password_hash, reset_token = '', reset_expires = NULL, updated_at = NOW()
             WHERE id = :id"
        );
        $stmt->execute([
            ':password_hash' => $newPasswordHash,
            ':id' => $userId,
        ]);

        return $stmt->rowCount() > 0;
    }

    /**
     * Установить токен сброса пароля.
     *
     * @param int $userId
     * @return array ['success' => bool, 'token' => string|null, 'expires' => string|null]
     */
    public function updateResetToken(int $userId): array
    {
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', time() + 3600); // 1 час

        $stmt = $this->db->prepare(
            'UPDATE ' . db_table('users') . ' SET reset_token = :token, reset_expires = :expires, updated_at = NOW()
             WHERE id = :id'
        );
        $stmt->execute([
            ':token' => $token,
            ':expires' => $expires,
            ':id' => $userId,
        ]);

        return ['success' => $stmt->rowCount() > 0, 'token' => $token, 'expires' => $expires];
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
     * @return bool
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
    ): bool {
        $userDto = $this->findDtoById($id);
        if (!$userDto) {
            return false;
        }

        $userDto->name = $name;
        $userDto->phone = $phone;
        $userDto->about = $about;
        $userDto->position = $position;
        $userDto->bio = $bio;
        $userDto->avatarPath = $avatarPath ?: $userDto->avatarPath;
        $userDto->timezone = $timezone ?: $userDto->timezone;
        $userDto->language = $language ?: $userDto->language;

        $savedId = $this->updateUserFromDTO($userDto);
        return $savedId !== false;
    }

    /**
     * Обновить профиль (устаревший метод, делегирует к updateFullProfile).
     *
     * @param int $id
     * @param string $name
     * @param string $avatarPath
     * @return bool
     */
    public function updateProfile(int $id, string $name, string $avatarPath = ''): bool
    {
        return $this->updateFullProfile($id, $name, '', '', '', '', $avatarPath);
    }

    /**
     * Записать посещение и обновить последнее время.
     * Делегирует запись активности в UserActivity.
     *
     * @param int $userId
     * @param string $pageUrl
     * @param string $pageTitle
     * @param string $ipAddress
     * @param string $userAgent
     * @return bool
     */
    public function logVisit(int $userId, string $pageUrl, string $pageTitle, string $ipAddress, string $userAgent): bool
    {
        // Обновляем last_visit и last_ip
        try {
            $stmt = $this->db->prepare(
                'UPDATE ' . db_table('users') . ' SET last_visit = NOW(), last_ip = :ip_address WHERE id = :id'
            );
            $stmt->execute([
                ':ip_address' => $ipAddress,
                ':id' => $userId,
            ]);
        } catch (\Exception $e) {
            // Поле может не существовать в старых версиях БД
        }

        // Записываем активность через UserActivity
        try {
            $activity = new UserActivity();
            $activity->logVisit($userId, $pageUrl, $pageTitle, $ipAddress, $userAgent);
        } catch (\Exception $e) {
            // Таблица может не существовать
        }

        return true;
    }

    /**
     * Сохранить секрет TOTP (двухфакторная аутентификация) и сбросить флаг включения.
     *
     * @param int $userId
     * @param string $secret
     * @return bool
     */
    public function setTotpSecret(int $userId, string $secret): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE ' . db_table('users') . ' SET totp_secret = :secret, totp_enabled = 0, updated_at = NOW() WHERE id = :id'
        );
        $stmt->execute(['secret' => $secret, 'id' => $userId]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Включить или выключить двухфакторную аутентификацию.
     *
     * @param int $userId
     * @param bool $enabled
     * @return bool
     */
    public function setTotpEnabled(int $userId, bool $enabled): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE ' . db_table('users') . ' SET totp_enabled = :enabled, updated_at = NOW() WHERE id = :id'
        );
        $stmt->execute([
            'enabled' => $enabled ? 1 : 0,
            'id' => $userId,
        ]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Проверить, является ли пользователь администратором.
     *
     * @param int $userId
     * @return bool
     */
    public function isAdmin(int $userId): bool
    {
        $user = $this->findDtoById($userId);
        return $user && $user->role === 'admin';
    }
}
