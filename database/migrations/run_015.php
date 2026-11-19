<?php

/**
 * Миграция 015: добавление колонки referrer в user_activities.
 *
 * Идемпотентна: повторный прогон ничего не делает.
 * Запуск: php database/migrations/run_015.php
 */

require dirname(__DIR__, 2) . '/app/bootstrap.php';

$db = \App\Core\Database::connection();
$table = db_table('user_activities');

$stmt = $db->prepare(
    'SELECT COUNT(*) FROM information_schema.columns
     WHERE table_schema = DATABASE() AND table_name = ? AND column_name = ?'
);
$stmt->execute([$table, 'referrer']);
$exists = (int) $stmt->fetchColumn() > 0;

if ($exists) {
    echo "Колонка referrer уже есть в {$table} — ничего не делаю.\n";
} else {
    $db->exec(
        "ALTER TABLE `{$table}`
         ADD COLUMN `referrer` varchar(500) NULL DEFAULT NULL COMMENT 'Страница входа (HTTP_REFERER)' AFTER `user_agent`"
    );
    echo "Колонка referrer добавлена в {$table}.\n";
}
