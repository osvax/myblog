CREATE TABLE IF NOT EXISTS site_settings (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT NULL,
    setting_type VARCHAR(20) NOT NULL DEFAULT 'text' COMMENT 'text, number, checkbox, select, color, textarea, email, tel, url',
    tab_group VARCHAR(50) NOT NULL DEFAULT 'general' COMMENT 'group: general, appearance, seo, notifications, consultant, contacts, security, email',
    display_order INT UNSIGNED NOT NULL DEFAULT 0,
    placeholder VARCHAR(254) DEFAULT '',
    options TEXT NULL COMMENT 'JSON array for select',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_tab_group (tab_group),
    INDEX idx_display_order (display_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
