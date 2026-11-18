<?php
/**
 * Миграция RBAC: роли и права доступа к разделам админки.
 * Запуск: php database/migrations/run_010_roles.php
 */

require dirname(__DIR__, 2) . '/app/bootstrap.php';

use App\Core\Database;

$db = Database::connection();

echo "=== Создание таблиц ролей и прав ===\n";

$db->exec('CREATE TABLE IF NOT EXISTS roles (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    role_key VARCHAR(64) NOT NULL,
    name VARCHAR(120) NOT NULL,
    description VARCHAR(500) NOT NULL DEFAULT "",
    is_system TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_roles_key (role_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

$db->exec('CREATE TABLE IF NOT EXISTS role_permissions (
    role_id BIGINT UNSIGNED NOT NULL,
    page_slug VARCHAR(120) NOT NULL,
    PRIMARY KEY (role_id, page_slug),
    CONSTRAINT fk_role_permissions_role FOREIGN KEY (role_id) REFERENCES roles (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
echo "  Готово\n";

// Поле role должно позволять произвольные ключи новых ролей.
echo "=== Расширение users.role ===\n";
$column = $db->query("SHOW COLUMNS FROM users LIKE 'role'")->fetch();
if ($column && stripos((string) $column['Type'], 'enum') === 0) {
    $db->exec('ALTER TABLE users MODIFY role VARCHAR(64) NOT NULL DEFAULT "user"');
    echo "  ENUM → VARCHAR(64)\n";
} else {
    echo "  Уже VARCHAR, пропускаем\n";
}

// Стартовые роли с текущими наборами прав (как было в admin_can_access()).
$allPages = [
    'index', 'blog', 'categories', 'portfolio', 'landing-pages', 'menus', 'templates',
    'new-orders', 'current-orders', 'orders', 'calculator',
    'messages', 'consultant', 'users', 'settings', 'custom-fields', 'files',
    'profile', 'user-edit', 'article-edit', 'portfolio-edit', 'order-edit',
    'roles',
];

$adminPages = array_values(array_diff($allPages, ['roles']));
$editorPages = ['index', 'blog', 'categories', 'portfolio', 'files', 'profile', 'user-edit', 'article-edit', 'portfolio-edit'];
$managerPages = ['index', 'consultant', 'orders', 'current-orders', 'new-orders', 'calculator', 'profile', 'user-edit', 'order-edit'];

$roles = [
    ['admin', 'Администратор', 'Полный доступ к сайту и настройкам', 1, $adminPages],
    ['editor', 'Редактор', 'Управление контентом: статьи, рубрики, портфолио, файлы', 1, $editorPages],
    ['manager', 'Менеджер', 'Заказы, сообщения, консультант и калькулятор', 1, $managerPages],
    ['user', 'Пользователь', 'Без доступа к админке', 1, []],
];

echo "=== Заполнение стартовых ролей ===\n";
$findRole = $db->prepare('SELECT id FROM roles WHERE role_key = :key LIMIT 1');
$insertRole = $db->prepare('INSERT INTO roles (role_key, name, description, is_system) VALUES (:key, :name, :description, :is_system)');
$clearPerms = $db->prepare('DELETE FROM role_permissions WHERE role_id = :role_id');
$insertPerm = $db->prepare('INSERT IGNORE INTO role_permissions (role_id, page_slug) VALUES (:role_id, :page_slug)');

$db->beginTransaction();
try {
    foreach ($roles as [$key, $name, $description, $isSystem, $pages]) {
        $findRole->execute(['key' => $key]);
        $roleId = (int) $findRole->fetchColumn();
        if (!$roleId) {
            $insertRole->execute([
                'key' => $key,
                'name' => $name,
                'description' => $description,
                'is_system' => $isSystem,
            ]);
            $roleId = (int) $db->lastInsertId();
        }
        $clearPerms->execute(['role_id' => $roleId]);
        foreach ($pages as $page) {
            $insertPerm->execute(['role_id' => $roleId, 'page_slug' => $page]);
        }
        echo "  {$key}: " . count($pages) . " разделов\n";
    }
    $db->commit();
} catch (\Throwable $e) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    echo "Ошибка миграции: " . $e->getMessage() . "\n";
    exit(1);
}

echo "Миграция ролей завершена.\n";
