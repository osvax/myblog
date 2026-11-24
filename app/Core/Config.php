<?php

declare(strict_types=1);

namespace App\Core;

final class Config
{
    private array $config = [];
    private bool $loaded = false;

    public function __construct()
    {
        $this->load();
    }

    private function load(): void
    {
        if ($this->loaded) {
            return;
        }

        // 1. Загружаем .env (уже загружен в bootstrap.php через Dotenv)
        // 2. Загружаем config/*.php
        $configDir = APP_ROOT . '/config';
        if (is_dir($configDir)) {
            foreach (scandir($configDir) as $file) {
                if (preg_match('/\.php$/', $file)) {
                    $key = basename($file, '.php');
                    $this->config[$key] = require $configDir . '/' . $file;
                }
            }
        }

        $this->loaded = true;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        // Поддержка точечной нотации: database.host
        $keys = explode('.', $key);
        $value = $this->config;

        foreach ($keys as $k) {
            if (!is_array($value) || !array_key_exists($k, $value)) {
                // Фолбэк на env()
                return $this->env($key, $default);
            }
            $value = $value[$k];
        }

        return $value;
    }

    public function env(string $key, mixed $default = null): mixed
    {
        $value = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);
        return $value !== false ? $value : $default;
    }

    public function database(): array
    {
        return $this->get('database', []);
    }

    public function app(): array
    {
        return [
            'env' => $this->env('APP_ENV', 'production'),
            'debug' => filter_var($this->env('APP_DEBUG', 'false'), FILTER_VALIDATE_BOOLEAN),
            'url' => $this->env('APP_URL', ''),
            'key' => $this->env('APP_KEY', ''),
            'timezone' => $this->env('APP_TIMEZONE', 'UTC'),
            'locale' => $this->env('APP_LOCALE', 'ru'),
        ];
    }

    public function mail(): array
    {
        return [
            'mailer' => $this->env('MAIL_MAILER', 'smtp'),
            'host' => $this->env('MAIL_HOST', ''),
            'port' => (int) $this->env('MAIL_PORT', 587),
            'username' => $this->env('MAIL_USERNAME', ''),
            'password' => $this->env('MAIL_PASSWORD', ''),
            'encryption' => $this->env('MAIL_ENCRYPTION', 'tls'),
            'from' => [
                'address' => $this->env('MAIL_FROM_ADDRESS', ''),
                'name' => $this->env('MAIL_FROM_NAME', 'MyBlog'),
            ],
        ];
    }

    public function redis(): array
    {
        return [
            'host' => $this->env('REDIS_HOST', '127.0.0.1'),
            'port' => (int) $this->env('REDIS_PORT', 6379),
            'password' => $this->env('REDIS_PASSWORD', null),
            'database' => (int) $this->env('REDIS_DB', 0),
        ];
    }

    public function session(): array
    {
        return [
            'lifetime' => (int) $this->env('SESSION_LIFETIME', 120),
            'secure' => filter_var($this->env('SESSION_SECURE_COOKIE', 'false'), FILTER_VALIDATE_BOOLEAN),
            'samesite' => 'Strict',
        ];
    }

    public function all(): array
    {
        return $this->config;
    }
}
