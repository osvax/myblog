<?php

namespace App\Models;

use PDO;
use App\Core\QueryBuilder;
use App\DTO\MessageDTO;

class MessageRepository
{
    private $db;

    public function __construct(PDO $db = null)
    {
        $this->db = $db ?? \App\Core\Database::connection();
    }

    /**
     * Базовый QueryBuilder для messages.
     */
    private function baseQuery(): QueryBuilder
    {
        return new QueryBuilder($this->db, 'messages');
    }

    /**
     * Список всех сообщений (для обратной совместимости — возвращает массивы).
     *
     * @return array
     */
    public function messages(): array
    {
        $rows = $this->baseQuery()
            ->select(['*'])
            ->orderBy('created_at', 'DESC')
            ->orderBy('id', 'DESC')
            ->get();

        return $rows;
    }

    /**
     * Сообщение по ID (для обратной совместимости — возвращает массив).
     *
     * @param int $id
     * @return array|null
     */
    public function messageById(int $id): ?array
    {
        $row = $this->baseQuery()
            ->select(['*'])
            ->where('id', '=', $id)
            ->first();

        return $row ?: null;
    }

    /**
     * Список сообщений как массив MessageDTO (новый API).
     *
     * @return MessageDTO[]
     */
    public function messagesAsDto(): array
    {
        $rows = $this->baseQuery()
            ->select(['*'])
            ->orderBy('created_at', 'DESC')
            ->orderBy('id', 'DESC')
            ->get();

        return array_map([MessageDTO::class, 'fromDatabase'], $rows);
    }

    /**
     * Сообщение по ID как MessageDTO (новый API).
     *
     * @param int $id
     * @return MessageDTO|null
     */
    public function messageDtoById(int $id): ?MessageDTO
    {
        $row = $this->baseQuery()
            ->select(['*'])
            ->where('id', '=', $id)
            ->first();

        return $row ? MessageDTO::fromDatabase($row) : null;
    }

    /**
     * Сохраняет сообщение из MessageDTO (новый предпочтительный способ).
     *
     * @param MessageDTO $dto
     * @return int ID сохранённого сообщения
     */
    public function saveMessageFromDTO(MessageDTO $dto): int
    {
        // Валидируем DTO перед сохранением
        if (!$dto->validate()) {
            throw new \InvalidArgumentException('MessageDTO validation failed: ' . json_encode($dto->getErrors()));
        }

        $data = $dto->toArray();
        $table = db_table('messages');

        if ($dto->id === null) {
            // INSERT — новое сообщение
            unset($data['id']);
            $columns = array_keys($data);
            $placeholders = implode(',', array_fill(0, count($columns), '?'));
            $colList = implode(',', $columns);

            $sql = "INSERT INTO $table ($colList) VALUES ($placeholders)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(array_values($data));

            return (int)$this->db->lastInsertId();
        } else {
            // UPDATE — существующее сообщение (обычно только статусы)
            $updateParts = [];
            foreach ($data as $col => $val) {
                if ($col !== 'id') {
                    $updateParts[] = "$col = ?";
                }
            }
            $sql = "UPDATE $table SET " . implode(', ', $updateParts) . " WHERE id = ?";

            $values = [];
            foreach ($data as $col => $val) {
                if ($col !== 'id') {
                    $values[] = $val;
                }
            }
            $values[] = $dto->id;

            $stmt = $this->db->prepare($sql);
            $stmt->execute($values);

            return $dto->id;
        }
    }

    /**
     * Создаёт сообщение из массива (обратная совместимость для публичной формы).
     *
     * @param array $message
     * @return int
     */
    public function createMessage(array $message): int
    {
        $dto = MessageDTO::fromRequest($message);
        return $this->saveMessageFromDTO($dto);
    }

