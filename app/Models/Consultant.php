<?php

declare(strict_types=1);

namespace App\Models;

use App\DTO\ConsultantMessageDTO;
use App\DTO\ConsultantSettingsDTO;
use App\DTO\ConsultantUserDTO;
use PDO;

/**
 * Consultant — онлайн-консультант (чат поддержки).
 *
 * === МИГРАЦИЯ ===
 * - Конструктор принимает PDO (для тестов — SQLite :memory:)
 * - Инкапсулирует всю работу с БД (настройки, пользователи, сообщения, typing, reads)
 * - Возвращает DTO вместо массивов
 * - Статический метод connection() для обратной совместимости
 */
final class Consultant
{
    private PDO $db;
    private string $tableSettings;
    private string $tableUsers;
    private string $tableMessages;
    private string $tableReads;
    private string $tableMessageReads;
    private string $tableTyping;

    /**
     * @param PDO|null $db PDO соединение (по умолчанию Database::connection())
     */
    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? \App\Core\Database::connection();
        $this->tableSettings = db_table('consultant_settings');
        $this->tableUsers = db_table('consultant_users');
        $this->tableMessages = db_table('consultant_messages');
        $this->tableReads = db_table('consultant_reads');
        $this->tableMessageReads = db_table('consultant_message_reads');
        $this->tableTyping = db_table('consultant_typing');
        self::ensureSchema($this->db);
    }

    // ===================== ОБРАТНАЯ СОВМЕСТИМОСТЬ (статический) =====================

    /**
     * Получить PDO соединение с гарантированной схемой (обратная совместимость).
     *
     * @return PDO
     */
    public static function connection(): PDO
    {
        $pdo = \App\Core\Database::connection();
        self::ensureSchema($pdo);
        return $pdo;
    }

    // ===================== SCHEMA =====================

    /**
     * Создаёт таблицы, если их нет.
     */
    private static function ensureSchema(PDO $pdo): void
    {
        $pdo->exec('CREATE TABLE IF NOT EXISTS ' . db_table('consultant_settings') . ' (
            setting_key VARCHAR(64) NOT NULL PRIMARY KEY,
            setting_value TEXT NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
        $pdo->exec('CREATE TABLE IF NOT EXISTS ' . db_table('consultant_users') . " (
            id CHAR(32) NOT NULL PRIMARY KEY,
            token_hash CHAR(64) NOT NULL,
            name VARCHAR(100) NOT NULL,
            phone VARCHAR(40) NOT NULL,
            source VARCHAR(500) NOT NULL DEFAULT '',
            status VARCHAR(16) NOT NULL DEFAULT 'new',
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            INDEX consultant_users_updated_at (updated_at),
            UNIQUE KEY consultant_users_token_hash (token_hash)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        $pdo->exec('CREATE TABLE IF NOT EXISTS ' . db_table('consultant_messages') . ' (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            user_id CHAR(32) NOT NULL,
            role VARCHAR(8) NOT NULL,
            message TEXT NOT NULL,
            created_at DATETIME NOT NULL,
            INDEX consultant_messages_user_id (user_id, id),
            CONSTRAINT consultant_messages_user_fk FOREIGN KEY (user_id) REFERENCES ' . db_table('consultant_users') . '(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
        $pdo->exec('CREATE TABLE IF NOT EXISTS ' . db_table('consultant_reads') . ' (
            user_id CHAR(32) NOT NULL PRIMARY KEY,
            message_id BIGINT UNSIGNED NOT NULL,
            read_at DATETIME NOT NULL,
            CONSTRAINT consultant_reads_user_fk FOREIGN KEY (user_id) REFERENCES ' . db_table('consultant_users') . '(id) ON DELETE CASCADE,
            CONSTRAINT consultant_reads_message_fk FOREIGN KEY (message_id) REFERENCES ' . db_table('consultant_messages') . '(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
        $pdo->exec('CREATE TABLE IF NOT EXISTS ' . db_table('consultant_message_reads') . ' (
            user_id CHAR(32) NOT NULL,
            reader_role VARCHAR(8) NOT NULL,
            viewed_message_id BIGINT UNSIGNED NULL,
            read_message_id BIGINT UNSIGNED NULL,
            updated_at DATETIME NOT NULL,
            PRIMARY KEY (user_id, reader_role),
            CONSTRAINT consultant_message_reads_user_fk FOREIGN KEY (user_id) REFERENCES ' . db_table('consultant_users') . '(id) ON DELETE CASCADE,
            CONSTRAINT consultant_message_reads_viewed_fk FOREIGN KEY (viewed_message_id) REFERENCES ' . db_table('consultant_messages') . '(id) ON DELETE SET NULL,
            CONSTRAINT consultant_message_reads_read_fk FOREIGN KEY (read_message_id) REFERENCES ' . db_table('consultant_messages') . '(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
        $pdo->exec('CREATE TABLE IF NOT EXISTS ' . db_table('consultant_typing') . ' (
            user_id CHAR(32) NOT NULL,
            role VARCHAR(8) NOT NULL,
            updated_at DATETIME NOT NULL,
            PRIMARY KEY (user_id, role),
            CONSTRAINT consultant_typing_user_fk FOREIGN KEY (user_id) REFERENCES ' . db_table('consultant_users') . '(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
        $pdo->exec('INSERT IGNORE INTO ' . db_table('consultant_message_reads') . "
            (user_id, reader_role, viewed_message_id, read_message_id, updated_at)
            SELECT user_id, 'admin', message_id, message_id, read_at FROM " . db_table('consultant_reads') . '');
    }

    // ===================== SETTINGS =====================

    /**
     * Получить все настройки.
     *
     * @return ConsultantSettingsDTO
     */
    public function getSettings(): ConsultantSettingsDTO
    {
        $rows = $this->db->query('SELECT setting_key, setting_value FROM ' . $this->tableSettings . '')->fetchAll();
        return ConsultantSettingsDTO::fromDatabase($rows);
    }

    /**
     * Сохранить настройки.
     *
     * @param array<string, mixed> $settings
     * @return array{success: bool, error?: string}
     */
    public function saveSettings(array $settings): array
    {
        $allowedKeys = [
            'enabled', 'buttonColor', 'buttonTextColor', 'buttonPosition',
            'workingHours', 'censoredWords', 'welcomeMessage', 'notificationEmail'
        ];

        $statement = $this->db->prepare('INSERT INTO ' . $this->tableSettings . ' (setting_key, setting_value) VALUES (?, ?)
            ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)');

        foreach ($settings as $key => $value) {
            if (!in_array($key, $allowedKeys, true)) {
                continue;
            }
            if (!is_scalar($value)) {
                return ['success' => false, 'error' => 'Некорректное значение настройки.'];
            }
            // Нормализация enabled
            if ($key === 'enabled') {
                $value = filter_var($value, FILTER_VALIDATE_BOOLEAN) ? '1' : '0';
            }
            $statement->execute([$key, (string) $value]);
        }
        return ['success' => true];
    }

    // ===================== USERS / THREADS =====================

    /**
     * Получить состояние для админки (список пользователей + сообщения).
     *
     * @return array{users: ConsultantUserDTO[], messages: array<string, ConsultantMessageDTO[]>}
     */
    public function getState(): array
    {
        $sql = 'SELECT u.id, u.name, u.phone, u.source, u.status, u.created_at, u.updated_at,
                (SELECT m.message FROM ' . $this->tableMessages . ' m WHERE m.user_id = u.id ORDER BY m.id DESC LIMIT 1) AS lastMessage,
                (SELECT m.id FROM ' . $this->tableMessages . ' m WHERE m.user_id = u.id ORDER BY m.id DESC LIMIT 1) AS lastMessageId,
                (SELECT m.role FROM ' . $this->tableMessages . ' m WHERE m.user_id = u.id ORDER BY m.id DESC LIMIT 1) AS lastMessageRole,
                (SELECT m.id FROM ' . $this->tableMessages . ' m WHERE m.user_id = u.id AND m.role = \'user\' ORDER BY m.id DESC LIMIT 1) AS lastUserMessageId,
                (SELECT r.viewed_message_id FROM ' . $this->tableMessageReads . ' r WHERE r.user_id = u.id AND r.reader_role = \'admin\') AS viewedByAdminMessageId,
                (SELECT r.read_message_id FROM ' . $this->tableMessageReads . ' r WHERE r.user_id = u.id AND r.reader_role = \'admin\') AS readByAdminMessageId,
                (SELECT r.viewed_message_id FROM ' . $this->tableMessageReads . ' r WHERE r.user_id = u.id AND r.reader_role = \'user\') AS viewedByUserMessageId,
                (SELECT r.read_message_id FROM ' . $this->tableMessageReads . ' r WHERE r.user_id = u.id AND r.reader_role = \'user\') AS readByUserMessageId,
                (SELECT t.role FROM ' . $this->tableTyping . ' t WHERE t.user_id = u.id AND t.role != \'admin\' AND t.updated_at >= UTC_TIMESTAMP() - INTERVAL 5 SECOND LIMIT 1) AS typingRole
            FROM ' . $this->tableUsers . ' u ORDER BY u.updated_at DESC LIMIT 200';

        $rows = $this->db->query($sql)->fetchAll();
        $users = array_map([ConsultantUserDTO::class, 'fromDatabase'], $rows);

        // Получаем сообщения
        $messageQuery = $this->db->query('SELECT m.id, m.user_id, m.role, m.message, m.created_at
            FROM ' . $this->tableMessages . ' m
            JOIN ' . $this->tableUsers . ' u ON u.id = m.user_id
            ORDER BY u.updated_at DESC, m.id ASC');
        $messageRows = $messageQuery->fetchAll();
        $messages = [];
        foreach ($messageRows as $message) {
            $dto = ConsultantMessageDTO::fromDatabase($message);
            $messages[$dto->userId][] = $dto;
        }

        return ['users' => $users, 'messages' => $messages];
    }

    /**
     * Создать новый чат (обращение).
     *
     * @param string $name
     * @param string $phone
     * @param string $question
     * @param string $source
     * @return array{success: bool, userId?: string, token?: string, error?: string}
     */
    public function createThread(string $name, string $phone, string $question, string $source = ''): array
    {
        $id = bin2hex(random_bytes(16));
        $token = bin2hex(random_bytes(32));
        $now = gmdate('Y-m-d H:i:s');

        try {
            $this->db->beginTransaction();
            $userInsert = $this->db->prepare('INSERT INTO ' . $this->tableUsers . ' (id, token_hash, name, phone, source, status, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
            $userInsert->execute([
                $id, hash('sha256', $token), $name, $phone, $source,
                'new', $now, $now,
            ]);
            $messageInsert = $this->db->prepare('INSERT INTO ' . $this->tableMessages . ' (user_id, role, message, created_at) VALUES (?, ?, ?, ?)');
            $messageInsert->execute([$id, 'user', $question, $now]);
            $this->db->commit();
        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            return ['success' => false, 'error' => 'Не удалось сохранить обращение.'];
        }

        return ['success' => true, 'userId' => $id, 'token' => $token];
    }

    /**
     * Получить чат пользователя.
     *
     * @param string $userId
     * @return array{success: bool, messages?: ConsultantMessageDTO[], status?: string, readStatus?: array, typingRole?: string|null, error?: string}
     */
    public function getThread(string $userId): array
    {
        $query = $this->db->prepare('SELECT id, role, message, created_at FROM ' . $this->tableMessages . ' WHERE user_id = ? ORDER BY id ASC');
        $query->execute([$userId]);
        $messages = array_map([ConsultantMessageDTO::class, 'fromDatabase'], $query->fetchAll());

        $query = $this->db->prepare('SELECT reader_role, viewed_message_id, read_message_id FROM ' . $this->tableMessageReads . ' WHERE user_id = ?');
        $query->execute([$userId]);
        $readStatus = ['admin' => ['viewed' => null, 'read' => null], 'user' => ['viewed' => null, 'read' => null]];
        foreach ($query->fetchAll() as $row) {
            $readStatus[$row['reader_role']] = [
                'viewed' => $row['viewed_message_id'] === null ? null : (string) $row['viewed_message_id'],
                'read' => $row['read_message_id'] === null ? null : (string) $row['read_message_id'],
            ];
        }

        $query = $this->db->prepare('SELECT role FROM ' . $this->tableTyping . " WHERE user_id = ? AND role = 'admin' AND updated_at >= UTC_TIMESTAMP() - INTERVAL 5 SECOND LIMIT 1");
        $query->execute([$userId]);
        $typingRole = $query->fetchColumn();

        $query = $this->db->prepare('SELECT status FROM ' . $this->tableUsers . ' WHERE id = ?');
        $query->execute([$userId]);
        $status = $query->fetchColumn() ?? 'new';

        return ['success' => true, 'messages' => $messages, 'status' => $status, 'readStatus' => $readStatus, 'typingRole' => $typingRole === false ? null : $typingRole];
    }

    /**
     * Отправить сообщение в чат.
     *
     * @param string $userId
     * @param string $role 'user' или 'admin'
     * @param string $message
     * @return array{success: bool, message?: ConsultantMessageDTO, error?: string}
     */
    public function sendMessage(string $userId, string $role, string $message): array
    {
        $now = gmdate('Y-m-d H:i:s');
        $newStatus = $role === 'admin' ? 'answered' : 'new';

        try {
            $this->db->beginTransaction();
            $insert = $this->db->prepare('INSERT INTO ' . $this->tableMessages . ' (user_id, role, message, created_at) VALUES (?, ?, ?, ?)');
            $insert->execute([$userId, $role, $message, $now]);
            $messageId = (int) $this->db->lastInsertId();

            $update = $this->db->prepare('UPDATE ' . $this->tableUsers . ' SET updated_at = ?, status = ? WHERE id = ?');
            $update->execute([$now, $newStatus, $userId]);
            $this->db->commit();
        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            return ['success' => false, 'error' => 'Не удалось сохранить сообщение.'];
        }

        $dto = new ConsultantMessageDTO();
        $dto->id = $messageId;
        $dto->userId = $userId;
        $dto->role = $role;
        $dto->text = $message;
        $dto->createdAt = gmdate(DATE_ATOM);

        return ['success' => true, 'message' => $dto];
    }

    /**
     * Закрыть чат.
     *
     * @param string $userId
     * @return array{success: bool, error?: string}
     */
    public function closeThread(string $userId): array
    {
        $now = gmdate('Y-m-d H:i:s');
        $close = $this->db->prepare('UPDATE ' . $this->tableUsers . ' SET status = \'closed\', updated_at = ? WHERE id = ? AND status != \'closed\'');
        $close->execute([$now, $userId]);
        if ($close->rowCount() === 0) {
            $exists = $this->db->prepare('SELECT status FROM ' . $this->tableUsers . ' WHERE id = ?');
            $exists->execute([$userId]);
            if ($exists->fetchColumn() === false) {
                return ['success' => false, 'error' => 'Чат не найден.'];
            }
        }
        return ['success' => true];
    }

    /**
     * Удалить отвеченные обращения (статус answered).
     *
     * @return array{success: bool, deletedCount?: int, error?: string}
     */
    public function deleteAnswered(): array
    {
        try {
            $this->db->beginTransaction();
            $delete = $this->db->prepare('DELETE FROM ' . $this->tableUsers . ' WHERE status = \'answered\'');
            $delete->execute();
            $deletedCount = $delete->rowCount();
            $this->db->commit();
        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            return ['success' => false, 'error' => 'Не удалось удалить отвеченные обращения.'];
        }
        return ['success' => true, 'deletedCount' => $deletedCount];
    }

    // ===================== MESSAGES / READS =====================

    /**
     * Отметить сообщения как просмотренные/прочитанные.
     *
     * @param string $userId
     * @param string $readerRole 'admin' или 'user'
     * @param string $level 'viewed' или 'read'
     * @return array{success: bool, messageId?: string, error?: string}
     */
    public function markRead(string $userId, string $readerRole, string $level): array
    {
        if (!in_array($readerRole, ['admin', 'user'], true) || !in_array($level, ['viewed', 'read'], true)) {
            return ['success' => false, 'error' => 'Некорректный статус сообщения.'];
        }

        $messageRole = $readerRole === 'admin' ? 'user' : 'admin';
        $query = $this->db->prepare('SELECT id FROM ' . $this->tableMessages . ' WHERE user_id = ? AND role = ? ORDER BY id DESC LIMIT 1');
        $query->execute([$userId, $messageRole]);
        $messageId = $query->fetchColumn();
        if ($messageId === false) {
            return ['success' => true];
        }

        $viewedValue = $level === 'viewed' ? $messageId : null;
        $readValue = $level === 'read' ? $messageId : null;

        $statement = $this->db->prepare('INSERT INTO ' . $this->tableMessageReads . '
                (user_id, reader_role, viewed_message_id, read_message_id, updated_at)
            VALUES (?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
                viewed_message_id = CASE WHEN VALUES(viewed_message_id) IS NULL THEN viewed_message_id
                    ELSE GREATEST(COALESCE(viewed_message_id, 0), VALUES(viewed_message_id)) END,
                read_message_id = CASE WHEN VALUES(read_message_id) IS NULL THEN read_message_id
                    ELSE GREATEST(COALESCE(read_message_id, 0), VALUES(read_message_id)) END,
                updated_at = VALUES(updated_at)');
        $statement->execute([
            $userId,
            $readerRole,
            $level === 'read' ? $messageId : $viewedValue,
            $readValue,
            gmdate('Y-m-d H:i:s'),
        ]);

        return ['success' => true, 'messageId' => (string) $messageId];
    }

    // ===================== TYPING =====================

    /**
     * Проверить, печатает ли кто-то.
     *
     * @param string $userId
     * @param string $readerRole 'admin' или 'user'
     * @return string|null роль печатающего или null
     */
    public function getTyping(string $userId, string $readerRole): ?string
    {
        if (!in_array($readerRole, ['admin', 'user'], true)) {
            return null;
        }
        $query = $this->db->prepare('SELECT role FROM ' . $this->tableTyping . '
            WHERE user_id = ? AND role != ? AND updated_at >= UTC_TIMESTAMP() - INTERVAL 5 SECOND LIMIT 1');
        $query->execute([$userId, $readerRole]);
        $typingRole = $query->fetchColumn();
        return $typingRole === false ? null : $typingRole;
    }

    /**
     * Установить/снять статус печати.
     *
     * @param string $userId
     * @param string $role 'admin' или 'user'
     * @param bool $isTyping
     * @return array{success: bool, error?: string}
     */
    public function setTyping(string $userId, string $role, bool $isTyping): array
    {
        if (!in_array($role, ['admin', 'user'], true)) {
            return ['success' => false, 'error' => 'Не удалось проверить чат.'];
        }

        if ($isTyping) {
            $query = $this->db->prepare('INSERT INTO ' . $this->tableTyping . ' (user_id, role, updated_at) VALUES (?, ?, ?)
                ON DUPLICATE KEY UPDATE updated_at = VALUES(updated_at)');
            $query->execute([$userId, $role, gmdate('Y-m-d H:i:s')]);
        } else {
            $query = $this->db->prepare('DELETE FROM ' . $this->tableTyping . ' WHERE user_id = ? AND role = ?');
            $query->execute([$userId, $role]);
        }
        return ['success' => true];
    }

    // ===================== HELPERS =====================

    /**
     * Получить пользователя по ID.
     *
     * @param string $userId
     * @return array{id: string, token_hash: string, status: string}|null
     */
    public function getUserById(string $userId): ?array
    {
        $query = $this->db->prepare('SELECT id, token_hash, status FROM ' . $this->tableUsers . ' WHERE id = ?');
        $query->execute([$userId]);
        $row = $query->fetch();
        return $row ?: null;
    }

    /**
     * Проверить токен пользователя.
     *
     * @param string $userId
     * @param string $token
     * @return bool
     */
    public function validateToken(string $userId, string $token): bool
    {
        $query = $this->db->prepare('SELECT token_hash FROM ' . $this->tableUsers . ' WHERE id = ?');
        $query->execute([$userId]);
        $hash = $query->fetchColumn();
        if ($hash === false) {
            return false;
        }
        return preg_match('/^[a-f0-9]{64}$/', $token) && hash_equals($hash, hash('sha256', $token));
    }
}
