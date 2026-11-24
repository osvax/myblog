<?php

declare(strict_types=1);

require dirname(__DIR__) . '/app/bootstrap.php';

$importer = new \App\Models\PortfolioImporter();
foreach (['agroprospect-master', 'forma-studio', 'textile', 'chistkoff'] as $folder) {
    $markdown = file_get_contents(APP_ROOT . '/public/portfolio/' . $folder . '/job/description.md');
    $data = $importer->parse($markdown);
    foreach (['title', 'description', 'task', 'solution', 'technologies'] as $field) {
        if (empty($data[$field])) {
            throw new RuntimeException($folder . ': пустое поле ' . $field);
        }
    }
    if ($folder !== 'chistkoff' && ($data['result'] === '' || !$data['year'] || $data['duration'] === '')) {
        throw new RuntimeException($folder . ': не разобраны результат, год или сроки');
    }
}

$project = ['infoItems' => [
    ['kind' => 'link', 'name' => 'Демо', 'value' => '/portfolio/textile/index.html'],
    ['kind' => 'link', 'name' => 'ТЗ', 'value' => '/portfolio/textile/job/TZ-template.pdf'],
    ['kind' => 'link', 'name' => 'Недопустимая ссылка', 'value' => 'javascript:alert(1)'],
]];
ob_start();
require APP_ROOT . '/app/Views/frontend/templates/default/partials/project-info-items.php';
$html = ob_get_clean();
if (substr_count($html, '<a href=') !== 2 || strpos($html, 'href="javascript:') !== false) {
    throw new RuntimeException('Некорректный вывод локальных ссылок');
}

echo "Portfolio importer checks passed.\n";
