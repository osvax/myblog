<?php

namespace App\Core;

use Symfony\Component\Mailer\Mailer as SymfonyMailer;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mime\Email;
use App\Models\SiteSettings;

class Mailer
{
    private static ?SymfonyMailer $instance = null;
    private static ?array $config = null;

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
            'host' => $settings->get('smtpHost') ?? $_ENV['MAIL_HOST'] ?? 'localhost',
            'port' => (int) ($settings->get('smtpPort') ?? $_ENV['MAIL_PORT'] ?? 25),
            'username' => $settings->get('smtpUsername') ?? $_ENV['MAIL_USERNAME'] ?? null,
            'password' => $settings->get('smtpPassword') ?? $_ENV['MAIL_PASSWORD'] ?? null,
            'encryption' => $settings->get('smtpEncryption') ?? $_ENV['MAIL_ENCRYPTION'] ?? null,
            'from_address' => $settings->get('smtpFromEmail') ?? $_ENV['MAIL_FROM_ADDRESS'] ?? 'no-reply@myblog.loc',
            'from_name' => $settings->get('smtpFromName') ?? $_ENV['MAIL_FROM_NAME'] ?? 'МойБлог',
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
     * Построить DSN для Symfony Mailer
     */
    private static function buildDsn(array $config): string
    {
        $scheme = 'smtp';
        if (!empty($config['encryption'])) {
            $scheme .= '+' . $config['encryption'];
        }

        $dsn = $scheme . '://';

        if (!empty($config['username'])) {
            $dsn .= urlencode($config['username']);
            if (!empty($config['password'])) {
                $dsn .= ':' . urlencode($config['password']);
            }
            $dsn .= '@';
        }

        $dsn .= $config['host'] . ':' . $config['port'];

        return $dsn;
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
    public static function send(string $subject, string $body, string $toEmail, string $toName = '', string $htmlBody = null): bool
    {
        try {
            $config = self::getConfig();
            $mailer = self::getMailer();

            $email = (new Email())
                ->from($config['from_address'], $config['from_name'])
                ->to($toEmail, $toName)
                ->subject($subject)
                ->text($body);

            if ($htmlBody !== null) {
                $email->html($htmlBody);
            }

            $mailer->send($email);
            return true;
        } catch (\Throwable $e) {
            error_log('Mailer error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Проверить подключение к SMTP
     */
    public static function testConnection(): array
    {
        try {
            $config = self::getConfig();
            $transport = Transport::fromDsn(self::buildDsn($config));
            $mailer = new SymfonyMailer($transport);

            // Пробуем отправить тестовое письмо на тот же адрес
            $email = (new Email())
                ->from($config['from_address'], $config['from_name'])
                ->to($config['from_address'], 'Test')
                ->subject('SMTP Test - МойБлог')
                ->text('Это тестовое письмо для проверки SMTP настроек.');

            $mailer->send($email);
            return ['success' => true, 'message' => 'Тестовое письмо отправлено успешно'];
        } catch (\Throwable $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}