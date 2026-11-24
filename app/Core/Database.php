<?php

declare(strict_types=1);

namespace App\Core {

    use PDO;
    use PDOException;
    use RuntimeException;

    final class Database
    {
        private static $connection;
        private static $config;

        /**
         * Префикс имён таблиц из .env (DB_PREFIX).
         */
        public static function prefix(): string
        {
            $config = self::loadConfig();
            return isset($config['prefix']) && is_string($config['prefix']) ? $config['prefix'] : '';
        }

        public static function connection()
        {
            if (self::$connection instanceof PDO) {
                return self::$connection;
            }

            $config = self::loadConfig();

            try {
                self::$connection = new PDO($config['dsn'], $config['username'], $config['password'], [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]);
                self::$connection->exec("SET time_zone = '+00:00'");
            } catch (PDOException $error) {
                error_log('MySQL connection failed: ' . $error->getMessage());
                throw new RuntimeException('Could not connect to MySQL.', 0, $error);
            }

            return self::$connection;
        }

        /**
         * Конфигурация MySQL (config/database.php), валидируется один раз.
         */
        private static function loadConfig(): array
        {
            if (is_array(self::$config)) {
                return self::$config;
            }

            $configPath = APP_ROOT . '/config/database.php';
            if (!is_file($configPath)) {
                throw new RuntimeException('MySQL configuration is missing.');
            }
            $config = require $configPath;
            if (!is_array($config) || empty($config['dsn']) ||
                !isset($config['username'], $config['password']) ||
                !is_string($config['dsn']) || !is_string($config['username']) || !is_string($config['password'])) {
                throw new RuntimeException('MySQL configuration is invalid.');
            }
            if (strpos($config['dsn'], 'YOUR_DATABASE_NAME') !== false ||
                $config['username'] === 'YOUR_DATABASE_USER' ||
                $config['password'] === 'YOUR_DATABASE_PASSWORD') {
                throw new RuntimeException('MySQL configuration contains placeholders.');
            }

            self::$config = $config;
            return $config;
        }
    }
}

// Глобальный хелпер: имя таблицы с префиксом из .env (DB_PREFIX)

namespace {
    if (!function_exists('db_table')) {
        /**
         * Полное имя таблицы БД с префиксом из .env (DB_PREFIX).
         */
        function db_table(string $name): string
        {
            return App\Core\Database::prefix() . $name;
        }
    }
}
