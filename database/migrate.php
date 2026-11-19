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
        // Транслитерация кириллицы вручную: iconv на Windows портит русские названия
        $translitMap = [
            'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd', 'е' => 'e',
            'ё' => 'yo', 'ж' => 'zh', 'з' => 'z', 'и' => 'i', 'й' => 'y', 'к' => 'k',
            'л' => 'l', 'м' => 'm', 'н' => 'n', 'о' => 'o', 'п' => 'p', 'р' => 'r',
            'с' => 's', 'т' => 't', 'у' => 'u', 'ф' => 'f', 'х' => 'h', 'ц' => 'ts',
            'ч' => 'ch', 'ш' => 'sh', 'щ' => 'sch', 'ъ' => '', 'ы' => 'y', 'ь' => '',
            'э' => 'e', 'ю' => 'yu', 'я' => 'ya',
        ];
        $categorySlug = mb_strtolower(strtr(mb_strtolower(trim($name)), $translitMap));
        $categorySlug = trim(preg_replace('/[^a-z0-9]+/', '-', $categorySlug), '-');
        $category = $connection->prepare(
            'INSERT INTO ' . db_table('categories') . ' (name, slug) VALUES (:name, :slug)
             ON DUPLICATE KEY UPDATE name = VALUES(name)'
        );
        $category->execute(['name' => $name, 'slug' => $categorySlug !== '' ? $categorySlug : hash('sha256', $name)]);
        $lookup = $connection->prepare('SELECT id FROM ' . db_table('categories') . ' WHERE name = :name');
        $lookup->execute(['name' => $name]);
        $categories[$name] = (int) $lookup->fetchColumn();

        $insert = $connection->prepare(
            'INSERT IGNORE INTO ' . db_table('articles') . '
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
            'INSERT IGNORE INTO ' . db_table('portfolio_projects') . '
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
