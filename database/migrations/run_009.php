<?php

define('APP_ROOT', 'E:/OSPanel/www-html/myblog.loc');
require APP_ROOT . '/app/bootstrap.php';

$db = \App\Core\Database::connection();

echo "=== Creating landing_pages table ===\n";

try {
    $db->exec("DROP TABLE IF EXISTS " . db_table('landing_pages') . "");
    echo "  Old table dropped\n";
} catch (\Exception $e) {
    // Ignore
}

$sql = file_get_contents(APP_ROOT . '/database/migrations/009_landing_pages.sql');
if ($sql) {
    $db->exec($sql);
    echo "  Table created\n";
} else {
    echo "  Error: SQL file not found\n";
    exit(1);
}

// Create default home page
$stmt = $db->prepare("INSERT INTO " . db_table('landing_pages') . " (slug, title, is_active, generated_at) VALUES ('home', 'Главная страница', 1, NOW()) ON DUPLICATE KEY UPDATE title = VALUES(title)");
$stmt->execute();

echo "  Default home page created\n";
echo "Done.\n";
