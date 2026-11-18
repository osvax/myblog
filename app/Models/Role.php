<?php

namespace App\Models;

use App\Core\Database;

/**
 * Роли админки и их права на разделы.
 * Ключ роли хранится в users.role и в сессии администратора.
 */
class Role
{
    /** Все роли с числом пользователей. */
    public function all(): array
    {
        $db = Database::connection();
        return $db->query(
            'SELECT r.id, r.role_key, r.name, r.description, r.is_system,
                    (SELECT COUNT(*) FROM users u WHERE u.role = r.role_key) AS users_count
             FROM roles r ORDER BY r.is_system DESC, r.id ASC'
        )->fetchAll();
    }

    public function byId(int $id): ?array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM roles WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function byKey(string $key): ?array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM roles WHERE role_key = :key LIMIT 1');
        $stmt->execute(['key' => $key]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function exists(string $key): bool
    {
        return $this->byKey($key) !== null;
    }

    /** Разрешённые страницы роли. */
    public function permissions(int $roleId): array
    {
        $stmt = Database::connection()->prepare('SELECT page_slug FROM role_permissions WHERE role_id = :id ORDER BY page_slug');
        $stmt->execute(['id' => $roleId]);
        return array_column($stmt->fetchAll(), 'page_slug');
    }

    /** Быстрая проверка прав по ключу роли (для сессии). */
    public function permissionsForKey(string $key): array
    {
        $role = $this->byKey($key);
        return $role ? $this->permissions((int) $role['id']) : [];
    }

    public function create(string $key, string $name, string $description): array
    {
        if (!preg_match('/^[a-z0-9][a-z0-9_-]{1,62}$/', $key)) {
            return ['success' => false, 'error' => 'Ключ роли: латинские буквы, цифры, дефис и подчёркивание (от 2 символов)'];
        }
        if (trim($name) === '') {
            return ['success' => false, 'error' => 'Укажите название роли'];
        }
        if ($this->exists($key)) {
            return ['success' => false, 'error' => 'Роль с таким ключом уже существует'];
        }

        $stmt = Database::connection()->prepare(
            'INSERT INTO roles (role_key, name, description, is_system) VALUES (:key, :name, :description, 0)'
        );
        $stmt->execute([
            'key' => $key,
            'name' => trim($name),
            'description' => trim($description),
        ]);
        return ['success' => true, 'id' => (int) Database::connection()->lastInsertId()];
    }

    public function update(int $id, string $name, string $description, ?array $permissions = null): array
    {
        $role = $this->byId($id);
        if (!$role) {
            return ['success' => false, 'error' => 'Роль не найдена'];
        }
        if (trim($name) === '') {
            return ['success' => false, 'error' => 'Укажите название роли'];
        }

        $db = Database::connection();
        $db->beginTransaction();
        try {
            $stmt = $db->prepare('UPDATE roles SET name = :name, description = :description WHERE id = :id');
            $stmt->execute([
                'name' => trim($name),
                'description' => trim($description),
                'id' => $id,
            ]);
            if ($permissions !== null) {
                $known = $this->knownPages();
                $stmt = $db->prepare('DELETE FROM role_permissions WHERE role_id = :id');
                $stmt->execute(['id' => $id]);
                $stmt = $db->prepare('INSERT IGNORE INTO role_permissions (role_id, page_slug) VALUES (:id, :page)');
                foreach (array_unique($permissions) as $page) {
                    if (!is_string($page) || !in_array($page, $known, true)) {
                        continue;
                    }
                    $stmt->execute(['id' => $id, 'page' => $page]);
                }
            }
            $db->commit();
            return ['success' => true];
        } catch (\Throwable $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function delete(int $id): array
    {
        $role = $this->byId($id);
        if (!$role) {
            return ['success' => false, 'error' => 'Роль не найдена'];
        }
        if (!empty($role['is_system'])) {
            return ['success' => false, 'error' => 'Стартовые роли удалять нельзя'];
        }
        $stmt = Database::connection()->prepare('SELECT COUNT(*) FROM users WHERE role = :key');
        $stmt->execute(['key' => $role['role_key']]);
        if ((int) $stmt->fetchColumn() > 0) {
            return ['success' => false, 'error' => 'Роль назначена пользователям — сначала смените им роль'];
        }
        $stmt = Database::connection()->prepare('DELETE FROM roles WHERE id = :id');
        $stmt->execute(['id' => $id]);
        return ['success' => true];
    }

    /** Все известные страницы админки (из конфигурации навигации). */
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
}
