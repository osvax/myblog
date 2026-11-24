<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\QueryBuilder;
use App\DTO\RoleDTO;
use PDO;

/**
 * Роли админки и их права на разделы.
 * Ключ роли хранится в users.role и в сессии администратора.
 *
 * === МИГРАЦИЯ ===
 * - Конструктор принимает PDO (для тестов — SQLite :memory:)
 * - QueryBuilder для простых SELECT/INSERT/UPDATE/DELETE
 * - Raw PDO для сложных запросов с подзапросами
 * - Возвращает RoleDTO вместо массивов
 * - Статические методы-обёртки для обратной совместимости
 */
final class Role
{
    private PDO $db;
    private string $rolesTable;
    private string $permsTable;

    /**
     * @param PDO|null $db PDO соединение (по умолчанию Database::connection())
     */
    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? \App\Core\Database::connection();
        $this->rolesTable = db_table('roles');
        $this->permsTable = db_table('role_permissions');
    }

    // ===================== ОБРАТНАЯ СОВМЕСТИМОСТЬ (статические) =====================

    /** @return array<int, array<string, mixed>> Все роли с числом пользователей (старый API) */
    public static function all(): array
    {
        return array_map(fn (RoleDTO $dto) => $dto->toApiArray(), (new self())->allNew());
    }

    /** @return array<string, mixed>|null Роль по ID (старый API) */
    public static function byId(int $id): ?array
    {
        return (new self())->byIdNew($id)?->toApiArray();
    }

    /** @return array<string, mixed>|null Роль по ключу (старый API) */
    public static function byKey(string $key): ?array
    {
        return (new self())->byKeyNew($key)?->toApiArray();
    }

    /** @return array<int, string> Разрешения роли (старый API) */
    public static function permissions(int $roleId): array
    {
        return (new self())->permissionsNew($roleId);
    }

    /** @return array<int, string> Разрешения по ключу роли (старый API) */
    public static function permissionsForKey(string $key): array
    {
        return (new self())->permissionsForKeyNew($key);
    }

    /** @return array<string, mixed> Создание роли (старый API) */
    public static function create(string $key, string $name, string $description): array
    {
        $dto = new RoleDTO();
        $dto->roleKey = $key;
        $dto->name = $name;
        $dto->description = $description;
        return (new self())->createNew($dto);
    }

    /** @return array<string, mixed> Обновление роли (старый API) */
    public static function update(int $id, string $name, string $description, ?array $permissions = null): array
    {
        $dto = new RoleDTO();
        $dto->id = $id;
        $dto->name = $name;
        $dto->description = $description;
        $dto->permissions = $permissions ?? [];
        return (new self())->updateNew($dto);
    }

    /** @return array<string, mixed> Удаление роли (старый API) */
    public static function delete(int $id): array
    {
        return (new self())->deleteNew($id);
    }

    // ===================== НОВЫЙ API (DTO-based) =====================

    /** @return RoleDTO[] Все роли с permissions и users_count */
    public function allNew(): array
    {
        $usersTable = db_table('users');
        $sql = <<<SQL
            SELECT r.*,
                   rp.permissions,
                   COALESCE(u.users_count, 0) AS users_count
            FROM {$this->rolesTable} r
            LEFT JOIN (
                SELECT role_id, JSON_ARRAYAGG(page_slug) AS permissions
                FROM {$this->permsTable}
                GROUP BY role_id
            ) rp ON rp.role_id = r.id
            LEFT JOIN (
                SELECT role AS role_key, COUNT(*) AS users_count
                FROM {$usersTable}
                GROUP BY role
            ) u ON u.role_key = r.role_key
            ORDER BY r.is_system DESC, r.id ASC
        SQL;

        $rows = $this->db->query($sql)->fetchAll();
        return array_map([RoleDTO::class, 'fromDatabase'], $rows);
    }

    /** @return RoleDTO|null Роль по ID с permissions */
    public function byIdNew(int $id): ?RoleDTO
    {
        $usersTable = db_table('users');
        $sql = <<<SQL
            SELECT r.*,
                   rp.permissions,
                   COALESCE(u.users_count, 0) AS users_count
            FROM {$this->rolesTable} r
            LEFT JOIN (
                SELECT role_id, JSON_ARRAYAGG(page_slug) AS permissions
                FROM {$this->permsTable}
                GROUP BY role_id
            ) rp ON rp.role_id = r.id
            LEFT JOIN (
                SELECT role AS role_key, COUNT(*) AS users_count
                FROM {$usersTable}
                GROUP BY role
            ) u ON u.role_key = r.role_key
            WHERE r.id = :id
        SQL;

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        return $row ? RoleDTO::fromDatabase($row) : null;
    }

    /** @return RoleDTO|null Роль по ключу с permissions */
    public function byKeyNew(string $key): ?RoleDTO
    {
        $usersTable = db_table('users');
        $sql = <<<SQL
            SELECT r.*,
                   rp.permissions,
                   COALESCE(u.users_count, 0) AS users_count
            FROM {$this->rolesTable} r
            LEFT JOIN (
                SELECT role_id, JSON_ARRAYAGG(page_slug) AS permissions
                FROM {$this->permsTable}
                GROUP BY role_id
            ) rp ON rp.role_id = r.id
            LEFT JOIN (
                SELECT role AS role_key, COUNT(*) AS users_count
                FROM {$usersTable}
                GROUP BY role
            ) u ON u.role_key = r.role_key
            WHERE r.role_key = :key
        SQL;

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['key' => $key]);
        $row = $stmt->fetch();

        return $row ? RoleDTO::fromDatabase($row) : null;
    }

    /** @return array<int, string> Разрешения роли (слаги страниц) */
    public function permissionsNew(int $roleId): array
    {
        $rows = (new QueryBuilder($this->db, $this->permsTable))
            ->select(['page_slug'])
            ->where('role_id', '=', $roleId)
            ->orderBy('page_slug', 'ASC')
            ->get();

        return array_column($rows, 'page_slug');
    }

    /** @return array<int, string> Разрешения по ключу роли */
    public function permissionsForKeyNew(string $key): array
    {
        $role = $this->byKeyNew($key);
        return $role ? $this->permissionsNew($role->id) : [];
    }

    /** @return array<string, mixed> Создание роли из DTO */
    public function createNew(RoleDTO $dto): array
    {
        if (!$dto->validate()) {
            return ['success' => false, 'error' => implode('; ', $dto->getErrors())];
        }

        // Проверка уникальности role_key
        if ($this->byKeyNew($dto->roleKey) !== null) {
            return ['success' => false, 'error' => 'Роль с таким ключом уже существует'];
        }

        try {
            $data = $dto->toArray();
            unset($data['id']);

            $columns = implode(',', array_keys($data));
            $placeholders = ':' . implode(',:', array_keys($data));
            $sql = "INSERT INTO {$this->rolesTable} ($columns) VALUES ($placeholders)";

            $stmt = $this->db->prepare($sql);
            $stmt->execute($data);

            $dto->id = (int) $this->db->lastInsertId();

            // Сохраняем permissions
            if (!empty($dto->permissions)) {
                $this->savePermissions($dto->id, $dto->permissions);
            }

            return ['success' => true, 'id' => $dto->id];
        } catch (\Throwable $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /** @return array<string, mixed> Обновление роли из DTO */
    public function updateNew(RoleDTO $dto): array
    {
        if ($dto->id === null) {
            return ['success' => false, 'error' => 'ID роли не указан'];
        }
        if (!$dto->validateUpdate()) {
            return ['success' => false, 'error' => implode('; ', $dto->getErrors())];
        }

        $existing = $this->byIdNew($dto->id);
        if ($existing === null) {
            return ['success' => false, 'error' => 'Роль не найдена'];
        }

        try {
            $this->db->beginTransaction();

            // Обновляем роль
            $data = [
                'name' => $dto->name,
                'description' => $dto->description,
                'id' => $dto->id,
            ];
            $stmt = $this->db->prepare("UPDATE {$this->rolesTable} SET name = :name, description = :description WHERE id = :id");
            $stmt->execute($data);

            // Обновляем permissions если переданы
            if ($dto->permissions !== null) {
                $stmt = $this->db->prepare("DELETE FROM {$this->permsTable} WHERE role_id = :id");
                $stmt->execute(['id' => $dto->id]);

                if (!empty($dto->permissions)) {
                    $this->savePermissions($dto->id, $dto->permissions);
                }
            }

            $this->db->commit();
            return ['success' => true];
        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /** @return array<string, mixed> Удаление роли */
    public function deleteNew(int $id): array
    {
        $role = $this->byIdNew($id);
        if ($role === null) {
            return ['success' => false, 'error' => 'Роль не найдена'];
        }
        if ($role->isSystem) {
            return ['success' => false, 'error' => 'Стартовые роли удалять нельзя'];
        }

        // Проверка пользователей с этой ролью
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM ' . db_table('users') . ' WHERE role = :key');
        $stmt->execute(['key' => $role->roleKey]);
        if ((int) $stmt->fetchColumn() > 0) {
            return ['success' => false, 'error' => 'Роль назначена пользователям — сначала смените им роль'];
        }

        try {
            $this->db->beginTransaction();

            $stmt = $this->db->prepare("DELETE FROM {$this->permsTable} WHERE role_id = :id");
            $stmt->execute(['id' => $id]);

            $stmt = $this->db->prepare("DELETE FROM {$this->rolesTable} WHERE id = :id");
            $stmt->execute(['id' => $id]);

            $this->db->commit();
            return ['success' => true];
        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /** Известные страницы админки (из конфигурации навигации). */
    public function knownPages(): array
    {
        $navigation = require APP_ROOT . '/config/admin-navigation.php';
        $pages = [];
        foreach ($navigation as $entry) {
            if (($entry['type'] ?? 'link') === 'group') {
                foreach ($entry['items'] ?? [] as $item) {
                    if (!empty($item['slug'])) {
                        $pages[] = $item['slug'];
                    }
                }
            } elseif (!empty($entry['slug'])) {
                $pages[] = $entry['slug'];
            }
        }
        return array_values(array_unique($pages));
    }

    // ===================== PRIVATE HELPERS =====================

    /** Сохраняет permissions для роли. */
    private function savePermissions(int $roleId, array $permissions): void
    {
        $known = $this->knownPages();
        $stmt = $this->db->prepare("INSERT IGNORE INTO {$this->permsTable} (role_id, page_slug) VALUES (:id, :page)");
        foreach (array_unique($permissions) as $page) {
            if (!is_string($page) || !in_array($page, $known, true)) {
                continue;
            }
            $stmt->execute(['id' => $roleId, 'page' => $page]);
        }
    }
}
