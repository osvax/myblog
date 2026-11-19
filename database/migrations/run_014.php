<?php

/**
 * Миграция 014: применение префикса таблиц (DB_PREFIX из .env).
 *
 * - Переименовывает все таблицы, созданные миграциями 001–013.
 * - Новые таблицы портфолио (project_gallery, project_info_items)
 *   переименовывает, если они уже созданы без префикса, или создаёт.
 *
 * Идемпотентна: повторный прогон ничего не делает.
 * Запуск: php database/migrations/run_014.php
 */

require dirname(__DIR__, 2) . '/app/bootstrap.php';

$db = \App\Core\Database::connection();
$prefix = \App\Core\Database::prefix();

if ($prefix === '') {
    echo "Ошибка: DB_PREFIX не задан в .env — префикс применять нечем.\n";
    exit(1);
}

// Таблицы миграций 001–013 (создавались без префикса)
$legacyTables = [
    'articles', 'categories', 'consultant_message_reads', 'consultant_messages',
    'consultant_reads', 'consultant_settings', 'consultant_typing', 'consultant_users',
    'custom_fields', 'landing_page_settings', 'landing_pages', 'menu_items', 'menus',
    'messages', 'orders', 'portfolio_projects', 'role_permissions', 'roles',
    'site_settings', 'static_pages', 'user_activities', 'users',
];

