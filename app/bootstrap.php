<?php

define('APP_ROOT', dirname(__DIR__));

if (!function_exists('request_is_https')) {
    function request_is_https(): bool
    {
        if (!empty($_SERVER['HTTPS']) && strtolower((string) $_SERVER['HTTPS']) !== 'off') {
            return true;
        }

        $forwardedProto = strtolower(trim((string) ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '')));
        if ($forwardedProto !== '') {
            return strtolower(trim(explode(',', $forwardedProto)[0])) === 'https';
        }

        return (string) ($_SERVER['SERVER_PORT'] ?? '') === '443';
    }
}

// Load environment variables from .env
if (is_file(APP_ROOT . '/vendor/autoload.php')) {
    require_once APP_ROOT . '/vendor/autoload.php';
    if (is_file(APP_ROOT . '/.env')) {
        $dotenv = Dotenv\Dotenv::createImmutable(APP_ROOT);
        $dotenv->load();
    }
}

spl_autoload_register(function ($className) {
    $prefix = 'App\\';
    if (strncmp($className, $prefix, strlen($prefix)) !== 0) {
        return;
    }

    $relativeClass = substr($className, strlen($prefix));
    $file = APP_ROOT . '/app/' . str_replace('\\', '/', $relativeClass) . '.php';
    if (is_file($file)) {
        require_once $file;
    }
});

require_once APP_ROOT . '/app/Core/admin-functions.php';
require_once APP_ROOT . '/app/Core/auth.php';
require_once APP_ROOT . '/app/Core/custom-fields.php';
require_once APP_ROOT . '/app/Core/slug.php';
require_once APP_ROOT . '/app/Core/assets.php';
require_once APP_ROOT . '/app/Core/calculator.php';
require_once APP_ROOT . '/app/Core/menu.php';
require_once APP_ROOT . '/app/Core/static-pages.php';

// ============ DI Container ============
// Строим контейнер один раз и делаем доступным глобально через container()
$containerBuilder = new DI\ContainerBuilder();
$containerDefinitions = require APP_ROOT . '/config/container.php';
$containerDefinitions($containerBuilder);
$container = $containerBuilder->build();

// Глобальная функция для доступа к контейнеру
if (!function_exists('container')) {
    /**
     * @template T
     * @param class-string<T> $id
     * @return T
     */
    function container(string $id): mixed
    {
        global $container;
        return $container->get($id);
    }
}
