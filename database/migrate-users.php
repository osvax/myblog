<?php

define('APP_ROOT', dirname(__DIR__));
require APP_ROOT . '/app/Core/Database.php';

use App\Core\Database;

$connection = Database::connection();
$migration = APP_ROOT . '/database/migrations/002_users.sql';
$sql = file_get_contents($migration);
if ($sql === false) {
    throw new RuntimeException('Could not read the users database migration.');
}

foreach (array_filter(array_map('trim', explode(';', $sql))) as $statement) {
    $connection->exec($statement);
}

echo "Users table migration completed.\n";
