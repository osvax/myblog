<?php

declare(strict_types=1);

namespace App\DTO;

/**
 * MessageDTO — объект переноса данных для сообщений/обращений/комментариев.
 *
 * === ПОТОК ДАННЫХ (Data Flow) ===
 *
 * 1. ВХОД (Request → DTO):
 *    - POST /api/messages (публичная форма обратной связи / комментарий)
 *      Тело: { "type": "contact", "name": "Иван", "email": "ivan@test.ru",
 *              "phone": "+7...", "subject": "Вопрос", "body": "Текст...",
 *              "articleSlug": "my-article" }
 *    - PATCH /api/admin/messages/{id} (админка: изменение статусов)
 *      Тело: { "isRead": true, "isApproved": true }
 *    - POST /api/admin/messages/{id}/reply (ответ автора на комментарий)
 *      Тело: { "name": "Автор сайта", "body": "Текст ответа..." }
 *    - Контроллер: MessageDTO::fromRequest($request->json(), $id?)
 *
 * 2. ВАЛИДАЦИЯ (DTO → Errors):
 *    - $dto->validate() — правила зависят от message_type
 *    - Для contact/question: name, email, body обязательны
 *    * Для comment: name, body, articleSlug обязательны
 *    - Ошибки → $dto->getErrors()
 *
 * 3. СОХРАНЕНИЕ (DTO → Database):
 *    - Repository: saveMessageFromDTO(MessageDTO $dto)
 *    - DTO::toArray() → массив колонок va_messages
 *    - INSERT в va_messages
 *
 * 4. ЧТЕНИЕ (Database → DTO):
 *    - Repository: messages(), messageById(), commentsByArticle()
 *    - SELECT * FROM va_messages ORDER BY created_at DESC
 *    - MessageDTO::fromDatabase($row) → DTO
 *
 * 5. ВЫХОД (DTO → JSON Response):
 *    - DTO::toApiArray() → массив для json()
 *    - Контроллер возвращает список или одиночное сообщение
 *
 * === СТРУКТУРА ТАБЛИЦЫ va_messages ===
 * - id (INT, PK, AI)
 * - message_type (VARCHAR 20) — тип: 'contact'|'question'|'comment'
 * - name (VARCHAR 160) — имя отправителя
 * - email (VARCHAR 255) — email (опционально для комментариев)
 * - phone (VARCHAR 50) — телефон (опционально)
 * - subject (VARCHAR 255) — тема (для contact/question)
 * - body (TEXT) — текст сообщения
 * - article_slug (VARCHAR 180) — slug статьи (для комментариев)
 * - parent_id (INT, NULL) — ID родительского комментария (для ответов)
 * - is_staff (TINYINT) — 1 если ответ администратора/автора
 * - is_approved (TINYINT) — 1 если одобрен (для комментариев)
 * - is_read (TINYINT) — 1 если прочитано администратором
 * - created_at, updated_at (TIMESTAMP)
 *
 * === ТИПЫ СООБЩЕНИЙ ===
 * - 'contact' — форма обратной связи (требует email, subject)
 * - 'question' — вопрос с сайта (требует email)
 * - 'comment' — комментарий к статье (требует article_slug)
 */
final class MessageDTO
{
    // === Основные поля (колонки va_messages) ===

    /** @var int|null ID сообщения (NULL для нового) */
    public ?int $id = null;

    /** @var string Тип сообщения: 'contact'|'question'|'comment' */
    public string $type = 'contact';

    /** @var string Имя отправителя (обязательно, 2..160) */
    public string $name = '';

    /** @var string Email (обязателен для contact/question) */
    public string $email = '';

    /** @var string Телефон (опционально) */
    public string $phone = '';

    /** @var string Тема (для contact/question) */
    public string $subject = '';

    /** @var string Текст сообщения (обязательно, макс 10000) */
    public string $body = '';

    /** @var string Slug статьи (для комментариев) */
    public string $articleSlug = '';

    /** @var int|null ID родительского комментария (для ответов) */
    public ?int $parentId = null;

