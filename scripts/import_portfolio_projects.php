<?php

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require dirname(__DIR__) . '/app/bootstrap.php';

$projects = (new \App\Models\PortfolioImporter())->importNew();
foreach ($projects as $slug) {
    echo "Импортирован проект: {$slug}\n";
}
echo 'Добавлено проектов: ' . count($projects) . ".\n";
