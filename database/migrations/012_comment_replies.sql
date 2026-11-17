ALTER TABLE messages
    ADD COLUMN IF NOT EXISTS parent_id INT UNSIGNED NULL COMMENT 'Родительский комментарий (ответ)' AFTER article_slug,
    ADD COLUMN IF NOT EXISTS is_staff TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Ответ от имени автора сайта' AFTER parent_id;

ALTER TABLE messages
    ADD INDEX IF NOT EXISTS idx_messages_parent (parent_id);
