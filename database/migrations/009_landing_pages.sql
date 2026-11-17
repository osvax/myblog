CREATE TABLE IF NOT EXISTS landing_pages (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    slug VARCHAR(100) NOT NULL DEFAULT 'home' COMMENT 'Уникальный ключ страницы',
    title VARCHAR(254) NOT NULL DEFAULT '' COMMENT 'Заголовок страницы',
    html_content LONGTEXT NULL COMMENT 'Сгенерированный HTML',
    is_active TINYINT(1) NOT NULL DEFAULT 1 COMMENT 'Активна',
    generated_at DATETIME NULL COMMENT 'Дата генерации',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_slug (slug),
    INDEX idx_is_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
