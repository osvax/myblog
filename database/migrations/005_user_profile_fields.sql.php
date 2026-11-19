<?php

// Миграция: добавление полей профиля пользователя
// Добавляем: last_visit, last_ip, phone, about, position, bio, timezone, language

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

$db = \App\Core\Database::connection();

foreach ($columns as $name => $definition) {
    try {
        $stmt = $db->query("SHOW COLUMNS FROM " . db_table('users') . " LIKE '{$name}'");
        $exists = $stmt->fetch();
        if (!$exists) {
            $db->query("ALTER TABLE " . db_table('users') . " ADD `{$name}` {$definition}");
            echo "Добавлено поле: {$name}\n";
        } else {
            echo "Поле уже существует: {$name}\n";
        }
    } catch (\Exception $e) {
        echo "Ошибка при добавлении поля {$name}: " . $e->getMessage() . "\n";
    }
}

// Индексы для новых полей
$indexes = [
    'idx_users_last_visit' => 'last_visit',
    'idx_users_last_ip' => 'last_ip',
];

foreach ($indexes as $indexName => $columnName) {
    try {
        $stmt = $db->query("SHOW INDEX FROM " . db_table('users') . " WHERE Key_name = '{$indexName}'");
        $exists = $stmt->fetch();
        if (!$exists) {
            $db->query("ALTER TABLE " . db_table('users') . " ADD INDEX {$indexName} ({$columnName})");
            echo "Добавлен индекс: {$indexName}\n";
        }
    } catch (\Exception $e) {
        echo "Ошибка при создании индекса {$indexName}: " . $e->getMessage() . "\n";
    }
}

echo "Миграция завершена.\n";
