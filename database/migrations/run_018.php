<?php

require dirname(__DIR__, 2) . '/app/bootstrap.php';

$db = \App\Core\Database::connection();
$prefix = \App\Core\Database::prefix();
$sql = file_get_contents(APP_ROOT . '/database/migrations/018_category_navigation.sql');
if ($sql === false) {
    throw new RuntimeException('Migration not found');
}
$sql = str_replace(
    ['{CATEGORY_NAVIGATION_TABLE}', '{CATEGORIES_TABLE}'],
    [$prefix . 'category_navigation', $prefix . 'categories'],
    $sql
);
$db->exec($sql);
echo "Category navigation migration completed.\n";
