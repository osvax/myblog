<?php

declare(strict_types=1);

namespace App\DTO;

/**
 * UserDTO — объект переноса данных для пользователей (администраторы, редакторы, менеджеры).
 *
 * === ПОТОК ДАННЫХ (Data Flow) ===
 *
 * 1. ВХОД (Request → DTO):
 *    - POST /api/admin/users (создание пользователя админом)
 *      Тело: { "name": "Иван", "email": "ivan@test.ru", "password": "secret123", "role": "editor" }
 *    - PUT /api/admin/users/{id} (обновление профиля/роли/статуса)
 *      Тело: { "name": "Иван", "email": "ivan@test.ru", "role": "manager", "is_active": true }
 *    - POST /api/admin/users/{id}/avatar (загрузка аватара — multipart)
 *    - POST /api/admin/users/{id}/reset-password (сброс пароля админом)
 *      Тело: { "password": "newsecret123" }
 *    - Контроллер: UserDTO::fromRequest($request->json(), $id?)
 *
 * 2. ВАЛИДАЦИЯ (DTO → Errors):
 *    - $dto->validate() — для создания: name, email, password, role
 *    - $dto->validateUpdate() — для обновления: name, email, role, is_active
 *    - $dto->validatePassword() — для смены пароля: password (мин 8 символов)
 *    - Ошибки → $dto->getErrors()
 *
 * 3. СОХРАНЕНИЕ (DTO → Database):
 *    - Repository: saveUserFromDTO(UserDTO $dto) — INSERT/UPDATE va_users
 *    - Пароль хешируется перед сохранением (password_hash)
 *    - Аватар сохраняется отдельно, путь записывается в avatar_path
 *
 * 4. ЧТЕНИЕ (Database → DTO):
 *    - Repository: all(), findById(), findByEmail()
 *    - SELECT с JOIN на roles для получения role_name
 *    - UserDTO::fromDatabase($row) → DTO
 *
 * 5. ВЫХОД (DTO → JSON Response):
 *    - DTO::toApiArray() — для списка/отдельного пользователя в админке
 *    - DTO::toProfileArray() — для публичного профиля (ограниченные поля)
 *    - Контроллер возвращает json
 *
 * === СТРУКТУРА ТАБЛИЦЫ va_users ===
 * - id (INT, PK, AI)
 * - email (VARCHAR 255, UNIQUE) — email для входа
 * - password_hash (VARCHAR 255) — хеш пароля (bcrypt)
 * - name (VARCHAR 160) — отображаемое имя
 * - avatar_path (VARCHAR 500, NULL) — путь к аватару (/uploads/profile/xxx.jpg)
 * - role (VARCHAR 50) — роль: 'user'|'editor'|'manager'|'admin' (FK на va_roles.role_key)
 * - is_active (TINYINT) — 1 активен, 0 заблокирован
 * - created_at, updated_at (TIMESTAMP)
 * - last_visit, last_ip (TIMESTAMP, VARCHAR 45) — последняя активность
 * - phone, about, position, bio, timezone, language — профиль
 * - totp_secret (VARCHAR 32, NULL) — секрет для 2FA (TOTP)
 * - totp_enabled (TINYINT) — 1 если 2FA включено
 * - yandex_id (VARCHAR 100, NULL) — для OAuth Яндекс
 *
 * === РОЛИ (из va_roles) ===
 * - 'user' — обычный пользователь (фронтенд)
 * - 'editor' — может управлять контентом (статьи, проекты, категории)
 * - 'manager' — editor + заказы, сообщения, пользователи
 * - 'admin' — полный доступ (настройки, роли, логи, бэкапы)
 */
final class UserDTO extends AbstractDTO
{
    // === Основные поля (колонки va_users) ===

    /** @var int|null ID пользователя (NULL для нового) */
    public ?int $id = null;

    /** @var string Email (обязателен, уникален, валидный email) */
    public string $email = '';

    /** @var string Хеш пароля (устанавливается при создании/смене пароля) */
    public string $passwordHash = '';

    /** @var string Отображаемое имя (обязательно, 2..160) */
    public string $name = '';

    /** @var string Путь к аватару (опционально) */
    public string $avatarPath = '';

    /** @var string Роль: 'user'|'editor'|'manager'|'admin' */
    public string $role = 'user';

    /** @var bool Активен/заблокирован */
    public bool $isActive = true;

