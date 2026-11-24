<?php

declare(strict_types=1);

namespace App\DTO;

/**
 * RoleDTO — объект переноса данных для ролей админки.
 *
 * === ПОТОК ДАННЫХ (Data Flow) ===
 *
 * 1. ВХОД (Request → DTO):
 *    - POST /api/admin/roles (создание роли)
 *      Тело: { "role_key": "editor", "name": "Редактор", "description": "Управляет контентом", "permissions": ["blog", "pages"] }
 *    - PUT /api/admin/roles/{id} (обновление роли)
 *      Тело: { "name": "Редактор", "description": "...", "permissions": ["blog"] }
 *    - Контроллер: RoleDTO::fromRequest($request->json(), $id?)
 *
 * 2. ВАЛИДАЦИЯ (DTO → Errors):
 *    - $dto->validate() — для создания: role_key, name
 *    - $dto->validateUpdate() — для обновления: name
 *    - Ошибки → $dto->getErrors()
 *
 * 3. СОХРАНЕНИЕ (DTO → Database):
 *    - Repository: saveRoleFromDTO(RoleDTO $dto) — INSERT/UPDATE va_roles + va_role_permissions
 *
 * 4. ЧТЕНИЕ (Database → DTO):
 *    - Repository: all(), findById(), findByKey()
 *    - RoleDTO::fromDatabase($row) → DTO (с permissions)
 *
 * 5. ВЫХОД (DTO → JSON Response):
 *    - DTO::toApiArray() — для списка/отдельной роли в админке
 *
 * === СТРУКТУРА ТАБЛИЦ ===
 * va_roles:
 * - id (INT, PK, AI)
 * - role_key (VARCHAR 64, UNIQUE) — ключ роли: 'user'|'editor'|'manager'|'admin'|custom
 * - name (VARCHAR 160) — отображаемое название
 * - description (TEXT) — описание
 * - is_system (TINYINT) — 1 системная (не удаляется), 0 пользовательская
 * - created_at, updated_at (TIMESTAMP)
 *
 * va_role_permissions:
 * - role_id (INT, FK) + page_slug (VARCHAR 100) — PK составной
 * - page_slug — слаг страницы админки (из config/admin-navigation.php)
 */
final class RoleDTO extends AbstractDTO
{
    // === Основные поля (колонки va_roles) ===

    /** @var int|null ID роли (NULL для новой) */
    public ?int $id = null;

    /** @var string Ключ роли (обязателен, уникален, латиница/цифры/_-) */
    public string $roleKey = '';

    /** @var string Отображаемое название (обязательно, 2..160) */
    public string $name = '';

    /** @var string Описание роли */
    public string $description = '';

    /** @var bool Системная роль (не удаляется) */
    public bool $isSystem = false;

    /** @var string|null Дата создания */
    public ?string $createdAt = null;

    /** @var string|null Дата обновления */
    public ?string $updatedAt = null;

    // === Связанные данные ===

    /** @var array<string> Разрешения (слаги страниц из admin-navigation.php) */
    public array $permissions = [];

    /** @var int Количество пользователей с этой ролью */
    public int $usersCount = 0;

    /**
     * Создаёт DTO из строки БД (va_roles + permissions + users_count).
     *
     * @param array<string, mixed> $row
     * @return static
     */
    public static function fromDatabase(array $row): static
    {
        $dto = new self();
        $dto->id = (int) ($row['id'] ?? 0);
        $dto->roleKey = (string) ($row['role_key'] ?? '');
        $dto->name = (string) ($row['name'] ?? '');
        $dto->description = (string) ($row['description'] ?? '');
        $dto->isSystem = (bool) ($row['is_system'] ?? false);
        $dto->createdAt = $row['created_at'] ?? null;
        $dto->updatedAt = $row['updated_at'] ?? null;

        // permissions может прийти как JSON или массив
        if (isset($row['permissions'])) {
            $perms = $row['permissions'];
            if (is_string($perms)) {
                $decoded = json_decode($perms, true);
                $dto->permissions = is_array($decoded) ? $decoded : [];
            } elseif (is_array($perms)) {
                $dto->permissions = $perms;
            }
        }

        $dto->usersCount = (int) ($row['users_count'] ?? 0);

        return $dto;
    }

    /**
     * Создаёт DTO из запроса (JSON).
     *
     * @param array<string, mixed> $data
     * @param int|null $id ID роли при обновлении
     * @return static
     */
    public static function fromRequest(array $data, ?int $id = null): static
    {
        $dto = new self();
        $dto->id = $id;
        $dto->roleKey = trim((string) ($data['role_key'] ?? $data['roleKey'] ?? ''));
        $dto->name = trim((string) ($data['name'] ?? ''));
        $dto->description = trim((string) ($data['description'] ?? ''));
        $dto->isSystem = (bool) ($data['is_system'] ?? $data['isSystem'] ?? false);

        $perms = $data['permissions'] ?? [];
        if (is_array($perms)) {
            $dto->permissions = array_values(array_unique(array_map('strval', $perms)));
        } elseif (is_string($perms)) {
            $decoded = json_decode($perms, true);
            $dto->permissions = is_array($decoded) ? $decoded : [];
        }

        return $dto;
    }

    /** Валидация для создания роли. */
    public function validate(): bool
    {
        $this->clearErrors();

        if ($this->roleKey === '') {
            $this->addError('role_key', 'Ключ роли обязателен');
        } elseif (!preg_match('/^[a-z0-9][a-z0-9_-]{1,62}$/', $this->roleKey)) {
            $this->addError('role_key', 'Ключ роли: латинские буквы, цифры, дефис и подчёркивание (от 2 символов)');
        }

        if ($this->name === '') {
            $this->addError('name', 'Название роли обязательно');
        } elseif (mb_strlen($this->name) > 160) {
            $this->addError('name', 'Название роли не более 160 символов');
        }

        return empty($this->errors);
    }

    /** Валидация для обновления роли (role_key не меняется). */
    public function validateUpdate(): bool
    {
        $this->clearErrors();

        if ($this->name === '') {
            $this->addError('name', 'Название роли обязательно');
        } elseif (mb_strlen($this->name) > 160) {
            $this->addError('name', 'Название роли не более 160 символов');
        }

        return empty($this->errors);
    }

    /**
     * Преобразует в массив для сохранения в БД (va_roles).
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $data = [
            'role_key' => $this->roleKey,
            'name' => $this->name,
            'description' => $this->description,
            'is_system' => $this->isSystem ? 1 : 0,
        ];

        if ($this->id !== null) {
            $data['id'] = $this->id;
        }

        return $data;
    }

    /**
     * Массив для API-ответа (список/детали роли).
     *
     * @return array<string, mixed>
     */
    public function toApiArray(): array
    {
        return [
            'id' => $this->id,
            'role_key' => $this->roleKey,
            'name' => $this->name,
            'description' => $this->description,
            'is_system' => $this->isSystem,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
            'permissions' => $this->permissions,
            'users_count' => $this->usersCount,
        ];
    }
}