    /**
     * Обновляет статус сообщения (is_read, is_approved) — обратная совместимость.
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function updateMessage(int $id, array $data): bool
    {
        $stmt = $this->db->prepare('UPDATE ' . db_table('messages') . ' SET is_read = :is_read, is_approved = :is_approved WHERE id = :id');
        $stmt->execute([
            'is_read' => !empty($data['isRead']) ? 1 : 0,
            'is_approved' => !empty($data['isApproved']) ? 1 : 0,
            'id' => $id,
        ]);
        if ($stmt->rowCount() > 0) {
            return true;
        }
        $exists = $this->db->prepare('SELECT 1 FROM ' . db_table('messages') . ' WHERE id = :id');
        $exists->execute(['id' => $id]);
        return $exists->fetchColumn() !== false;
    }

    /**
     * Удаляет сообщение.
     *
     * @param int $id
     * @return bool
     */
    public function deleteMessage(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM ' . db_table('messages') . ' WHERE id = :id');
        $stmt->execute(['id' => $id]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Ответ администратора на корневой комментарий статьи.
     * Ответ публикуется сразу (одобрен) и помечается как ответ автора сайта.
     *
     * @param int $rootId ID корневого комментария
     * @param string $name Имя автора ответа
     * @param string $body Текст ответа
     * @return array{success: bool, id?: int, error?: string}
     */
    public function staffReply(int $rootId, string $name, string $body): array
    {
        $root = $this->messageById($rootId);
        if (!$root || $root['message_type'] !== 'comment' || $root['parent_id'] !== null) {
            return ['success' => false, 'error' => 'Комментарий не найден или уже является ответом'];
        }

        $dto = new MessageDTO();
        $dto->type = MessageDTO::TYPE_COMMENT;
        $dto->name = $name;
        $dto->body = $body;
        $dto->articleSlug = $root['article_slug'];
        $dto->parentId = $rootId;
        $dto->isStaff = true;
        $dto->isApproved = true;
        $dto->isRead = true;

        if (!$dto->validateStaffReply()) {
            return ['success' => false, 'error' => 'Ошибки валидации: ' . json_encode($dto->getErrors())];
        }

        $id = $this->saveMessageFromDTO($dto);
        return ['success' => true, 'id' => $id];
    }

    /**
     * Одобренные комментарии к статье (для публичного вывода).
     *
     * @param string $slug
     * @return array
     */
    public function commentsByArticle(string $slug): array
    {
        $stmt = $this->db->prepare(
            "SELECT id, parent_id, is_staff, name, body, created_at FROM " . db_table('messages') . "
             WHERE message_type = 'comment' AND article_slug = :slug AND is_approved = 1
             ORDER BY created_at ASC, id ASC"
        );
        $stmt->execute(['slug' => $slug]);
        return $stmt->fetchAll();
    }

    /**
     * Одобренные комментарии к статье как DTO (для API).
     *
     * @param string $slug
     * @return MessageDTO[]
     */
    public function commentsDtoByArticle(string $slug): array
    {
        $rows = $this->baseQuery()
            ->select(['id', 'parent_id', 'is_staff', 'name', 'body', 'created_at'])
            ->where('message_type', '=', 'comment')
            ->where('article_slug', '=', $slug)
            ->where('is_approved', '=', 1)
            ->orderBy('created_at', 'ASC')
            ->orderBy('id', 'ASC')
            ->get();

        return array_map([MessageDTO::class, 'fromDatabase'], $rows);
    }

    /**
     * Количество одобренных комментариев к статье.
     *
     * @param string $slug
     * @return int
     */
    public function commentsCount(string $slug): int
    {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM " . db_table('messages') . "
             WHERE message_type = 'comment' AND article_slug = :slug AND is_approved = 1"
        );
        $stmt->execute(['slug' => $slug]);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Количество одобренных комментариев по slug статей (для списка статей).
     *
     * @return array<string, int>
     */
    public function commentCounts(): array
    {
        $rows = $this->db->query(
            "SELECT article_slug, COUNT(*) AS n FROM " . db_table('messages') . "
             WHERE message_type = 'comment' AND is_approved = 1 AND article_slug <> ''
             GROUP BY article_slug"
        )->fetchAll();
        $map = [];
        foreach ($rows as $row) {
            $map[$row['article_slug']] = (int) $row['n'];
        }
        return $map;
    }

    /**
     * Сообщения по типу (contact, question, comment).
     *
     * @param string $type
     * @return MessageDTO[]
     */
    public function messagesByType(string $type): array
    {
        $rows = $this->baseQuery()
            ->select(['*'])
            ->where('message_type', '=', $type)
            ->orderBy('created_at', 'DESC')
            ->orderBy('id', 'DESC')
            ->get();

        return array_map([MessageDTO::class, 'fromDatabase'], $rows);
    }

    /**
     * Непрочитанные сообщения (contact + question).
     *
     * @return int
     */
    public function unreadCount(): int
    {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM " . db_table('messages') . "
             WHERE message_type IN ('contact', 'question') AND is_read = 0"
        );
        $stmt->execute();
        return (int) $stmt->fetchColumn();
    }
}