    /** @var bool Является ответом администратора/автора */
    public bool $isStaff = false;

    /** @var bool Одобрено (для комментариев) */
    public bool $isApproved = false;

    /** @var bool Прочитано администратором */
    public bool $isRead = false;

    /** @var string Дата создания (Y-m-d H:i:s) */
    public string $createdAt = '';

    /** @var string Дата обновления (Y-m-d H:i:s) */
    public string $updatedAt = '';

    // === Ошибки валидации ===
    private array $errors = [];

    // === Константы ===
    public const TYPE_CONTACT = 'contact';
    public const TYPE_QUESTION = 'question';
    public const TYPE_COMMENT = 'comment';
    public const TYPE_VALUES = [self::TYPE_CONTACT, self::TYPE_QUESTION, self::TYPE_COMMENT];
    public const NAME_MAX = 160;
    public const NAME_MIN = 2;
    public const SUBJECT_MAX = 255;
    public const BODY_MAX = 10000;
    public const BODY_MIN = 1;

    /**
     * Создаёт DTO из массива запроса (JSON / POST).
     *
     * ИСПОЛЬЗОВАНИЕ:
     * ```php
     * // Публичная форма (POST /api/messages)
     * $jsonData = $request->json(); // {type, name, email, phone, subject, body, articleSlug, parentId}
     * $dto = MessageDTO::fromRequest($jsonData);
     *
     * // Админка: обновление статусов (PATCH /api/admin/messages/{id})
     * $jsonData = $request->json(); // {isRead: true, isApproved: true}
     * $dto = MessageDTO::fromRequest($jsonData, $params['id']);
     *
     * // Админка: ответ на комментарий (POST /api/admin/messages/{id}/reply)
     * $jsonData = $request->json(); // {name: "Автор", body: "Текст..."}
     * $dto = MessageDTO::fromRequest($jsonData, $params['id']);
     * $dto->type = 'comment';
     * $dto->isStaff = true;
     * $dto->parentId = (int)$params['id'];
     * ```
     *
     * @param array $data Данные из $request->json()
     * @param int|null $id ID из URL (для PATCH/POST reply)
     * @return self
     */
    public static function fromRequest(array $data, ?int $id = null): self
    {
        $dto = new self();
        $dto->id = $id ?? (isset($data['id']) && is_numeric($data['id']) ? (int)$data['id'] : null);

        // type
        if (isset($data['type']) && is_string($data['type'])) {
            $dto->type = $data['type'];
        } elseif (isset($data['message_type']) && is_string($data['message_type'])) {
            $dto->type = $data['message_type'];
        }

        // name
        $dto->name = trim((string)($data['name'] ?? ''));

        // email
        $dto->email = trim((string)($data['email'] ?? ''));

        // phone
        $dto->phone = trim((string)($data['phone'] ?? ''));

        // subject
        $dto->subject = trim((string)($data['subject'] ?? ''));

        // body
        $dto->body = trim((string)($data['body'] ?? ''));

        // articleSlug
        $dto->articleSlug = trim((string)($data['articleSlug'] ?? $data['article_slug'] ?? ''));

        // parentId
        if (isset($data['parentId']) && is_numeric($data['parentId'])) {
            $dto->parentId = (int)$data['parentId'];
        } elseif (isset($data['parent_id']) && is_numeric($data['parent_id'])) {
            $dto->parentId = (int)$data['parent_id'];
        }

        // isStaff
        if (isset($data['isStaff'])) {
            $dto->isStaff = filter_var($data['isStaff'], FILTER_VALIDATE_BOOLEAN);
        } elseif (isset($data['is_staff'])) {
            $dto->isStaff = filter_var($data['is_staff'], FILTER_VALIDATE_BOOLEAN);
        }

        // isApproved (для PATCH админки)
        if (isset($data['isApproved'])) {
            $dto->isApproved = filter_var($data['isApproved'], FILTER_VALIDATE_BOOLEAN);
        } elseif (isset($data['is_approved'])) {
            $dto->isApproved = filter_var($data['is_approved'], FILTER_VALIDATE_BOOLEAN);
        }

        // isRead (для PATCH админки)
        if (isset($data['isRead'])) {
            $dto->isRead = filter_var($data['isRead'], FILTER_VALIDATE_BOOLEAN);
        } elseif (isset($data['is_read'])) {
            $dto->isRead = filter_var($data['is_read'], FILTER_VALIDATE_BOOLEAN);
        }

        return $dto;
    }

