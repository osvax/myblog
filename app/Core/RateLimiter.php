<?php

namespace App\Core;

use Predis\Client as RedisClient;

/**
 * Простой rate limiter с поддержкой Redis и файловым fallback.
 */
class RateLimiter
{
    private static ?RedisClient $redis = null;
    private static ?bool $redisAvailable = null;
    private static ?string $storagePath = null;

    /**
     * Логирование ошибок через ApplicationLogger (если контейнер доступен) или error_log.
     */
    private static function logError(string $message): void
    {
        if (function_exists('container')) {
            try {
                $logger = container(ApplicationLogger::class);
                $logger->error($message);
                return;
            } catch (\Throwable) {
                // игнорируем, fallback на error_log
            }
        }
        error_log($message);
    }

    /**
     * Проверить доступность Redis.
     */
    private static function checkRedis(): bool
    {
        if (self::$redisAvailable !== null) {
            return self::$redisAvailable;
        }

        $host = $_ENV['REDIS_HOST'] ?? '127.0.0.1';
        $port = (int) ($_ENV['REDIS_PORT'] ?? 6379);
        $password = $_ENV['REDIS_PASSWORD'] ?? null;
        $db = (int) ($_ENV['REDIS_DB'] ?? 0);

        try {
            $params = ['scheme' => 'tcp', 'host' => $host, 'port' => $port, 'database' => $db];
            if (!empty($password)) {
                $params['password'] = $password;
            }
            self::$redis = new RedisClient($params);
            // Ping to verify connection
            self::$redis->ping();
            self::$redisAvailable = true;
        } catch (\Throwable $e) {
            self::$redisAvailable = false;
            self::$redis = null;
        }

        return self::$redisAvailable;
    }

    /**
     * Получить путь к файловому хранилищу.
     */
    private static function getStoragePath(): string
    {
        if (self::$storagePath === null) {
            self::$storagePath = APP_ROOT . '/storage/rate-limit';
            if (!is_dir(self::$storagePath)) {
                @mkdir(self::$storagePath, 0755, true);
            }
        }
        return self::$storagePath;
    }

    /**
     * Проверить и увеличить счётчик.
     *
     * @param string $key      Уникальный ключ (например: "login:ip:192.168.1.1")
     * @param int    $maxAttempts  Максимальное количество попыток
     * @param int    $decayMinutes Время жизни в минутах
     * @return array ['allowed' => bool, 'remaining' => int, 'retryAfter' => int|null]
     */
    public static function hit(string $key, int $maxAttempts, int $decayMinutes): array
    {
        $decaySeconds = $decayMinutes * 60;
        $now = time();
        $windowStart = $now - $decaySeconds;

        if (self::checkRedis()) {
            return self::hitRedis($key, $maxAttempts, $decaySeconds, $windowStart, $now);
        }

        return self::hitFile($key, $maxAttempts, $decaySeconds, $windowStart, $now);
    }

    /**
     * Реализация через Redis.
     */
    private static function hitRedis(string $key, int $maxAttempts, int $decaySeconds, int $windowStart, int $now): array
    {
        $redisKey = 'ratelimit:' . $key;

        try {
            $pipe = self::$redis->pipeline();
            $pipe->zremrangebyscore($redisKey, 0, $windowStart);
            $pipe->zcard($redisKey);
            $pipe->zadd($redisKey, $now, $now . ':' . uniqid('', true));
            $pipe->expire($redisKey, $decaySeconds);
            $results = $pipe->execute();

            $count = (int) $results[1];
            $allowed = $count < $maxAttempts;
            $remaining = max(0, $maxAttempts - $count - ($allowed ? 1 : 0));
            $retryAfter = $allowed ? null : $decaySeconds;

            return ['allowed' => $allowed, 'remaining' => $remaining, 'retryAfter' => $retryAfter];
        } catch (\Throwable $e) {
            self::logError('RateLimiter Redis error: ' . $e->getMessage());
            // Fallback to file
            return self::hitFile($key, $maxAttempts, $decaySeconds, $windowStart, $now);
        }
    }

    /**
     * Реализация через файлы.
     */
    private static function hitFile(string $key, int $maxAttempts, int $decaySeconds, int $windowStart, int $now): array
    {
        $safeKey = preg_replace('/[^a-zA-Z0-9_:.-]/', '_', $key);
        $file = self::getStoragePath() . '/' . $safeKey . '.json';

        $data = ['hits' => []];
        if (is_file($file)) {
            $content = @file_get_contents($file);
            if ($content !== false) {
                $decoded = json_decode($content, true);
                if (is_array($decoded) && isset($decoded['hits'])) {
                    $data = $decoded;
                }
            }
        }

        // Удаляем старые хиты
        $data['hits'] = array_filter($data['hits'], function ($ts) use ($windowStart) {
            return $ts > $windowStart;
        });

        $count = count($data['hits']);
        $allowed = $count < $maxAttempts;

        if ($allowed) {
            $data['hits'][] = $now;
            @file_put_contents($file, json_encode($data), LOCK_EX);
        }

        $remaining = max(0, $maxAttempts - $count - ($allowed ? 1 : 0));
        $retryAfter = $allowed ? null : $decaySeconds;

        return ['allowed' => $allowed, 'remaining' => $remaining, 'retryAfter' => $retryAfter];
    }

    /**
     * Очистить лимиты для ключа.
     */
    public static function clear(string $key): void
    {
        if (self::checkRedis()) {
            try {
                self::$redis->del('ratelimit:' . $key);
            } catch (\Throwable $e) {
                // ignore
            }
        }

        $safeKey = preg_replace('/[^a-zA-Z0-9_:.-]/', '_', $key);
        $file = self::getStoragePath() . '/' . $safeKey . '.json';
        if (is_file($file)) {
            @unlink($file);
        }
    }

    /**
     * Получить клиентский IP.
     */
    public static function getClientIp(): string
    {
        // Проверяем заголовки прокси (если за Nginx/Cloudflare)
        $headers = ['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'HTTP_CLIENT_IP'];
        foreach ($headers as $header) {
            if (!empty($_SERVER[$header])) {
                $ips = explode(',', $_SERVER[$header]);
                return trim($ips[0]);
            }
        }
        return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    }

    /**
     * Сгенерировать ключ для эндпоинта.
     */
    public static function makeKey(string $endpoint, string $identifier = ''): string
    {
        $ip = self::getClientIp();
        $ua = substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 50);
        $uaHash = substr(md5($ua), 0, 8);
        $identifier = $identifier ? ':' . $identifier : '';
        return $endpoint . ':' . $ip . ':' . $uaHash . $identifier;
    }
}