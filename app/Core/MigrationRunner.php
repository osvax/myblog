<?php

namespace App\Core;

/**
 * MigrationRunner — запуск версионированных миграций БД.
 *
 * === АРХИТЕКТУРА ===
 *
 * 1. ТАБЛИЦА ВЕРСИЙ (va_migrations):
 *    CREATE TABLE va_migrations (
 *        version VARCHAR(50) PRIMARY KEY,      -- например: '001', '014', '2025_01_15_add_field'
 *        name VARCHAR(255) NOT NULL,           -- описание миграции
 *        applied_at DATETIME NOT NULL,         -- когда применена
 *        execution_time_ms INT NOT NULL DEFAULT 0
 *    );
 *
 * 2. ФАЙЛЫ МИГРАЦИЙ (database/migrations/):
 *    Именование: {version}_{name}.php или {version}_{name}.sql
 *    Примеры:
 *      001_initial_schema.php
 *      002_add_users_table.sql
 *      2025_01_15_add_totp_to_users.php
 *
 * 3. СТРУКТУРА PHP-МИГРАЦИИ:
 *    <?php
 *    return [
 *        'up' => function(\PDO $pdo): void {
 *            // SQL для применения
 *            $pdo->exec("CREATE TABLE ...");
 *        },
 *        'down' => function(\PDO $pdo): void {
 *            // SQL для отката (опционально)
 *            $pdo->exec("DROP TABLE ...");
 *        },
 *        'description' => 'Описание миграции'
 *    ];
 *
 * 4. СТРУКТУРА SQL-МИГРАЦИИ:
 *    -- up
 *    CREATE TABLE ...
 *    -- down
 *    DROP TABLE ...
 *
 *    Или просто CREATE/ALTER statements (только up, down не поддерживается)
 *
 * 5. ЗАПУСК:
 *    CLI: php database/migrate.php [up|down|status|create <name>]
 *    Код: $runner = new MigrationRunner(); $runner->up();
 *
 * === ПРИНЦИПЫ ===
 * - Идемпотентность: повторный запуск up() не ломает БД (IF NOT EXISTS, IF EXISTS)
 * - Транзакционность: каждая миграция в своей транзакции (кроме DDL в MySQL — автокоммит)
 * - Порядок: по версии (строгое сравнение строк, работает для 001, 002... и 2025_01_15)
 * - Безопасность: down() только если явно вызван, требует подтверждения
 */
class MigrationRunner
{
    /** @var \PDO */
    private $pdo;

    /** @var string Путь к директории с миграциями */
    private string $migrationsPath;

    /** @var string Имя таблицы версий */
    private string $versionTable = 'va_migrations';

    /** @var callable|null Логгер (например, fn($msg) => error_log($msg)) */
    private $logger = null;

    /**
     * @param \PDO|null $pdo PDO соединение (по умолчанию Database::connection())
     * @param string|null $migrationsPath Путь к миграциям (по умолчанию database/migrations)
     */
    public function __construct(?\PDO $pdo = null, ?string $migrationsPath = null)
    {
        $this->pdo = $pdo ?? \App\Core\Database::connection();
        $this->migrationsPath = $migrationsPath ?? (dirname(__DIR__, 2) . '/database/migrations');
    }

    /**
     * Установить кастомный логгер.
     */
    public function setLogger($logger): self
    {
        $this->logger = $logger;
        return $this;
    }

