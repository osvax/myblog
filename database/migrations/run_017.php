<?php

require dirname(__DIR__, 2) . '/app/bootstrap.php';
require_once APP_ROOT . '/app/Core/Database.php';

$sql = file_get_contents(APP_ROOT . '/database/migrations/017_config_to_settings.sql');
if ($sql === false) {
    throw new RuntimeException('Migration not found');
}

$sql = str_replace(' site_settings', ' ' . db_table('site_settings'), $sql);
foreach (array_filter(array_map('trim', explode(';', $sql))) as $statement) {
    \App\Core\Database::connection()->exec($statement);
}

echo "Config settings migration completed.\n";
