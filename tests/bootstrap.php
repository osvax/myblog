<?php

/**
 * PHPUnit bootstrap для тестов с SQLite :memory:
 *
 * Настраивает окружение, загружает автозагрузчик, создаёт схему БД в памяти.
 */

define('APP_ROOT', dirname(__DIR__));

// Load Composer autoloader
require_once APP_ROOT . '/vendor/autoload.php';

// Load .env.testing if exists
if (is_file(APP_ROOT . '/.env.testing')) {
    $dotenv = Dotenv\Dotenv::createImmutable(APP_ROOT, '.env.testing');
    $dotenv->load();
} elseif (is_file(APP_ROOT . '/.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(APP_ROOT);
    $dotenv->load();
}

// Переопределяем конфигурацию БД для тестов
$_ENV['DB_CONNECTION'] = 'sqlite';
$_ENV['DB_DATABASE'] = ':memory:';
$_ENV['DB_PREFIX'] = 'va_';
$_ENV['APP_ENV'] = 'testing';

// Автозагрузка классов App\
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

// Подключаем хелперы (без сессий для CLI)
if (!function_exists('request_is_https')) {
    function request_is_https(): bool { return false; }
}
if (!function_exists('db_table')) {
    function db_table(string $table): string { return 'va_' . $table; }
}
if (!function_exists('admin_is_authenticated')) {
    function admin_is_authenticated(): bool { return false; }
}
if (!function_exists('admin_can_access')) {
    function admin_can_access(string $permission): bool { return true; }
}
if (!function_exists('admin_require_csrf')) {
    function admin_require_csrf(): void {}
}
if (!function_exists('admin_start_session')) {
    function admin_start_session(): void {}
}
if (!function_exists('flash')) {
    function flash(string $type, string $message): void {}
}
if (!function_exists('redirect')) {
    function redirect(string $url, int $status = 302): void {}
}
if (!function_exists('is_post')) {
    function is_post(): bool { return $_SERVER['REQUEST_METHOD'] === 'POST'; }
}
if (!function_exists('is_get')) {
    function is_get(): bool { return $_SERVER['REQUEST_METHOD'] === 'GET'; }
}
if (!function_exists('is_ajax')) {
    function is_ajax(): bool { return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest'; }
}
if (!function_exists('sanitize')) {
    function sanitize(string $input): string { return htmlspecialchars($input, ENT_QUOTES, 'UTF-8'); }
}
if (!function_exists('slugify')) {
    function slugify(string $text): string { return strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $text), '-')); }
}
if (!function_exists('format_price')) {
    function format_price(float $price, string $currency = '₽'): string { return number_format($price, 0, ',', ' ') . ' ' . $currency; }
}
if (!function_exists('format_date')) {
    function format_date(string $date, string $format = 'd.m.Y'): string { return date($format, strtotime($date)); }
}
if (!function_exists('get_admin_url')) {
    function get_admin_url(string $path = ''): string { return '/admin' . ($path ? '/' . ltrim($path, '/') : ''); }
}
if (!function_exists('asset')) {
    function asset(string $path): string { return '/' . ltrim($path, '/'); }
}
if (!function_exists('view')) {
    function view(string $path, array $data = []): string { return ''; }
}
if (!function_exists('config')) {
    function config(string $key, $default = null) { return $_ENV[$key] ?? $default; }
}
if (!function_exists('logger')) {
    function logger(string $message): void {}
}
if (!function_exists('mailer')) {
    function mailer(): \App\Core\Mailer { return new \App\Core\Mailer(); }
}
if (!function_exists('container')) {
    function container(string $id): mixed {
        static $container = null;
        if ($container === null) {
            $containerBuilder = new DI\ContainerBuilder();
            $containerDefinitions = require APP_ROOT . '/config/container.php';
            $containerDefinitions($containerBuilder);
            $container = $containerBuilder->build();
        }
        return $container->get($id);
    }
}

// Инициализируем БД в памяти
require_once APP_ROOT . '/app/Core/Database.php';

// Патчим Database::connection() для SQLite в памяти
$reflection = new ReflectionClass(\App\Core\Database::class);
$property = $reflection->getProperty('connection');
$property->setAccessible(true);

// Создаём SQLite соединение
$pdo = new PDO('sqlite::memory:', null, null, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
]);
$pdo->exec("PRAGMA foreign_keys = ON");
$property->setValue(null, $pdo);

// Запускаем миграции для создания схемы
require_once APP_ROOT . '/app/Core/MigrationRunner.php';
$runner = new \App\Core\MigrationRunner($pdo);
$runner->up();

// Делаем контейнер доступным глобально (для тестов)
if (!function_exists('container')) {
    $containerBuilder = new DI\ContainerBuilder();
    $containerDefinitions = require APP_ROOT . '/config/container.php';
    $containerDefinitions($containerBuilder);
    $container = $containerBuilder->build();

    function container(string $id): mixed
    {
        global $container;
        return $container->get($id);
    }
}