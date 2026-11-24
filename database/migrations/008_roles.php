<?php

/**
 * Migration: roles
 * Created: 2025-11-24 00:00:00
 * Description: Таблицы ролей и прав доступа (RBAC) с сид-данными
 * Применяется ПОСЛЕ 002_users (users таблица должна существовать)
 */

return [
    'up' => function (\PDO $pdo): void {
        // Таблица ролей
        $pdo->exec(<<<'SQL'
            CREATE TABLE IF NOT EXISTS `va_roles` (
                `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                `role_key` VARCHAR(64) NOT NULL,
                `name` VARCHAR(120) NOT NULL,
                `description` VARCHAR(500) NOT NULL DEFAULT "",
                `is_system` TINYINT(1) NOT NULL DEFAULT 0,
                `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                UNIQUE KEY `uq_roles_key` (`role_key`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        SQL);

        // Таблица прав доступа
        $pdo->exec(<<<'SQL'
            CREATE TABLE IF NOT EXISTS `va_role_permissions` (
                `role_id` BIGINT UNSIGNED NOT NULL,
                `page_slug` VARCHAR(120) NOT NULL,
                PRIMARY KEY (`role_id`, `page_slug`),
                CONSTRAINT `fk_role_permissions_role` FOREIGN KEY (`role_id`) REFERENCES `va_roles` (`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        SQL);

        // Расширяем users.role с ENUM на VARCHAR(64) (если ещё ENUM)
        try {
            $stmt = $pdo->query("SHOW COLUMNS FROM `va_users` LIKE 'role'");
            $column = $stmt->fetch(\PDO::FETCH_ASSOC);
            if ($column && stripos((string)$column['Type'], 'enum') === 0) {
                $pdo->exec('ALTER TABLE `va_users` MODIFY `role` VARCHAR(64) NOT NULL DEFAULT "user"');
            }
        } catch (\Throwable $e) {
            // Игнорируем ошибку, колонка может уже быть VARCHAR
        }

        // Сид-данные: роли и права
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

        $findRole = $pdo->prepare('SELECT `id` FROM `va_roles` WHERE `role_key` = :key LIMIT 1');
        $insertRole = $pdo->prepare('INSERT INTO `va_roles` (`role_key`, `name`, `description`, `is_system`) VALUES (:key, :name, :description, :is_system)');
        $clearPerms = $pdo->prepare('DELETE FROM `va_role_permissions` WHERE `role_id` = :role_id');
        $insertPerm = $pdo->prepare('INSERT IGNORE INTO `va_role_permissions` (`role_id`, `page_slug`) VALUES (:role_id, :page_slug)');

        $pdo->beginTransaction();
        try {
            foreach ($roles as [$key, $name, $description, $isSystem, $pages]) {
                $findRole->execute(['key' => $key]);
                $roleId = (int)$findRole->fetchColumn();
                if (!$roleId) {
                    $insertRole->execute([
                        'key' => $key,
                        'name' => $name,
                        'description' => $description,
                        'is_system' => $isSystem,
                    ]);
                    $roleId = (int)$pdo->lastInsertId();
                }
                $clearPerms->execute(['role_id' => $roleId]);
                foreach ($pages as $page) {
                    $insertPerm->execute(['role_id' => $roleId, 'page_slug' => $page]);
                }
            }
            $pdo->commit();
        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }
    },

    'down' => function (\PDO $pdo): void {
        $pdo->exec('DROP TABLE IF EXISTS `va_role_permissions`;');
        $pdo->exec('DROP TABLE IF EXISTS `va_roles`;');
        // Не откатываем изменение users.role обратно в ENUM — это ломает новые роли
    },

    'description' => 'Roles and permissions tables with seed data (RBAC)'
];