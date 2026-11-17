<?php
define('APP_ROOT', 'E:/OSPanel/www-html/myblog.loc');
require APP_ROOT . '/app/bootstrap.php';

$db = \App\Core\Database::connection();

echo "=== Migration 003: user_activities ===\n";
try {
    $sql = file_get_contents(APP_ROOT . '/database/migrations/003_user_activities.sql');
    if ($sql) {
        $db->exec($sql);
        echo "  OK: user_activities created\n";
    }
} catch (\Exception $e) {
    echo "  Error: " . $e->getMessage() . "\n";
}

echo "\n=== Migration 004: user_custom_fields ===\n";
try {
    $sql = file_get_contents(APP_ROOT . '/database/migrations/004_user_custom_fields.sql');
    if ($sql) {
        $db->exec($sql);
        echo "  OK: user_custom_fields created\n";
    }
} catch (\Exception $e) {
    echo "  Error: " . $e->getMessage() . "\n";
}

echo "\n=== Migration 005: user profile fields ===\n";
$columns = [
    'last_visit' => 'DATETIME NULL DEFAULT NULL COMMENT "Последнее посещение"',
    'last_ip' => "VARCHAR(45) NOT NULL DEFAULT '' COMMENT 'IP последнего посещения'",
    'phone' => "VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Телефон'",
    'about' => "TEXT NULL COMMENT 'О себе'",
    'position' => "VARCHAR(100) NOT NULL DEFAULT '' COMMENT 'Должность / роль'",
    'bio' => "TEXT NULL COMMENT 'Биография'",
    'timezone' => "VARCHAR(50) NOT NULL DEFAULT '' COMMENT 'Часовой пояс'",
    'language' => "VARCHAR(10) NOT NULL DEFAULT 'ru' COMMENT 'Язык интерфейса'",
];

foreach ($columns as $name => $definition) {
    try {
        $stmt = $db->query("SHOW COLUMNS FROM users LIKE '{$name}'");
        $exists = $stmt->fetch();
        if (!$exists) {
            $db->exec("ALTER TABLE users ADD `{$name}` {$definition}");
            echo "  Added: {$name}\n";
        } else {
            echo "  Exists: {$name}\n";
        }
    } catch (\Exception $e) {
        echo "  Error adding {$name}: " . $e->getMessage() . "\n";
    }
}

// Индексы
$indexes = [
    'idx_users_last_visit' => 'last_visit',
    'idx_users_last_ip' => 'last_ip',
];

foreach ($indexes as $indexName => $columnName) {
    try {
        $stmt = $db->query("SHOW INDEX FROM users WHERE Key_name = '{$indexName}'");
        $exists = $stmt->fetch();
        if (!$exists) {
            $db->exec("ALTER TABLE users ADD INDEX {$indexName} ({$columnName})");
            echo "  Added index: {$indexName}\n";
        }
    } catch (\Exception $e) {
        echo "  Error adding index {$indexName}: " . $e->getMessage() . "\n";
    }
}

echo "\n=== Final state of users table ===\n";
$stmt = $db->query('SHOW COLUMNS FROM users');
$cols = $stmt->fetchAll();
foreach ($cols as $c) {
    echo "  - " . $c['Field'] . " (" . $c['Type'] . ")\n";
}

echo "\nDone.\n";