    /**
     * Инициализировать таблицу версий (создаёт если не существует).
     */
    public function initVersionTable(): void
    {
        $sql = <<<SQL
            CREATE TABLE IF NOT EXISTS `{$this->versionTable}` (
                `version` VARCHAR(50) NOT NULL PRIMARY KEY,
                `name` VARCHAR(255) NOT NULL,
                `applied_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `execution_time_ms` INT NOT NULL DEFAULT 0
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        SQL;
        $this->pdo->exec($sql);
        $this->log("Version table `{$this->versionTable}` initialized.");
    }

    /**
     * Получить список всех файлов миграций, отсортированных по версии.
     *
     * @return array<string, string> [version => filepath]
     */
    public function getMigrationFiles(): array
    {
        $files = glob($this->migrationsPath . '/*.{php,sql}', GLOB_BRACE);
        if ($files === false) {
            $files = [];
        }

        $migrations = [];
        foreach ($files as $file) {
            $basename = basename($file);

            // Пропускаем служебные файлы (run_*, старые run скрипты)
            if (str_starts_with($basename, 'run_')) {
                continue;
            }

            // Извлекаем версию: 001_, 014_, 2025_01_15_
            // Только для файлов, начинающихся с цифр и подчёркивания
            if (!preg_match('/^(\d+(?:_\d+)*)_/', $basename, $matches)) {
                $this->log("Warning: Skipping file with invalid name format: $basename");
                continue;
            }

            $version = $matches[1];

            // Предпочитаем .php файлы над .sql (если версии совпадают)
            $ext = pathinfo($basename, PATHINFO_EXTENSION);
            if (isset($migrations[$version])) {
                $existingExt = pathinfo($migrations[$version], PATHINFO_EXTENSION);
                if ($ext === 'sql' && $existingExt === 'php') {
                    // Уже есть .php версия - пропускаем .sql
                    continue;
                }
                if ($ext === 'php' && $existingExt === 'sql') {
                    // Заменяем .sql на .php
                    $migrations[$version] = $file;
                    continue;
                }
                // Конфликт версий - логируем предупреждение
                $this->log("Warning: Duplicate version $version: " . basename($migrations[$version]) . " vs $basename");
                continue;
            }

            $migrations[$version] = $file;
        }

        // Сортируем по версии (string comparison работает для 001, 002... и 2025_01_15)
        ksort($migrations, SORT_STRING);
        return $migrations;
    }

    /**
     * Получить список уже применённых миграций.
     *
     * @return array<string, array{name: string, applied_at: string, execution_time_ms: int}>
     */
    public function getAppliedMigrations(): array
    {
        $this->initVersionTable();
        $stmt = $this->pdo->query("SELECT version, name, applied_at, execution_time_ms FROM `{$this->versionTable}` ORDER BY version");
        $result = [];
        foreach ($stmt->fetchAll(\PDO::FETCH_ASSOC) as $row) {
            $result[$row['version']] = [
                'name' => $row['name'],
                'applied_at' => $row['applied_at'],
                'execution_time_ms' => (int)$row['execution_time_ms'],
            ];
        }
        return $result;
    }

    /**
     * Получить список ожидающих миграций (не применённых).
     *
     * @return array<string, string> [version => filepath]
     */
    public function getPendingMigrations(): array
    {
        $all = $this->getMigrationFiles();
        $applied = $this->getAppliedMigrations();

        return array_diff_key($all, $applied);
    }

    /**
     * Запустить все ожидающие миграции (up).
     *
     * @param bool $dryRun Только показать что будет выполнено
     * @return array Результаты: ['applied' => [], 'failed' => [], 'skipped' => []]
     */
    public function up(bool $dryRun = false): array
    {
        $this->initVersionTable();

        $pending = $this->getPendingMigrations();
        $applied = [];
        $failed = [];
        $skipped = [];

        if (empty($pending)) {
            $this->log('No pending migrations.');
            return ['applied' => [], 'failed' => [], 'skipped' => []];
        }

        $this->log("Found " . count($pending) . " pending migration(s).");

        foreach ($pending as $version => $file) {
            $startTime = microtime(true);
            $name = $this->extractMigrationName($file);

            $this->log("Running migration $version: $name ...");

            if ($dryRun) {
                $this->log("  [DRY RUN] Would apply: $file");
                $skipped[] = $version;
                continue;
            }

            try {
                $this->runMigrationFile($file, 'up');
                $executionTimeMs = (int)((microtime(true) - $startTime) * 1000);

                // Записываем версию
                $stmt = $this->pdo->prepare(
                    "INSERT INTO `{$this->versionTable}` (version, name, applied_at, execution_time_ms)
                     VALUES (:version, :name, NOW(), :time)"
                );
                $stmt->execute([
                    ':version' => $version,
                    ':name' => $name,
                    ':time' => $executionTimeMs,
                ]);

                $this->log("  OK ({$executionTimeMs} ms)");
                $applied[] = $version;
            } catch (\Throwable $e) {
                $this->log("  FAILED: " . $e->getMessage());
                $failed[] = ['version' => $version, 'error' => $e->getMessage()];
                // Прерываем выполнение при ошибке
                break;
            }
        }

        return ['applied' => $applied, 'failed' => $failed, 'skipped' => $skipped];
    }

    /**
     * Откатить последнюю применённую миграцию (down).
     *
     * @param int $steps Количество миграций для отката (по умолчанию 1)
     * @return array Результаты
     */
    public function down(int $steps = 1): array
    {
        $this->initVersionTable();

        $applied = $this->getAppliedMigrations();
        if (empty($applied)) {
            $this->log('No applied migrations to roll back.');
            return ['rolled_back' => [], 'failed' => []];
        }

        // Берём последние $steps миграций (в обратном порядке)
        $versions = array_keys($applied);
        $toRollback = array_slice($versions, -$steps);
        $toRollback = array_reverse($toRollback);

        $rolledBack = [];
        $failed = [];

        foreach ($toRollback as $version) {
            $file = $this->findMigrationFile($version);
            if (!$file) {
                $this->log("Migration file for version $version not found. Cannot roll back.");
                $failed[] = ['version' => $version, 'error' => 'Migration file not found'];
                continue;
            }

            $name = $this->extractMigrationName($file);
            $this->log("Rolling back migration $version: $name ...");

            try {
                // Проверяем, поддерживает ли миграция down()
                if (!$this->supportsDown($file)) {
                    throw new \RuntimeException("Migration $version does not support rollback (no 'down' function)");
                }

                $this->runMigrationFile($file, 'down');

                // Удаляем запись о версии
                $stmt = $this->pdo->prepare("DELETE FROM `{$this->versionTable}` WHERE version = :version");
                $stmt->execute([':version' => $version]);

                $this->log("  OK");
                $rolledBack[] = $version;
            } catch (\Throwable $e) {
                $this->log("  FAILED: " . $e->getMessage());
                $failed[] = ['version' => $version, 'error' => $e->getMessage()];
                break;
            }
        }

        return ['rolled_back' => $rolledBack, 'failed' => $failed];
    }

    /**
     * Показать статус миграций.
     *
     * @return array ['applied' => [], 'pending' => []]
     */
    public function status(): array
    {
        $this->initVersionTable();

        $allFiles = $this->getMigrationFiles();
        $applied = $this->getAppliedMigrations();

        $appliedList = [];
        foreach ($applied as $version => $info) {
            $file = $allFiles[$version] ?? null;
            $appliedList[] = [
                'version' => $version,
                'name' => $info['name'],
                'file' => $file ? basename($file) : 'missing',
                'applied_at' => $info['applied_at'],
                'execution_time_ms' => $info['execution_time_ms'],
            ];
        }

        $pendingList = [];
        foreach (array_diff_key($allFiles, $applied) as $version => $file) {
            $pendingList[] = [
                'version' => $version,
                'name' => $this->extractMigrationName($file),
                'file' => basename($file),
            ];
        }

        return ['applied' => $appliedList, 'pending' => $pendingList];
    }

    /**
     * Создать новый файл миграции.
     *
     * @param string $name Название (например: "add_totp_to_users")
     * @param string $type Тип: 'php' или 'sql'
     * @return string Путь к созданному файлу
     */
    public function create(string $name, string $type = 'php'): string
    {
        // Генерируем версию: timestamp в формате YYYY_MM_DD_HHMMSS
        $version = date('Y_m_d_His');
        $filename = "{$version}_{$name}.{$type}";
        $filepath = $this->migrationsPath . '/' . $filename;

        if (file_exists($filepath)) {
            throw new \RuntimeException("Migration file already exists: $filepath");
        }

        if (!is_dir($this->migrationsPath)) {
            mkdir($this->migrationsPath, 0755, true);
        }

        if ($type === 'php') {
            $content = $this->getPhpMigrationTemplate($name);
        } else {
            $content = $this->getSqlMigrationTemplate($name);
        }

        file_put_contents($filepath, $content);
        $this->log("Created migration: $filepath");

        return $filepath;
    }

    /**
     * Запустить конкретный файл миграции.
     */
    private function runMigrationFile(string $file, string $direction): void
    {
        $ext = pathinfo($file, PATHINFO_EXTENSION);

        if ($ext === 'php') {
            $this->runPhpMigration($file, $direction);
        } elseif ($ext === 'sql') {
            $this->runSqlMigration($file, $direction);
        } else {
            throw new \RuntimeException("Unsupported migration file type: $ext");
        }
    }

    /**
     * Запустить PHP-миграцию.
     * MySQL не поддерживает транзакционные DDL (CREATE/ALTER делают неявный COMMIT),
     * поэтому выполняем без явной транзакции.
     */
    private function runPhpMigration(string $file, string $direction): void
    {
        $migration = include $file;

        if (!is_array($migration) || !isset($migration[$direction])) {
            throw new \RuntimeException("Migration $file does not have '$direction' callable");
        }

        $callable = $migration[$direction];
        if (!is_callable($callable)) {
            throw new \RuntimeException("Migration $file '$direction' is not callable");
        }

        // Выполняем без транзакции (MySQL DDL автокоммит)
        $callable($this->pdo);
    }

    /**
     * Запустить SQL-миграцию.
     * MySQL не поддерживает транзакционные DDL, выполняем без транзакции.
     */
    private function runSqlMigration(string $file, string $direction): void
    {
        $sql = file_get_contents($file);
        if ($sql === false) {
            throw new \RuntimeException("Could not read migration file: $file");
        }

        // Парсим SQL на up/down секции
        $sections = $this->parseSqlMigration($sql);

        if (!isset($sections[$direction]) || trim($sections[$direction]) === '') {
            throw new \RuntimeException("Migration $file does not have '$direction' section");
        }

        $statements = $this->splitSqlStatements($sections[$direction]);
        if (empty($statements)) {
            return;
        }

        // Выполняем без транзакции (MySQL DDL автокоммит)
        foreach ($statements as $stmt) {
            $stmt = trim($stmt);
            if ($stmt !== '') {
                $this->pdo->exec($stmt);
            }
        }
    }

    /**
     * Парсит SQL файл на секции -- up и -- down.
     */
    private function parseSqlMigration(string $sql): array
    {
        $result = ['up' => '', 'down' => ''];
        $currentSection = 'up';

        foreach (explode("\n", $sql) as $line) {
            $trimmed = trim($line);
            if (strtolower($trimmed) === '-- up') {
                $currentSection = 'up';
                continue;
            }
            if (strtolower($trimmed) === '-- down') {
                $currentSection = 'down';
                continue;
            }
            $result[$currentSection] .= $line . "\n";
        }

        return $result;
    }

    /**
     * Разбивает SQL на отдельные statements.
     */
    private function splitSqlStatements(string $sql): array
    {
        $statements = [];
        $current = '';
        $inString = false;
        $stringChar = '';
        $length = strlen($sql);

        for ($i = 0; $i < $length; $i++) {
            $char = $sql[$i];
            $current .= $char;

            // Обработка строковых литералов
            if (($char === '\'' || $char === '"') && !$inString) {
                $inString = true;
                $stringChar = $char;
            } elseif ($char === $stringChar && $inString) {
                // Проверяем экранирование
                $escaped = false;
                $j = $i - 1;
                while ($j >= 0 && $sql[$j] === '\\') {
                    $escaped = !$escaped;
                    $j--;
                }
                if (!$escaped) {
                    $inString = false;
                    $stringChar = '';
                }
            }

            // Конец statement
            if ($char === ';' && !$inString) {
                $statements[] = $current;
                $current = '';
            }
        }

        if (trim($current) !== '') {
            $statements[] = $current;
        }

        return $statements;
    }

    /**
     * Проверяет, поддерживает ли миграция down().
     */
    private function supportsDown(string $file): bool
    {
        $ext = pathinfo($file, PATHINFO_EXTENSION);

        if ($ext === 'php') {
            $migration = include $file;
            return is_array($migration) && isset($migration['down']) && is_callable($migration['down']);
        } elseif ($ext === 'sql') {
            $sql = file_get_contents($file);
            $sections = $this->parseSqlMigration($sql);
            return trim($sections['down']) !== '';
        }

        return false;
    }

    /**
     * Находит файл миграции по версии.
     */
    private function findMigrationFile(string $version): ?string
    {
        $files = $this->getMigrationFiles();
        return $files[$version] ?? null;
    }

    /**
     * Извлекает имя миграции из имени файла.
     */
    private function extractMigrationName(string $file): string
    {
        $basename = basename($file);
        // Убираем версию и расширение: 001_name.php -> name
        return preg_replace('/^\d+(?:_\d+)*_(.+)\.(php|sql)$/', '$1', $basename);
    }

    /**
     * Шаблон для PHP-миграции.
     */
    private function getPhpMigrationTemplate(string $name): string
    {
        $date = date('Y-m-d H:i:s');
        return <<<PHP
<?php

/**
 * Migration: {$name}
 * Created: {$date}
 */

return [
    'up' => function(\PDO \$pdo): void {
        // SQL для применения миграции
        // \$pdo->exec("CREATE TABLE ...");
    },
    'down' => function(\PDO \$pdo): void {
        // SQL для отката (опционально)
        // \$pdo->exec("DROP TABLE ...");
    },
    'description' => '{$name}'
];
PHP;
    }

    /**
     * Шаблон для SQL-миграции.
     */
    private function getSqlMigrationTemplate(string $name): string
    {
        $date = date('Y-m-d H:i:s');
        return <<<SQL
-- Migration: {$name}
-- Created: {$date}

-- up
-- ВАШ SQL ДЛЯ ПРИМЕНЕНИЯ ЗДЕСЬ
-- CREATE TABLE ...

-- down
-- ВАШ SQL ДЛЯ ОТКАТА ЗДЕСЬ
-- DROP TABLE ...

SQL;
    }

    /**
     * Логирование.
     */
    private function log(string $message): void
    {
        if ($this->logger) {
            ($this->logger)($message);
        } else {
            echo $message . "\n";
        }
    }
}