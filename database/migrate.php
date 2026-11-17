<?php

define('APP_ROOT', dirname(__DIR__));
require APP_ROOT . '/app/Core/Database.php';

use App\Core\Database;

$connection = Database::connection();
$migration = APP_ROOT . '/database/migrations/001_content_and_crm.sql';
$sql = file_get_contents($migration);
if ($sql === false) {
    throw new RuntimeException('Could not read the initial database migration.');
}

foreach (array_filter(array_map('trim', explode(';', $sql))) as $statement) {
    $connection->exec($statement);
}

$seed = require APP_ROOT . '/config/public-content.php';
$categories = [];
$connection->beginTransaction();
try {
    foreach ($seed['articles'] as $slug => $article) {
        $name = $article['category'];
        $categorySlug = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $name);
        $categorySlug = strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '-', (string) $categorySlug), '-'));
        $category = $connection->prepare(
            'INSERT INTO categories (name, slug) VALUES (:name, :slug)
             ON DUPLICATE KEY UPDATE name = VALUES(name)'
        );
        $category->execute(['name' => $name, 'slug' => $categorySlug !== '' ? $categorySlug : hash('sha256', $name)]);
        $lookup = $connection->prepare('SELECT id FROM categories WHERE name = :name');
        $lookup->execute(['name' => $name]);
        $categories[$name] = (int) $lookup->fetchColumn();

        $insert = $connection->prepare(
            'INSERT IGNORE INTO articles
                (id, slug, title, description, body_html, category_id, author, published_at, icon, is_published)
             VALUES
                (:id, :slug, :title, :description, NULL, :category_id, :author, :published_at, :icon, 1)'
        );
        $insert->execute([
            'id' => substr(hash('sha256', 'article:' . $slug), 0, 32),
            'slug' => $slug,
            'title' => $article['title'],
            'description' => $article['description'],
            'category_id' => $categories[$name],
            'author' => 'Администратор',
            'published_at' => date('Y-m-d'),
            'icon' => $article['icon'],
        ]);
    }

    foreach ($seed['projects'] as $slug => $project) {
        $insert = $connection->prepare(
            'INSERT IGNORE INTO portfolio_projects
                (id, slug, title, technologies, icon, description, is_published)
             VALUES (:id, :slug, :title, :technologies, :icon, :description, 1)'
        );
        $insert->execute([
            'id' => substr(hash('sha256', 'project:' . $slug), 0, 32),
            'slug' => $slug,
            'title' => $project['title'],
            'technologies' => $project['description'],
            'icon' => $project['icon'],
            'description' => $project['title'],
        ]);
    }

    $connection->commit();
} catch (Throwable $error) {
    if ($connection->inTransaction()) {
        $connection->rollBack();
    }
    throw $error;
}

echo "Database migration and initial content seed completed.\n";
