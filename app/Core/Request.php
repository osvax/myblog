<?php

declare(strict_types=1);

namespace App\Core;

final class Request
{
    private $method;
    private $path;

    public function __construct($method, $uri)
    {
        $this->method = strtoupper((string) $method);
        $path = parse_url((string) $uri, PHP_URL_PATH);
        $path = is_string($path) ? $path : '/';

        $basePath = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
        if ($basePath !== '/' && $basePath !== '.' && strpos($path, $basePath . '/') === 0) {
            $path = substr($path, strlen($basePath));
        }

        $this->path = '/' . trim($path, '/');
        if ($this->path === '//') {
            $this->path = '/';
        }
    }

    public static function fromGlobals()
    {
        return new self($_SERVER['REQUEST_METHOD'] ?? 'GET', $_SERVER['REQUEST_URI'] ?? '/');
    }

    public function method()
    {
        return $this->method;
    }

    public function path()
    {
        return $this->path;
    }

    public function json()
    {
        $raw = file_get_contents('php://input');
        if ($raw === false || trim($raw) === '') {
            return [];
        }
        $data = json_decode($raw, true);
        return is_array($data) ? $data : [];
    }
}
