<?php

require dirname(__DIR__, 2) . '/app/bootstrap.php';

$db = \App\Core\Database::connection();
$table = db_table('portfolio_projects');
$sql = file_get_contents(APP_ROOT . '/database/migrations/019_portfolio_result.sql');
if ($sql === false) {
    throw new RuntimeException('Migration not found');
}
$db->exec(str_replace('{PORTFOLIO_TABLE}', $table, $sql));

$results = [
    'project-shop' => 'Интернет-магазин получил понятный путь от каталога до оформления заказа, а обновлённая структура страниц создала основу для дальнейшего роста продаж.',
    'project-blog' => 'Публикации стали удобнее для чтения и поиска: контент организован по рубрикам, а ключевые показатели вовлечённости видны владельцу проекта.',
    'project-landing' => 'Посадочная страница быстрее объясняет ценность приложения и направляет посетителя к установке без лишних шагов.',
    'project-community' => 'Сообщество получило удобный сценарий публикации событий и общения участников, что упростило регулярное возвращение пользователей.',
    'project-photo' => 'Портфолио демонстрирует работы в едином визуальном стиле и помогает посетителям быстрее перейти к заявке на съёмку.',
];

$update = $db->prepare(
    'UPDATE ' . $table . ' SET result = :result WHERE slug = :slug AND (result IS NULL OR TRIM(result) = "")'
);
$count = 0;
foreach ($results as $slug => $result) {
    $update->execute(['slug' => $slug, 'result' => $result]);
    $count += $update->rowCount();
}

echo "Миграция 019 завершена. Заполнено тестовых результатов: {$count}.\n";
