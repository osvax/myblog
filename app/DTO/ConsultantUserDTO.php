<?php

declare(strict_types=1);

namespace App\DTO;

/**
 * ConsultantUserDTO — пользователь (обращение) консультанта.
 *
 * === СТРУКТУРА ТАБЛИЦЫ va_consultant_users ===
 * - id (CHAR 32, PK) — уникальный ID (hex)
 * - token_hash (CHAR 64) — SHA256 токена
 * - name (VARCHAR 100) — имя пользователя
 * - phone (VARCHAR 40) — телефон
 * - source (VARCHAR 500) — источник обращения
 * - status (VARCHAR 16) — статус: new, answered, closed
 * - created_at (DATETIME)
 * - updated_at (DATETIME)
 */
final class ConsultantUserDTO
{
    /** @var string ID (32 hex chars) */
    public string $id = '';

    /** @var string Имя */
    public string $name = '';

    /** @var string Телефон */
    public string $phone = '';

    /** @var string Источник */
    public string $source = '';

    /** @var string Статус: new, answered, closed */
    public string $status = 'new';

    /** @var string Время создания */
    public string $createdAt = '';

    /** @var string Время обновления */
    public string $updatedAt = '';

    /** @var string|null Последнее сообщение */
    public ?string $lastMessage = null;

    /** @var int|null ID последнего сообщения */
    public ?int $lastMessageId = null;

    /** @var string|null Роль последнего сообщения */
    public ?string $lastMessageRole = null;

    /** @var int|null ID последнего сообщения пользователя */
    public ?int $lastUserMessageId = null;

    /** @var int|null ID сообщения, просмотренного админом */
    public ?int $viewedByAdminMessageId = null;

    /** @var int|null ID сообщения, прочитанного админом */
    public ?int $readByAdminMessageId = null;

    /** @var int|null ID сообщения, просмотренного пользователем */
    public ?int $viewedByUserMessageId = null;

    /** @var int|null ID сообщения, прочитанного пользователем */
    public ?int $readByUserMessageId = null;

    /** @var string|null Роль печатающего */
    public ?string $typingRole = null;

    /**
     * Создаёт DTO из строки БД.
     *
     * @param array<string, mixed> $row
     */
    public static function fromDatabase(array $row): self
    {
        $dto = new self();
        $dto->id = $row['id'] ?? '';
        $dto->name = $row['name'] ?? '';
        $dto->phone = $row['phone'] ?? '';
        $dto->source = $row['source'] ?? '';
        $dto->status = $row['status'] ?? 'new';
        $dto->createdAt = $row['created_at'] ?? '';
        $dto->updatedAt = $row['updated_at'] ?? '';
        $dto->lastMessage = $row['lastMessage'] ?? null;
        $dto->lastMessageId = isset($row['lastMessageId']) ? (int) $row['lastMessageId'] : null;
        $dto->lastMessageRole = $row['lastMessageRole'] ?? null;
        $dto->lastUserMessageId = isset($row['lastUserMessageId']) ? (int) $row['lastUserMessageId'] : null;
        $dto->viewedByAdminMessageId = isset($row['viewedByAdminMessageId']) ? (int) $row['viewedByAdminMessageId'] : null;
        $dto->readByAdminMessageId = isset($row['readByAdminMessageId']) ? (int) $row['readByAdminMessageId'] : null;
        $dto->viewedByUserMessageId = isset($row['viewedByUserMessageId']) ? (int) $row['viewedByUserMessageId'] : null;
        $dto->readByUserMessageId = isset($row['readByUserMessageId']) ? (int) $row['readByUserMessageId'] : null;
        $dto->typingRole = $row['typingRole'] ?? null;

        return $dto;
    }

    /**
     * Массив для API ответа / JSON.
     *
     * @return array<string, mixed>
     */
    public function toApiArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'phone' => $this->phone,
            'source' => $this->source,
            'status' => $this->status,
            'createdAt' => $this->createdAt,
            'updatedAt' => $this->updatedAt,
            'lastMessage' => $this->lastMessage,
            'lastMessageId' => $this->lastMessageId,
            'lastMessageRole' => $this->lastMessageRole,
            'lastUserMessageId' => $this->lastUserMessageId,
            'viewedByAdminMessageId' => $this->viewedByAdminMessageId,
            'readByAdminMessageId' => $this->readByAdminMessageId,
            'viewedByUserMessageId' => $this->viewedByUserMessageId,
            'readByUserMessageId' => $this->readByUserMessageId,
            'typingRole' => $this->typingRole,
        ];
    }
}
