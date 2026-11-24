<?php

declare(strict_types=1);

namespace App\DTO;

/**
 * UserActivityDTO — объект переноса данных для активности пользователей.
 *
 * === СТРУКТУРА ТАБЛИЦЫ va_user_activities ===
 * - id (INT, PK, AI)
 * - user_id (INT, FK → va_users.id, NULL для гостей)
 * - page_url (VARCHAR 500) — URL страницы
 * - page_title (VARCHAR 500) — заголовок страницы
 * - ip_address (VARCHAR 45) — IPv4/IPv6
 * - user_agent (TEXT) — User-Agent строка
 * - referrer (VARCHAR 500, NULL) — реферер
 * - visited_at (TIMESTAMP) — время визита
 * - browser (VARCHAR 32, виртуальная) — из CASE user_agent
 * - section (VARCHAR 32, виртуальная) — из CASE page_url
 */
final class UserActivityDTO
{
    /** @var int|null ID записи (NULL для новой) */
    public ?int $id = null;

    /** @var int|null ID пользователя (NULL для гостей) */
    public ?int $userId = null;

    /** @var string URL страницы */
    public string $pageUrl = '';

    /** @var string Заголовок страницы */
    public string $pageTitle = '';

    /** @var string IP адрес */
    public string $ipAddress = '';

    /** @var string User-Agent */
    public string $userAgent = '';

    /** @var string|null Реферер */
    public ?string $referrer = null;

    /** @var string Время визита (Y-m-d H:i:s) */
    public string $visitedAt = '';

    /** @var string|null Браузер (виртуальное поле) */
    public ?string $browser = null;

    /** @var string|null Раздел (виртуальное поле) */
    public ?string $section = null;

    /**
     * Создаёт DTO из строки БД.
     *
     * @param array<string, mixed> $row
     */
    public static function fromDatabase(array $row): self
    {
        $dto = new self();
        $dto->id = isset($row['id']) ? (int)$row['id'] : null;
        $dto->userId = $row['user_id'] !== null ? (int)$row['user_id'] : null;
        $dto->pageUrl = $row['page_url'] ?? '';
        $dto->pageTitle = $row['page_title'] ?? '';
        $dto->ipAddress = $row['ip_address'] ?? '';
        $dto->userAgent = $row['user_agent'] ?? '';
        $dto->referrer = $row['referrer'] ?? null;
        $dto->visitedAt = $row['visited_at'] ?? '';
        $dto->browser = $row['browser'] ?? null;
        $dto->section = $row['section'] ?? null;

        return $dto;
    }

    /**
     * Создаёт DTO для записи нового визита.
     *
     * @param int|null $userId
     * @param string $pageUrl
     * @param string $pageTitle
     * @param string $ipAddress
     * @param string $userAgent
     * @param string|null $referrer
     */
    public static function create(
        ?int $userId,
        string $pageUrl,
        string $pageTitle,
        string $ipAddress,
        string $userAgent,
        ?string $referrer = null
    ): self {
        $dto = new self();
        $dto->userId = $userId;
        $dto->pageUrl = $pageUrl;
        $dto->pageTitle = $pageTitle;
        $dto->ipAddress = $ipAddress;
        $dto->userAgent = $userAgent;
        $dto->referrer = $referrer;

        return $dto;
    }

    /**
     * Массив для INSERT в БД.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'user_id' => $this->userId,
            'page_url' => $this->pageUrl,
            'page_title' => $this->pageTitle,
            'ip_address' => $this->ipAddress,
            'user_agent' => $this->userAgent,
            'referrer' => $this->referrer,
        ];
    }

    /**
     * Массив для API ответа.
     *
     * @return array<string, mixed>
     */
    public function toApiArray(): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->userId,
            'page_url' => $this->pageUrl,
            'page_title' => $this->pageTitle,
            'ip_address' => $this->ipAddress,
            'user_agent' => $this->userAgent,
            'referrer' => $this->referrer,
            'visited_at' => $this->visitedAt,
            'browser' => $this->browser,
            'section' => $this->section,
        ];
    }
}
