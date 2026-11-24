<?php

/**
 * CLI скрипт для запуска миграций БД.
 *
 * ИСПОЛЬЗОВАНИЕ:
 *   php database/migrate.php              # Показать статус
 *   php database/migrate.php up           # Применить все ожидающие миграции
 *   php database/migrate.php up --dry-run # Показать что будет применено (не выполнять)
 *   php database/migrate.php down         # Откатить последнюю миграцию
 *   php database/migrate.php down 3       # Откатить последние 3 миграции
 *   php database/migrate.php status       # Показать статус (применённые/ожидающие)
 *   php database/migrate.php create "add_field_to_table" [--type=php|sql]
 *
 * ПЕРЕМЕННЫЕ ОКРУЖЕНИЯ (.env):
 *   DB_HOST, DB_PORT, DB_NAME, DB_USER, DB_PASS, DB_PREFIX
 */

define('APP_ROOT', dirname(__DIR__));
require APP_ROOT . '/app/Core/Database.php';
require APP_ROOT . '/app/Core/MigrationRunner.php';

use App\Core\Database;
use App\Core\MigrationRunner;

// Парсим аргументы
$args = $argv;
array_shift($args); // удаляем имя скрипта

$command = $args[0] ?? 'status';
$options = [];
$positionalArgs = [];

foreach ($args as $arg) {
    if (str_starts_with($arg, '--')) {
        $parts = explode('=', $arg, 2);
        $key = ltrim($parts[0], '--');
        $value = $parts[1] ?? true;
        $options[$key] = $value;
    } else {
        $positionalArgs[] = $arg;
    }
}

try {
    $pdo = Database::connection();
    $runner = new MigrationRunner($pdo);

    // Логгер с префиксом времени
    $runner->setLogger(function (string $msg) {
        echo "[" . date('H:i:s') . "] $msg\n";
    });

    switch ($command) {
        case 'up':
            $dryRun = isset($options['dry-run']) || isset($options['dry_run']);
            $result = $runner->up($dryRun);

            if ($dryRun) {
                echo "\nDRY RUN completed. No changes made.\n";
                exit(0);
            }

            if (!empty($result['applied'])) {
                echo "\nApplied " . count($result['applied']) . " migration(s): " . implode(', ', $result['applied']) . "\n";
            }
            if (!empty($result['failed'])) {
                echo "\nFAILED: " . json_encode($result['failed'], JSON_UNESCAPED_UNICODE) . "\n";
                exit(1);
            }
            if (empty($result['applied']) && empty($result['failed'])) {
                echo "\nNo migrations to apply.\n";
            }
            break;

        case 'down':
            $steps = 1;
            if (isset($positionalArgs[1]) && is_numeric($positionalArgs[1])) {
                $steps = (int)$positionalArgs[1];
            }
            if ($steps < 1) {
                echo "Error: steps must be >= 1\n";
                exit(1);
            }

            echo "Rolling back $steps migration(s)...\n";
            $result = $runner->down($steps);

            if (!empty($result['rolled_back'])) {
                echo "\nRolled back " . count($result['rolled_back']) . " migration(s): " . implode(', ', $result['rolled_back']) . "\n";
            }
            if (!empty($result['failed'])) {
                echo "\nFAILED: " . json_encode($result['failed'], JSON_UNESCAPED_UNICODE) . "\n";
                exit(1);
            }
            break;

        case 'status':
            $status = $runner->status();

            echo "\n=== APPLIED MIGRATIONS (" . count($status['applied']) . ") ===\n";
            if (empty($status['applied'])) {
                echo "  (none)\n";
            } else {
                foreach ($status['applied'] as $m) {
                    $time = $m['execution_time_ms'] > 0 ? " ({$m['execution_time_ms']} ms)" : '';
                    echo "  ✓ {$m['version']}  {$m['name']}  [{$m['file']}]  {$m['applied_at']}$time\n";
                }
            }

            echo "\n=== PENDING MIGRATIONS (" . count($status['pending']) . ") ===\n";
            if (empty($status['pending'])) {
                echo "  (none)\n";
            } else {
                foreach ($status['pending'] as $m) {
                    echo "  ○ {$m['version']}  {$m['name']}  [{$m['file']}]\n";
                }
            }
            echo "\n";
            break;

        case 'create':
            $name = $positionalArgs[1] ?? '';
            if ($name === '') {
                echo "Error: migration name required\n";
                echo "Usage: php database/migrate.php create \"add_field_to_table\" [--type=php|sql]\n";
                exit(1);
            }
            $type = $options['type'] ?? 'php';
            if (!in_array($type, ['php', 'sql'], true)) {
                echo "Error: type must be 'php' or 'sql'\n";
                exit(1);
            }

            $filepath = $runner->create($name, $type);
            echo "Created: $filepath\n";
            break;

        case 'init':
            // Просто инициализирует таблицу версий
            $runner->initVersionTable();
            echo "Version table initialized.\n";
            break;

        default:
            echo "Unknown command: $command\n";
            echo "Usage:\n";
            echo "  php database/migrate.php [up|down|status|create|init]\n";
            exit(1);
    }
} catch (\Throwable $e) {
    echo "\nERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}