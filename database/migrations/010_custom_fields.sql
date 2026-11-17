CREATE TABLE IF NOT EXISTS custom_fields (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    field_key VARCHAR(100) NOT NULL COMMENT 'Уникальный ключ для использования в шаблонах',
    label VARCHAR(200) NOT NULL DEFAULT '' COMMENT 'Название поля в админке',
    field_type ENUM('text','textarea','number','url','image') NOT NULL DEFAULT 'text' COMMENT 'Тип данных',
    field_value MEDIUMTEXT NULL COMMENT 'Значение (для image — путь к файлу)',
    field_group VARCHAR(100) NOT NULL DEFAULT 'general' COMMENT 'Группа для сортировки',
    is_active TINYINT(1) NOT NULL DEFAULT 1 COMMENT 'Активно',
    display_order INT NOT NULL DEFAULT 0 COMMENT 'Порядок отображения',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_custom_fields_key (field_key),
    KEY idx_custom_fields_group (field_group, display_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