    /**
     * Создаёт DTO из строки БД (результат SELECT * FROM va_messages).
     *
     * @param array $row Ассоциативный массив из БД
     * @return self
     */
    public static function fromDatabase(array $row): self
    {
        $dto = new self();
        $dto->id = isset($row['id']) ? (int)$row['id'] : null;
        $dto->type = $row['message_type'] ?? 'contact';
        $dto->name = $row['name'] ?? '';
        $dto->email = $row['email'] ?? '';
        $dto->phone = $row['phone'] ?? '';
        $dto->subject = $row['subject'] ?? '';
        $dto->body = $row['body'] ?? '';
        $dto->articleSlug = $row['article_slug'] ?? '';
        $dto->parentId = isset($row['parent_id']) && $row['parent_id'] !== null ? (int)$row['parent_id'] : null;
        $dto->isStaff = (bool)($row['is_staff'] ?? false);
        $dto->isApproved = (bool)($row['is_approved'] ?? false);
        $dto->isRead = (bool)($row['is_read'] ?? false);
        $dto->createdAt = $row['created_at'] ?? '';
        $dto->updatedAt = $row['updated_at'] ?? '';

        return $dto;
    }

    /**
     * Валидирует DTO, заполняет $this->errors.
     * Правила зависят от типа сообщения ($this->type).
     *
     * @return bool true если валидно
     */
    public function validate(): bool
    {
        $this->errors = [];

        // --- type ---
        if (!in_array($this->type, self::TYPE_VALUES, true)) {
            $this->errors['type'] = 'Недопустимый тип сообщения. Допустимые: ' . implode(', ', self::TYPE_VALUES);
        }

        // --- name (обязателен для всех типов) ---
        if ($this->name === '') {
            $this->errors['name'] = 'Имя обязательно';
        } elseif (mb_strlen($this->name) < self::NAME_MIN) {
            $this->errors['name'] = 'Имя не короче ' . self::NAME_MIN . ' символов';
        } elseif (mb_strlen($this->name) > self::NAME_MAX) {
            $this->errors['name'] = 'Имя не длиннее ' . self::NAME_MAX . ' символов';
        }

        // --- email ---
        if ($this->type === self::TYPE_CONTACT || $this->type === self::TYPE_QUESTION) {
            // Для contact/question — email обязателен
            if ($this->email === '') {
                $this->errors['email'] = 'Email обязателен';
            } elseif (!filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
                $this->errors['email'] = 'Неверный формат email';
            }
        } elseif ($this->type === self::TYPE_COMMENT) {
            // Для комментариев — email опционален, но если задан — валиден
            if ($this->email !== '' && !filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
                $this->errors['email'] = 'Неверный формат email';
            }
        }

        // --- phone ---
        // Опционально для всех типов

        // --- subject ---
        if ($this->type === self::TYPE_CONTACT || $this->type === self::TYPE_QUESTION) {
            if ($this->subject === '') {
                $this->errors['subject'] = 'Тема обязательна';
            } elseif (mb_strlen($this->subject) > self::SUBJECT_MAX) {
                $this->errors['subject'] = 'Тема не длиннее ' . self::SUBJECT_MAX . ' символов';
            }
        }

        // --- body (обязателен для всех) ---
        if ($this->body === '') {
            $this->errors['body'] = 'Текст сообщения обязателен';
        } elseif (mb_strlen($this->body) < self::BODY_MIN) {
            $this->errors['body'] = 'Текст сообщения слишком короткий';
        } elseif (mb_strlen($this->body) > self::BODY_MAX) {
            $this->errors['body'] = 'Текст сообщения не длиннее ' . self::BODY_MAX . ' символов';
        }

        // --- articleSlug (обязателен для комментариев) ---
        if ($this->type === self::TYPE_COMMENT) {
            if ($this->articleSlug === '') {
                $this->errors['articleSlug'] = 'Slug статьи обязателен для комментария';
            } elseif (mb_strlen($this->articleSlug) > 180) {
                $this->errors['articleSlug'] = 'Slug статьи не длиннее 180 символов';
            }
        }

        // --- parentId ---
        if ($this->parentId !== null && $this->parentId <= 0) {
            $this->errors['parentId'] = 'Некорректный ID родительского комментария';
        }

        return empty($this->errors);
    }