    /** @var string Дата создания (Y-m-d H:i:s) */
    public string $createdAt = '';

    /** @var string Дата обновления (Y-m-d H:i:s) */
    public string $updatedAt = '';

    /** @var string|null Последний визит (Y-m-d H:i:s) */
    public ?string $lastVisit = null;

    /** @var string|null Последний IP */
    public ?string $lastIp = null;

    // === Дополнительные поля профиля ===

    /** @var string Телефон */
    public string $phone = '';

    /** @var string О себе (кратко) */
    public string $about = '';

    /** @var string Должность */
    public string $position = '';

    /** @var string Биография (подробно) */
    public string $bio = '';

    /** @var string Часовой пояс (например: 'Europe/Moscow') */
    public string $timezone = 'Europe/Moscow';

    /** @var string Язык (например: 'ru') */
    public string $language = 'ru';

    // === 2FA (TOTP) ===

    /** @var string|null Секрет TOTP (base32) */
    public ?string $totpSecret = null;

    /** @var bool Включена ли 2FA */
    public bool $totpEnabled = false;

    // === OAuth ===

    /** @var string|null Яндекс ID для OAuth */
    public ?string $yandexId = null;

    // === Временные поля (не сохраняются в БД) ===

    /** @var string Открытый пароль (только для ввода при создании/смене) */
    public string $plainPassword = '';

    /** @var string Подтверждение пароля */
    public string $passwordConfirm = '';

    // === Константы ===
    public const ROLE_USER = 'user';
    public const ROLE_EDITOR = 'editor';
    public const ROLE_MANAGER = 'manager';
    public const ROLE_ADMIN = 'admin';
    public const ROLE_VALUES = [self::ROLE_USER, self::ROLE_EDITOR, self::ROLE_MANAGER, self::ROLE_ADMIN];
    public const NAME_MAX = 160;
    public const NAME_MIN = 2;
    public const PASSWORD_MIN = 8;
    public const AVATAR_MAX = 500;

    /**
     * Создаёт DTO из массива запроса (JSON / POST).
     *
     * ИСПОЛЬЗОВАНИЕ:
     * ```php
     * // Создание пользователя (POST /api/admin/users)
     * $jsonData = $request->json(); // {name, email, password, role}
     * $dto = UserDTO::fromRequest($jsonData);
     *
     * // Обновление пользователя (PUT /api/admin/users/{id})
     * $jsonData = $request->json(); // {name, email, role, is_active}
     * $dto = UserDTO::fromRequest($jsonData, $params['id']);
     *
     * // Смена пароля (POST /api/admin/users/{id}/reset-password)
     * $jsonData = $request->json(); // {password}
     * $dto = UserDTO::fromRequest($jsonData, $params['id']);
     * $dto->plainPassword = $jsonData['password'];
     * ```
     *
     * @param array $data Данные из $request->json()
     * @param int|null $id ID из URL (для PUT)
     * @return static
     */
    public static function fromRequest(array $data, ?int $id = null): static
    {
        $dto = new self();
        $dto->id = $id ?? (isset($data['id']) && is_numeric($data['id']) ? (int)$data['id'] : null);

        // Основные поля
        $dto->email = trim((string)($data['email'] ?? ''));
        $dto->name = trim((string)($data['name'] ?? ''));
        $dto->role = trim((string)($data['role'] ?? 'user'));

        // is_active
        if (isset($data['is_active'])) {
            $dto->isActive = filter_var($data['is_active'], FILTER_VALIDATE_BOOLEAN);
        } elseif (isset($data['isActive'])) {
            $dto->isActive = filter_var($data['isActive'], FILTER_VALIDATE_BOOLEAN);
        }

        // Пароль (plaintext — только для валидации, хешируется в репозитории)
        $dto->plainPassword = (string)($data['password'] ?? '');
        $dto->passwordConfirm = (string)($data['password_confirm'] ?? $data['passwordConfirm'] ?? '');

        // Профиль
        $dto->phone = trim((string)($data['phone'] ?? ''));
        $dto->about = trim((string)($data['about'] ?? ''));
        $dto->position = trim((string)($data['position'] ?? ''));
        $dto->bio = trim((string)($data['bio'] ?? ''));
        $dto->timezone = trim((string)($data['timezone'] ?? 'Europe/Moscow'));
        $dto->language = trim((string)($data['language'] ?? 'ru'));

        // Аватар (путь приходит отдельно после загрузки файла)
        if (isset($data['avatar_path'])) {
            $dto->avatarPath = trim((string)$data['avatar_path']);
        } elseif (isset($data['avatarPath'])) {
            $dto->avatarPath = trim((string)$data['avatarPath']);
        }

        return $dto;
    }

