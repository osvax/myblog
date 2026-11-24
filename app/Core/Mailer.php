<?php

declare(strict_types=1);

namespace App\Core;

use App\Models\SiteSettings;
use Symfony\Component\Mailer\Mailer as SymfonyMailer;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

final class Mailer
{
    private static ?SymfonyMailer $instance = null;
    private static ?array $config = null;

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
     * Получить конфигурацию SMTP из настроек сайта или .env
     */
    private static function getConfig(): array
    {
        if (self::$config !== null) {
            return self::$config;
        }

        $settings = new SiteSettings();

        // Сначала пробуем из БД (админка), затем из .env
        self::$config = [
            // Тумблер «Отправлять письма через SMTP»; если снят — обычная PHP mail()
            'use_smtp' => (bool) ($settings->get('smtpEnabled') ?? true),
            'host' => $settings->get('smtpHost') ?? $_ENV['MAIL_HOST'] ?? 'localhost',
            'port' => (int) ($settings->get('smtpPort') ?? $_ENV['MAIL_PORT'] ?? 25),
            'username' => $settings->get('smtpUsername') ?? $_ENV['MAIL_USERNAME'] ?? null,
            'password' => $settings->get('smtpPassword') ?? $_ENV['MAIL_PASSWORD'] ?? null,
            'encryption' => $settings->get('smtpEncryption') ?? $_ENV['MAIL_ENCRYPTION'] ?? null,
            'from_address' => $settings->get('smtpFromEmail') ?? $_ENV['MAIL_FROM_ADDRESS'] ?? 'no-reply@myblog.loc',
            'from_name' => $settings->get('smtpFromName') ?? $_ENV['MAIL_FROM_NAME'] ?? 'МойБлог',
            'reply_to' => $settings->get('smtpReplyTo') ?? '',
        ];

        return self::$config;
    }

    /**
     * Получить экземпляр Symfony Mailer (ленивая инициализация)
     */
    private static function getMailer(): SymfonyMailer
    {
        if (self::$instance !== null) {
            return self::$instance;
        }

        $config = self::getConfig();
        $dsn = self::buildDsn($config);
        $transport = Transport::fromDsn($dsn);
        self::$instance = new SymfonyMailer($transport);

        return self::$instance;
    }

    /**
     * Построить DSN для Symfony Mailer.
     * Поддерживаются только схемы smtp:// (STARTTLS подхватывается
     * автоматически, если сервер его поддерживает) и smtps:// (имплицитный TLS).
     * Префиксы «smtp+tls»/«smtp+ssl» библиотекой не поддерживаются.
     */
    private static function buildDsn(array $config): string
    {
        $encryption = strtolower(trim((string) $config['encryption']));
        $scheme = ($encryption === 'ssl') ? 'smtps' : 'smtp';

        $dsn = $scheme . '://';

        if (!empty($config['username'])) {
            $dsn .= urlencode($config['username']);
            if (!empty($config['password'])) {
                $dsn .= ':' . urlencode($config['password']);
            }
            $dsn .= '@';
        }

        $dsn .= $config['host'] . ':' . $config['port'];

        if ($encryption === 'none') {
            // Полностью без шифрования: отключаем автоматический STARTTLS
            $dsn .= '?auto_tls=0';
        }

        return $dsn;
    }

    /**
     * Отправить письмо через PHP mail() (запасной канал, когда SMTP выключен).
     */
    private static function sendViaPhpMail(string $subject, string $body, string $toEmail, string $toName, ?string $htmlBody, array $config): bool
    {
        $fromAddress = (string) $config['from_address'];
        $fromName = str_replace(["\r", "\n"], '', (string) $config['from_name']);
        $headers = 'MIME-Version: 1.0' . "\r\n";

        if ($htmlBody !== null) {
            $headers .= 'Content-Type: text/html; charset=UTF-8' . "\r\n";
        } else {
            $headers .= 'Content-Type: text/plain; charset=UTF-8' . "\r\n";
        }

        if (filter_var($fromAddress, FILTER_VALIDATE_EMAIL)) {
            $headers .= 'From: ' . ($fromName !== '' ? $fromName . ' <' . $fromAddress . '>' : $fromAddress) . "\r\n";
        }

        $replyTo = trim((string) $config['reply_to']);
        if (filter_var($replyTo, FILTER_VALIDATE_EMAIL)) {
            $headers .= 'Reply-To: ' . $replyTo . "\r\n";
        }

        $content = $htmlBody !== null ? $htmlBody : $body;
        $sent = @mail($toEmail, $subject, $content, $headers);
        if (!$sent) {
            self::logError('PHP mail() failed for ' . $toEmail);
        }
        return $sent;
    }