    /**
     * Валидация для ответа администратора на комментарий.
     * Требует: name (2..160), body (1..10000).
     *
     * @return bool
     */
    public function validateStaffReply(): bool
    {
        $this->errors = [];

        if ($this->name === '') {
            $this->errors['name'] = 'Имя автора ответа обязательно';
        } elseif (mb_strlen($this->name) < self::NAME_MIN || mb_strlen($this->name) > self::NAME_MAX) {
            $this->errors['name'] = 'Имя: от ' . self::NAME_MIN . ' до ' . self::NAME_MAX . ' символов';
        }

        if ($this->body === '') {
            $this->errors['body'] = 'Текст ответа обязателен';
        } elseif (mb_strlen($this->body) > self::BODY_MAX) {
            $this->errors['body'] = 'Текст ответа не длиннее ' . self::BODY_MAX . ' символов';
        }

        return empty($this->errors);
    }

    /**
     * Возвращает ошибки валидации.
     *
     * @return array ['field' => 'message', ...]
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Проверяет, прошла ли валидация.
     */
    public function isValid(): bool
    {
        if (empty($this->errors)) {
            $this->validate();
        }
        return empty($this->errors);
    }

    /**
     * Преобразует в массив для сохранения в БД (таблица va_messages).
     *
     * @return array ['column' => value, ...]
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'message_type' => $this->type,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'subject' => $this->subject,
            'body' => $this->body,
            'article_slug' => $this->articleSlug,
            'parent_id' => $this->parentId,
            'is_staff' => $this->isStaff ? 1 : 0,
            'is_approved' => $this->isApproved ? 1 : 0,
            'is_read' => $this->isRead ? 1 : 0,
        ];
    }

    /**
     * Преобразует в массив для JSON API ответа (список в админке).
     *
     * СТРУКТУРА ОТВЕТА:
     * [
     *   'id' => 1,
     *   'type' => 'comment',
     *   'name' => 'Иван',
     *   'email' => 'ivan@test.ru',
     *   'phone' => '+7...',
     *   'subject' => 'Вопрос по статье',
     *   'body' => 'Текст...',
     *   'articleSlug' => 'my-article',
     *   'parentId' => null,
     *   'isStaff' => false,
     *   'isApproved' => true,
     *   'isRead' => true,
     *   'createdAt' => '2025-01-15 10:30:00',
     *   'updatedAt' => '2025-01-15 10:30:00',
     * ]
     *
     * @return array
     */
    public function toApiArray(): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'subject' => $this->subject,
            'body' => $this->body,
            'articleSlug' => $this->articleSlug,
            'parentId' => $this->parentId,
            'isStaff' => $this->isStaff,
            'isApproved' => $this->isApproved,
            'isRead' => $this->isRead,
            'createdAt' => $this->createdAt,
            'updatedAt' => $this->updatedAt,
        ];
    }

    /**
     * Преобразует в массив для публичного вывода комментариев (commentsByArticle).
     * Минимальный набор полей.
     *
     * @return array
     */
    public function toPublicCommentArray(): array
    {
        return [
            'id' => $this->id,
            'parentId' => $this->parentId,
            'isStaff' => $this->isStaff,
            'name' => $this->name,
            'body' => $this->body,
            'createdAt' => $this->createdAt,
        ];
    }
}
