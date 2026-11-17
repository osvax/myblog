<?php

require dirname(__DIR__) . '/app/bootstrap.php';

$router = new App\Core\Router();
foreach ([APP_ROOT . '/routes/web.php', APP_ROOT . '/routes/api.php'] as $routeFile) {
    $registerRoutes = require $routeFile;
    $registerRoutes($router);
}

$response = $router->dispatch(App\Core\Request::fromGlobals());

// Логируем посещение для авторизованного пользователя
if (function_exists('current_user') && current_user()) {
    $user = current_user();
    $pageUrl = $_SERVER['REQUEST_URI'] ?? '/';
    $pageTitle = 'Пользовательская страница';
    \App\Core\VisitLogger::log('user', $pageUrl, $pageTitle);
}

$response->send();