    /**
     * Создаёт DTO из строки БД (результат SELECT * FROM va_users [JOIN va_roles]).
     *
     * @param array $row Ассоциативный массив из БД
     * @return static
     */
    public static function fromDatabase(array $row): static
    {
        $dto = new self();
        $dto->id = isset($row['id']) ? (int)$row['id'] : null;
        $dto->email = $row['email'] ?? '';
        $dto->passwordHash = $row['password_hash'] ?? '';
        $dto->name = $row['name'] ?? '';
        $dto->avatarPath = $row['avatar_path'] ?? '';
        $dto->role = $row['role'] ?? 'user';
        $dto->isActive = (bool)($row['is_active'] ?? true);
        $dto->createdAt = $row['created_at'] ?? '';
        $dto->updatedAt = $row['updated_at'] ?? '';
        $dto->lastVisit = $row['last_visit'] ?? null;
        $dto->lastIp = $row['last_ip'] ?? null;
        $dto->phone = $row['phone'] ?? '';
        $dto->about = $row['about'] ?? '';
        $dto->position = $row['position'] ?? '';
        $dto->bio = $row['bio'] ?? '';
        $dto->timezone = $row['timezone'] ?? 'Europe/Moscow';
        $dto->language = $row['language'] ?? 'ru';
        $dto->totpSecret = $row['totp_secret'] ?? null;
        $dto->totpEnabled = (bool)($row['totp_enabled'] ?? false);
        $dto->yandexId = $row['yandex_id'] ?? null;

        return $dto;
    }

    /**
     * Валидация для СОЗДАНИЯ пользователя (POST).
     * Требует: name, email, password, role.
     *
     * @return bool
     */
    public function validate(): bool
    {
        $this->clearErrors();

        // --- name ---
        $this->validateStringLength($this->name, 'name', self::NAME_MIN, self::NAME_MAX, 'Имя');

        // --- email ---
        $this->validateEmail($this->email, 'email', 255);

        // --- password (plainPassword) ---
        if ($this->plainPassword === '') {
            $this->addError('password', 'Пароль обязателен');
        } elseif (mb_strlen($this->plainPassword) < self::PASSWORD_MIN) {
            $this->addError('password', 'Пароль не короче ' . self::PASSWORD_MIN . ' символов');
        }

        // --- passwordConfirm ---
        if ($this->plainPassword !== $this->passwordConfirm) {
            $this->addError('passwordConfirm', 'Пароли не совпадают');
        }

        // --- role ---
        $this->validateEnum($this->role, 'role', self::ROLE_VALUES, 'роль');

        // --- avatarPath ---
        if ($this->avatarPath !== '' && mb_strlen($this->avatarPath) > self::AVATAR_MAX) {
            $this->addError('avatarPath', 'Путь к аватару слишком длинный');
        }

        return empty($this->errors);
    }

    /**
     * Валидация для ОБНОВЛЕНИЯ пользователя (PUT).
     * Требует: name, email, role, is_active.
     * Пароль НЕ обязателен (меняется отдельным эндпоинтом).
     *
     * @return bool
     */
    public function validateUpdate(): bool
    {
        $this->clearErrors();

        // --- name ---
        $this->validateStringLength($this->name, 'name', self::NAME_MIN, self::NAME_MAX, 'Имя');

        // --- email ---
        $this->validateEmail($this->email, 'email', 255);

        // --- role ---
        $this->validateEnum($this->role, 'role', self::ROLE_VALUES, 'роль');

        // --- avatarPath ---
        if ($this->avatarPath !== '' && mb_strlen($this->avatarPath) > self::AVATAR_MAX) {
            $this->addError('avatarPath', 'Путь к аватару слишком длинный');
        }

        // Профиль — опционально, без строгой валидации
        if (mb_strlen($this->phone) > 50) {
            $this->addError('phone', 'Телефон не длиннее 50 символов');
        }
        if (mb_strlen($this->timezone) > 50) {
            $this->addError('timezone', 'Неверный часовой пояс');
        }
        if (mb_strlen($this->language) > 10) {
            $this->addError('language', 'Неверный код языка');
        }

        return empty($this->errors);
    }

