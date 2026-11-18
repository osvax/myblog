<?php

define('APP_ROOT', dirname(__DIR__));

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
