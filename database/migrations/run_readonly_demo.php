<?php

require dirname(__DIR__, 2) . '/app/bootstrap.php';

$db = \App\Core\Database::connection();
$roleKey = 'readonly_demo';
$email = 'demo@user.test';
$name = 'Демонстрационный пользователь';

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
$pages = array_values(array_unique(array_merge($pages, [
    'profile', 'user-edit', 'article-edit', 'portfolio-edit', 'order-edit',
])));

$password = '';
if (defined('STDIN')) {
    fwrite(STDOUT, "Пароль для {$email}: ");
    $password = trim((string) fgets(STDIN));
}
if (strlen($password) < 8) {
    fwrite(STDERR, "Пароль должен содержать минимум 8 символов.\n");
    exit(1);
}

$db->beginTransaction();
try {
    $roleQuery = $db->prepare('SELECT id FROM ' . db_table('roles') . ' WHERE role_key = :key LIMIT 1');
    $roleQuery->execute(['key' => $roleKey]);
    $roleId = (int) $roleQuery->fetchColumn();

    if ($roleId > 0) {
        $roleUpdate = $db->prepare(
            'UPDATE ' . db_table('roles') . '
             SET name = :name, description = :description, is_system = 1
             WHERE id = :id'
        );
        $roleUpdate->execute([
            'name' => 'Демонстрационный просмотр',
            'description' => 'Полный просмотр админ-панели без сохранения, создания и удаления данных',
            'id' => $roleId,
        ]);
    } else {
        $roleInsert = $db->prepare(
            'INSERT INTO ' . db_table('roles') . ' (role_key, name, description, is_system)
             VALUES (:key, :name, :description, 1)'
        );
        $roleInsert->execute([
            'key' => $roleKey,
            'name' => 'Демонстрационный просмотр',
            'description' => 'Полный просмотр админ-панели без сохранения, создания и удаления данных',
        ]);
        $roleId = (int) $db->lastInsertId();
    }

    $clearPermissions = $db->prepare('DELETE FROM ' . db_table('role_permissions') . ' WHERE role_id = :role_id');
    $clearPermissions->execute(['role_id' => $roleId]);
    $permissionInsert = $db->prepare(
        'INSERT INTO ' . db_table('role_permissions') . ' (role_id, page_slug) VALUES (:role_id, :page_slug)'
    );
    foreach ($pages as $page) {
        $permissionInsert->execute(['role_id' => $roleId, 'page_slug' => $page]);
    }

    $userQuery = $db->prepare('SELECT id FROM ' . db_table('users') . ' WHERE LOWER(email) = LOWER(:email) LIMIT 1');
    $userQuery->execute(['email' => $email]);
    $userId = (int) $userQuery->fetchColumn();
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    if ($userId > 0) {
        $userUpdate = $db->prepare(
            'UPDATE ' . db_table('users') . '
             SET password_hash = :password_hash, name = :name, role = :role, is_active = 1, updated_at = NOW()
             WHERE id = :id'
        );
        $userUpdate->execute([
            'password_hash' => $passwordHash,
            'name' => $name,
            'role' => $roleKey,
            'id' => $userId,
        ]);
        $userAction = 'обновлён';
    } else {
        $userInsert = $db->prepare(
            'INSERT INTO ' . db_table('users') . ' (email, password_hash, name, role, is_active, created_at, updated_at)
             VALUES (:email, :password_hash, :name, :role, 1, NOW(), NOW())'
        );
        $userInsert->execute([
            'email' => $email,
            'password_hash' => $passwordHash,
            'name' => $name,
            'role' => $roleKey,
        ]);
        $userAction = 'создан';
    }

    $db->commit();
    fwrite(STDOUT, "Роль {$roleKey} настроена: " . count($pages) . " разделов.\n");
    fwrite(STDOUT, "Пользователь {$email} {$userAction}.\n");
} catch (Throwable $error) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    fwrite(STDERR, "Ошибка: {$error->getMessage()}\n");
    exit(1);
}