    /**
     * Валидация для СМЕНЫ ПАРОЛЯ.
     * Требует: plainPassword (мин 8 символов), passwordConfirm (совпадает).
     *
     * @return bool
     */
    public function validatePassword(): bool
    {
        $this->clearErrors();

        if ($this->plainPassword === '') {
            $this->addError('password', 'Пароль обязателен');
        } elseif (mb_strlen($this->plainPassword) < self::PASSWORD_MIN) {
            $this->addError('password', 'Пароль не короче ' . self::PASSWORD_MIN . ' символов');
        }

        if ($this->plainPassword !== $this->passwordConfirm) {
            $this->addError('passwordConfirm', 'Пароли не совпадают');
        }

        return empty($this->errors);
    }

    /**
     * Хеширует пароль и сохраняет в passwordHash.
     * Вызывается в репозитории перед сохранением.
     *
     * @return void
     */
    public function hashPassword(): void
    {
        if ($this->plainPassword !== '') {
            $this->passwordHash = password_hash($this->plainPassword, PASSWORD_DEFAULT);
            $this->plainPassword = '';
            $this->passwordConfirm = '';
        }
    }

    /**
     * Преобразует в массив для сохранения в БД (таблица va_users).
     * Пароль уже должен быть захеширован (вызван hashPassword()).
     *
     * @return array ['column' => value, ...]
     */
    public function toArray(): array
    {
        $data = [
            'id' => $this->id,
            'email' => $this->email,
            'password_hash' => $this->passwordHash,
            'name' => $this->name,
            'avatar_path' => $this->avatarPath ?: null,
            'role' => $this->role,
            'is_active' => $this->isActive ? 1 : 0,
            'phone' => $this->phone ?: null,
            'about' => $this->about ?: null,
            'position' => $this->position ?: null,
            'bio' => $this->bio ?: null,
            'timezone' => $this->timezone,
            'language' => $this->language,
            'totp_secret' => $this->totpSecret,
            'totp_enabled' => $this->totpEnabled ? 1 : 0,
            'yandex_id' => $this->yandexId,
        ];

        // Убираем NULL значения для INSERT (пусть сработают DEFAULT)
        if ($this->id === null) {
            unset($data['id']);
        }

        return $data;
    }

    /**
     * Преобразует в массив для JSON API ответа (админка: список/детали).
     *
     * СТРУКТУРА ОТВЕТА:
     * [
     *   'id' => 1,
     *   'email' => 'ivan@test.ru',
     *   'name' => 'Иван',
     *   'avatar_path' => '/uploads/profile/abc.jpg',
     *   'role' => 'editor',
     *   'role_name' => 'Редактор',  // из JOIN с va_roles
     *   'is_active' => true,
     *   'created_at' => '2025-01-15 10:30:00',
     *   'updated_at' => '2025-01-15 10:30:00',
     *   'last_visit' => '2025-01-20 09:15:00',
     * ]
     *
     * @param string|null $roleName Название роли из JOIN (опционально)
     * @return array
     */
    public function toApiArray(?string $roleName = null): array
    {
        return [
            'id' => $this->id,
            'email' => $this->email,
            'name' => $this->name,
            'avatar_path' => $this->avatarPath,
            'role' => $this->role,
            'role_name' => $roleName ?? $this->role,
            'is_active' => $this->isActive,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
            'last_visit' => $this->lastVisit,
            'last_ip' => $this->lastIp,
        ];
    }

    /**
     * Преобразует в массив для публичного профиля (ограниченные поля).
     *
     * @return array
     */
    public function toProfileArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'avatar_path' => $this->avatarPath,
            'about' => $this->about,
            'position' => $this->position,
            'bio' => $this->bio,
            'created_at' => $this->createdAt,
        ];
    }

    /**
     * Преобразует в массив для сессии (при логине).
     * Не включает password_hash, totp_secret.
     *
     * @return array
     */
    public function toSessionArray(): array
    {
        return [
            'id' => $this->id,
            'email' => $this->email,
            'name' => $this->name,
            'avatar_path' => $this->avatarPath,
            'role' => $this->role,
            'is_active' => $this->isActive,
            'totp_enabled' => $this->totpEnabled,
            'timezone' => $this->timezone,
            'language' => $this->language,
        ];
    }
}