// Новые таблицы: DDL для случая, когда их ещё нет вовсе
$newTables = [
    'project_gallery' => "CREATE TABLE `{$prefix}project_gallery` (
        `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
        `project_id` char(32) NOT NULL,
        `path` varchar(255) NOT NULL,
        `description` varchar(500) NOT NULL DEFAULT '',
        `sort_order` int(11) NOT NULL DEFAULT 0,
        `created_at` datetime NOT NULL DEFAULT current_timestamp(),
        PRIMARY KEY (`id`),
        KEY `idx_project_gallery_project` (`project_id`,`sort_order`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
    'project_info_items' => "CREATE TABLE `{$prefix}project_info_items` (
        `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
        `project_id` char(32) NOT NULL,
        `kind` varchar(20) NOT NULL,
        `name` varchar(160) NOT NULL,
        `value` varchar(500) NOT NULL DEFAULT '',
        `file_path` varchar(255) NOT NULL DEFAULT '',
        `sort_order` int(11) NOT NULL DEFAULT 0,
        `created_at` datetime NOT NULL DEFAULT current_timestamp(),
        PRIMARY KEY (`id`),
        KEY `idx_project_info_items_project` (`project_id`,`sort_order`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
];

$tableExists = static function (string $name) use ($db): bool {
    $stmt = $db->prepare('SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = ?');
    $stmt->execute([$name]);
    return (int) $stmt->fetchColumn() > 0;
};

// Первый столбец первичного ключа (для no-op в ON DUPLICATE KEY UPDATE)
$primaryColumn = static function (string $name) use ($db): string {
    $stmt = $db->prepare(
        "SELECT column_name FROM information_schema.key_column_usage
         WHERE table_schema = DATABASE() AND table_name = ? AND constraint_name = 'PRIMARY'
         ORDER BY ordinal_position LIMIT 1"
    );
    $stmt->execute([$name]);
    $column = $stmt->fetchColumn();
    return is_string($column) && $column !== '' ? $column : '';
};

echo "=== Миграция 014: префикс \"{$prefix}\" ===\n";

foreach ($legacyTables as $table) {
    $hasNew = $tableExists($prefix . $table);
    $hasOld = $tableExists($table);
    if ($hasNew && !$hasOld) {
        echo "  [ok] {$prefix}{$table} — уже с префиксом\n";
        continue;
    }
    if (!$hasNew && $hasOld) {
        $db->exec("RENAME TABLE `{$table}` TO `{$prefix}{$table}`");
        echo "  [>>] {$table} -> {$prefix}{$table}\n";
        continue;
    }
    if ($hasNew && $hasOld) {
        // Конкурентный случай: приложением уже создана таблица с префиксом
        // (ensureSchema), а старая с данными ещё не переименована —
        // переносим данные (новые строки приоритетнее) и удаляем остаток.
        // Сначала перенаводим внешние ключи, ссылающиеся на старую таблицу,
        // на новую — иначе DROP старой не пройдёт.
        $fkStmt = $db->prepare(
            "SELECT k.TABLE_NAME AS table_name, k.CONSTRAINT_NAME AS constraint_name,
                    GROUP_CONCAT(k.COLUMN_NAME ORDER BY k.ORDINAL_POSITION SEPARATOR ', ') AS cols,
                    GROUP_CONCAT(k.REFERENCED_COLUMN_NAME ORDER BY k.ORDINAL_POSITION SEPARATOR ', ') AS ref_cols,
                    MAX(r.DELETE_RULE) AS delete_rule
             FROM information_schema.KEY_COLUMN_USAGE k
             JOIN information_schema.REFERENTIAL_CONSTRAINTS r
               ON r.CONSTRAINT_SCHEMA = k.TABLE_SCHEMA
              AND r.TABLE_NAME = k.TABLE_NAME
              AND r.CONSTRAINT_NAME = k.CONSTRAINT_NAME
             WHERE k.TABLE_SCHEMA = DATABASE()
               AND k.REFERENCED_TABLE_SCHEMA = DATABASE()
               AND k.REFERENCED_TABLE_NAME = ?
               AND k.REFERENCED_COLUMN_NAME IS NOT NULL
             GROUP BY k.TABLE_NAME, k.CONSTRAINT_NAME"
        );
        $fkStmt->execute([$table]);
        foreach ($fkStmt->fetchAll() as $fk) {
            $rule = ($fk['delete_rule'] === 'CASCADE' || $fk['delete_rule'] === 'SET NULL' || $fk['delete_rule'] === 'NO ACTION')
                ? ' ON DELETE ' . $fk['delete_rule']
                : '';
            // DROP и ADD в отдельных ALTER: в одном statement MariaDB падает
            // с errno 121 (Duplicate key on write or update).
            $db->exec("ALTER TABLE `{$fk['table_name']}` DROP FOREIGN KEY `{$fk['constraint_name']}`");
            $db->exec("ALTER TABLE `{$fk['table_name']}`
                       ADD CONSTRAINT `{$fk['constraint_name']}`
                       FOREIGN KEY ({$fk['cols']}) REFERENCES `{$prefix}{$table}` ({$fk['ref_cols']}){$rule}");
        }
        $pk = $primaryColumn($prefix . $table);
        if ($pk === '') {
            echo "  [!!] {$table} — нет первичного ключа, перенос вручную!\n";
            continue;
        }
        $db->exec("INSERT INTO `{$prefix}{$table}` SELECT * FROM `{$table}`
                   ON DUPLICATE KEY UPDATE `{$prefix}{$table}`.`{$pk}` = `{$table}`.`{$pk}`");
        $db->exec("DROP TABLE `{$table}`");
        echo "  [~>] {$table} — данные перенесены в {$prefix}{$table}, остаток удалён\n";
        continue;
    }
    echo "  [..] {$table} — не существует, пропускаем\n";
}

foreach ($newTables as $table => $ddl) {
    if ($tableExists($prefix . $table)) {
        echo "  [ok] {$prefix}{$table} — уже с префиксом\n";
        continue;
    }
    if ($tableExists($table)) {
        $db->exec("RENAME TABLE `{$table}` TO `{$prefix}{$table}`");
        echo "  [>>] {$table} -> {$prefix}{$table}\n";
        continue;
    }
    $db->exec($ddl);
    echo "  [+] {$prefix}{$table} — создана\n";
}

echo "Готово.\n";
