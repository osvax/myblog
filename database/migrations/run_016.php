<?php
define('APP_ROOT', dirname(__DIR__, 2));
require_once APP_ROOT . '/app/Core/Database.php';
require APP_ROOT . '/app/bootstrap.php';
$sql = file_get_contents(APP_ROOT . '/database/migrations/016_auth_settings.sql');
if ($sql === false) throw new RuntimeException('Migration not found');
$sql = str_replace([' users', ' site_settings'], [' ' . db_table('users'), ' ' . db_table('site_settings')], $sql);
foreach (array_filter(array_map('trim', explode(';', $sql))) as $statement) {
    \App\Core\Database::connection()->exec($statement);
}
echo "Authentication settings migration completed.\n";
