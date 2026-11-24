<?php

namespace App\Core;

use Monolog\Logger as MonologLogger;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Formatter\JsonFormatter;
use Monolog\Level;

/**
 * ApplicationLogger — обёртка над Monolog для файловых JSON-логов.
 *
 * Отвечает за логирование ошибок приложения, исключений и отладочной информации
 * в файлы storage/logs/app-YYYY-MM-DD.json.
 *
 * Отличается от Logger (который пишет в БД для админки):
 * - Пишет в файлы, а не в БД
 * - JSON-формат для удобства парсинга (ELK, Loki и т.д.)
 * - Не зависит от настроек сайта (всегда активен)
 * - Поддерживает уровни PSR-3: debug, info, notice, warning, error, critical, alert, emergency
 */
class ApplicationLogger
{
    private MonologLogger $logger;

    public function __construct()
    {
        $logDir = APP_ROOT . '/storage/logs';
        if (!is_dir($logDir)) {
            @mkdir($logDir, 0775, true);
        }

        $this->logger = new MonologLogger('myblog');

        // RotatingFileHandler: ежедневная ротация, хранение 30 дней
        $handler = new RotatingFileHandler(
            $logDir . '/app.log',
            30,           // хранить 30 файлов
            Level::Debug, // минимальный уровень
            true,         // bubble
            0775          // file permissions
        );

        // JSON formatter для структурированных логов
        $formatter = new JsonFormatter(
            JsonFormatter::BATCH_MODE_JSON, // pretty print отключен для компактности
            JsonFormatter::BATCH_MODE_JSON
        );
        $formatter->setDateFormat('c'); // ISO 8601
        $handler->setFormatter($formatter);

        $this->logger->pushHandler($handler);
    }

    /**
     * Получить внутренний Monolog-логгер (для продвинутых случаев).
     */
    public function getLogger(): MonologLogger
    {
        return $this->logger;
    }

    // === PSR-3 методы ===

    public function emergency(string $message, array $context = []): void
    {
        $this->logger->emergency($message, $this->enrichContext($context));
    }

    public function alert(string $message, array $context = []): void
    {
        $this->logger->alert($message, $this->enrichContext($context));
    }

    public function critical(string $message, array $context = []): void
    {
        $this->logger->critical($message, $this->enrichContext($context));
    }

    public function error(string $message, array $context = []): void
    {
        $this->logger->error($message, $this->enrichContext($context));
    }

    public function warning(string $message, array $context = []): void
    {
        $this->logger->warning($message, $this->enrichContext($context));
    }

    public function notice(string $message, array $context = []): void
    {
        $this->logger->notice($message, $this->enrichContext($context));
    }

    public function info(string $message, array $context = []): void
    {
        $this->logger->info($message, $this->enrichContext($context));
    }

    public function debug(string $message, array $context = []): void
    {
        $this->logger->debug($message, $this->enrichContext($context));
    }

    /**
     * Логирование исключения с полным стеком.
     */
    public function logException(\Throwable $e, string $message = '', array $context = []): void
    {
        $context['exception'] = [
            'class' => get_class($e),
            'message' => $e->getMessage(),
            'code' => $e->getCode(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString(),
        ];

        $this->error($message ?: $e->getMessage(), $context);
    }

    /**
     * Обогащает контекст общими данными запроса.
     */
    private function enrichContext(array $context): array
    {
        // Добавляем request_id если есть (для трейсинга)
        if (empty($context['request_id']) && isset($_SERVER['HTTP_X_REQUEST_ID'])) {
            $context['request_id'] = $_SERVER['HTTP_X_REQUEST_ID'];
        }

        // Базовые данные запроса
        if (!isset($context['url'])) {
            $context['url'] = ($_SERVER['REQUEST_URI'] ?? '');
        }
        if (!isset($context['method'])) {
            $context['method'] = ($_SERVER['REQUEST_METHOD'] ?? '');
        }
        if (!isset($context['ip'])) {
            $context['ip'] = self::clientIp();
        }
        if (!isset($context['user_agent'])) {
            $context['user_agent'] = ($_SERVER['HTTP_USER_AGENT'] ?? '');
        }

        // Сессия админа (если авторизован) — аккуратная проверка без сайд-эффектов
        if (isset($_SESSION['admin_authenticated']) && $_SESSION['admin_authenticated'] === true) {
            $context['admin_user_id'] = $_SESSION['admin_user_id'] ?? 0;
            $context['admin_email'] = $_SESSION['admin_email'] ?? '';
        }

        return $context;
    }

    /**
     * IP клиента (как в Logger).
     */
    private static function clientIp(): string
    {
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = trim(explode(',', (string) $_SERVER['HTTP_X_FORWARDED_FOR'])[0]);
            if (filter_var($ip, FILTER_VALIDATE_IP)) {
                return $ip;
            }
        }
        return (string) ($_SERVER['REMOTE_ADDR'] ?? '');
    }
}