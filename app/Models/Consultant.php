<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class Consultant
{
    public static function connection()
    {
        $pdo = Database::connection();
        self::ensureSchema($pdo);
        return $pdo;
    }

    private static function ensureSchema(PDO $pdo)
    {
        $pdo->exec("CREATE TABLE IF NOT EXISTS consultant_settings (
            setting_key VARCHAR(64) NOT NULL PRIMARY KEY,
            setting_value TEXT NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        $pdo->exec("CREATE TABLE IF NOT EXISTS consultant_users (
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
        $pdo->exec("CREATE TABLE IF NOT EXISTS consultant_messages (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            user_id CHAR(32) NOT NULL,
            role VARCHAR(8) NOT NULL,
            message TEXT NOT NULL,
            created_at DATETIME NOT NULL,
            INDEX consultant_messages_user_id (user_id, id),
            CONSTRAINT consultant_messages_user_fk FOREIGN KEY (user_id) REFERENCES consultant_users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        $pdo->exec("CREATE TABLE IF NOT EXISTS consultant_reads (
            user_id CHAR(32) NOT NULL PRIMARY KEY,
            message_id BIGINT UNSIGNED NOT NULL,
            read_at DATETIME NOT NULL,
            CONSTRAINT consultant_reads_user_fk FOREIGN KEY (user_id) REFERENCES consultant_users(id) ON DELETE CASCADE,
            CONSTRAINT consultant_reads_message_fk FOREIGN KEY (message_id) REFERENCES consultant_messages(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        $pdo->exec("CREATE TABLE IF NOT EXISTS consultant_message_reads (
            user_id CHAR(32) NOT NULL,
            reader_role VARCHAR(8) NOT NULL,
            viewed_message_id BIGINT UNSIGNED NULL,
            read_message_id BIGINT UNSIGNED NULL,
            updated_at DATETIME NOT NULL,
            PRIMARY KEY (user_id, reader_role),
            CONSTRAINT consultant_message_reads_user_fk FOREIGN KEY (user_id) REFERENCES consultant_users(id) ON DELETE CASCADE,
            CONSTRAINT consultant_message_reads_viewed_fk FOREIGN KEY (viewed_message_id) REFERENCES consultant_messages(id) ON DELETE SET NULL,
            CONSTRAINT consultant_message_reads_read_fk FOREIGN KEY (read_message_id) REFERENCES consultant_messages(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        $pdo->exec("CREATE TABLE IF NOT EXISTS consultant_typing (
            user_id CHAR(32) NOT NULL,
            role VARCHAR(8) NOT NULL,
            updated_at DATETIME NOT NULL,
            PRIMARY KEY (user_id, role),
            CONSTRAINT consultant_typing_user_fk FOREIGN KEY (user_id) REFERENCES consultant_users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        $pdo->exec("INSERT IGNORE INTO consultant_message_reads
            (user_id, reader_role, viewed_message_id, read_message_id, updated_at)
            SELECT user_id, 'admin', message_id, message_id, read_at FROM consultant_reads");
    }
}
