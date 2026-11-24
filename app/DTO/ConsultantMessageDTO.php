<?php

declare(strict_types=1);

namespace App\DTO;

/**
 * ConsultantMessageDTO — сообщение в чате консультанта.
 *
 * === СТРУКТУРА ТАБЛИЦЫ va_consultant_messages ===
 * - id (BIGINT, PK, AI)
 * - user_id (CHAR 32) — FK на consultant_users.id
 * - role (VARCHAR 8) — 'user' или 'admin'
 * - message (TEXT) — текст сообщения
 * - created_at (DATETIME)
 */
final class ConsultantMessageDTO extends AbstractDTO
{
    /** @var int ID сообщения */
    public int $id = 0;

    /** @var string ID пользователя */
    public string $userId = '';

    /** @var string Роль: user/admin */
    public string $role = 'user';

    /** @var string Текст сообщения */
    public string $text = '';

    /** @var string Время создания (ISO 8601) */
    public string $createdAt = '';

    /**
     * Создаёт DTO из строки БД.
     *
     * @param array<string, mixed> $row
     * @return static
     */
    public static function fromDatabase(array $row): static
    {
        $dto = new self();
        $dto->id = isset($row['id']) ? (int) $row['id'] : 0;
        $dto->userId = $row['user_id'] ?? $row['userId'] ?? '';
        $dto->role = $row['role'] ?? 'user';
        $dto->text = $row['message'] ?? $row['text'] ?? '';
        $dto->createdAt = $row['created_at'] ?? $row['createdAt'] ?? '';

        return $dto;
    }

    /**
     * Массив для INSERT/UPDATE в БД.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'user_id' => $this->userId,
            'role' => $this->role,
            'message' => $this->text,
        ];
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
            'userId' => $this->userId,
            'role' => $this->role,
            'text' => $this->text,
            'createdAt' => $this->createdAt,
        ];
    }
}