    /**
     * Отправить письмо
     *
     * @param string $subject
     * @param string $body (plain text)
     * @param string $toEmail
     * @param string $toName
     * @param string|null $htmlBody (optional HTML version)
     * @return bool
     */
    public static function send(string $subject, string $body, string $toEmail, string $toName = '', ?string $htmlBody = null): bool
    {
        try {
            $config = self::getConfig();

            if (!$config['use_smtp']) {
                return self::sendViaPhpMail($subject, $body, $toEmail, $toName, $htmlBody, $config);
            }

            $mailer = self::getMailer();

            // Symfony Mime 7: from()/to() принимают список адресов; имя —
            // только через new Address($email, $name)
            $email = (new Email())
                ->from(new Address($config['from_address'], $config['from_name']))
                ->to(new Address($toEmail, $toName))
                ->subject($subject)
                ->text($body);

            if ($htmlBody !== null) {
                $email->html($htmlBody);
            }

            $mailer->send($email);
            return true;
        } catch (\Throwable $e) {
            self::logError('Mailer error: ' . $e->getMessage());
            // Фиксируем в журнале событий: без этого письмо «теряется» молча
            \App\Core\Logger::write('errors', 'Не удалось отправить письмо на ' . $toEmail . ': ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Проверить канал отправки (SMTP-подключение или PHP mail()).
     * Каждый неудачный тест пишется в журнал событий (раздел «Ошибки»),
     * в ответ возвращается понятное объяснение и тестируемое подключение.
     */
    public static function testConnection(): array
    {
        $config = self::getConfig();
        // Подключение для отчёта (пароль не показываем)
        $encryption = strtolower(trim((string) $config['encryption']));
        $reportDsn = ($encryption === 'ssl' ? 'smtps' : 'smtp') . '://'
            . (!empty($config['username']) ? $config['username'] . ':(пароль скрыт)@' : '')
            . $config['host'] . ':' . $config['port'];

        try {
            if (!$config['use_smtp']) {
                // PHP mail(): отправляем тест на email отправителя
                $target = (string) $config['from_address'];
                if (!filter_var($target, FILTER_VALIDATE_EMAIL)) {
                    $error = 'Укажите корректный email отправителя, на который отправим тест.';
                    \App\Core\Logger::write('errors', 'Тест отправки (PHP mail) не выполнен: ' . $error);
                    return ['success' => false, 'error' => $error];
                }
                $sent = self::sendViaPhpMail(
                    'Тест отправки — МойБлог',
                    'Это тестовое письмо для проверки отправки через PHP mail().',
                    $target,
                    'Test',
                    null,
                    $config
                );
                if ($sent) {
                    return ['success' => true, 'message' => 'Тестовое письмо отправлено через PHP mail() на ' . $target];
                }
                $error = 'PHP mail() не смог отправить письмо. Проверьте почтовый демон сервера (sendmail/postfix).';
                \App\Core\Logger::write('errors', 'Тест отправки (PHP mail) не прошёл: mail() вернул false для ' . $target);
                return ['success' => false, 'error' => $error];
            }

            $transport = Transport::fromDsn(self::buildDsn($config));
            $mailer = new SymfonyMailer($transport);

            // Отправляем тестовое письмо на адрес отправителя
            $email = (new Email())
                ->from(new Address($config['from_address'], $config['from_name']))
                ->to(new Address($config['from_address'], 'МойБлог'))
                ->subject('SMTP Test - МойБлог')
                ->text('Это тестовое письмо для проверки SMTP настроек.');

            $mailer->send($email);
            return ['success' => true, 'message' => 'Тестовое письмо отправлено успешно через SMTP на ' . $config['from_address']];
        } catch (\Throwable $e) {
            $error = self::explainSmtpError($e->getMessage()) . ' Проверка подключения: ' . $reportDsn;
            \App\Core\Logger::write('errors', 'Тест отправки (SMTP) не прошёл: ' . $e->getMessage() . ' (' . $reportDsn . ')');
            return ['success' => false, 'error' => $error];
        }
    }

    /**
     * Перевести типичные ошибки SMTP на понятный язык.
     */
    private static function explainSmtpError(string $raw): string
    {
        $lower = strtolower($raw);
        if (strpos($lower, '535') !== false || stripos($raw, 'authenticat') !== false || stripos($raw, 'login') !== false) {
            return 'Ошибка авторизации: проверьте имя пользователя и пароль приложения. Подробности: ' . $raw;
        }
        if (strpos($lower, '553') !== false || stripos($raw, 'sender') !== false) {
            return 'Сервер отклонил адрес отправителя («Email отправителя»): он должен совпадать с логином SMTP. Подробности: ' . $raw;
        }
        if (stripos($raw, 'connect') !== false || stripos($raw, 'refused') !== false
            || stripos($raw, 'timed out') !== false || stripos($raw, 'connection') !== false) {
            return 'Не удалось подключиться к SMTP-серверу: проверьте адрес сервера и порт (587 = STARTTLS, 465 = SSL/TLS). Подробности: ' . $raw;
        }
        if (stripos($raw, 'tls') !== false || stripos($raw, 'ssl') !== false || stripos($raw, 'certificate') !== false) {
            return 'Ошибка шифрования: проверьте «Шифрование» и порт (587 = STARTTLS, 465 = SSL/TLS). Подробности: ' . $raw;
        }
        if (stripos($raw, 'addr-spec') !== false || stripos($raw, 'rfc 2822') !== false) {
            return 'Некорректный email-адрес: проверьте «Email отправителя» (должен иметь вид name@domain). Подробности: ' . $raw;
        }
        return $raw;
    }
}
