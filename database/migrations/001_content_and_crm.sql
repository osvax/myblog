CREATE TABLE IF NOT EXISTS categories (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(120) NOT NULL,
    slug VARCHAR(140) NOT NULL,
    description VARCHAR(500) NOT NULL DEFAULT '',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_categories_slug (slug),
    UNIQUE KEY uq_categories_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS articles (
    id CHAR(32) NOT NULL,
    slug VARCHAR(180) NOT NULL,
    title VARCHAR(240) NOT NULL,
    description VARCHAR(500) NOT NULL DEFAULT '',
    body_html MEDIUMTEXT NULL,
    category_id BIGINT UNSIGNED NULL,
    author VARCHAR(160) NOT NULL DEFAULT '',
    published_at DATE NULL,
    icon VARCHAR(80) NOT NULL DEFAULT 'bi-file-text',
    cover_path VARCHAR(500) NOT NULL DEFAULT '',
    tags_json JSON NULL,
    is_published TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_articles_slug (slug),
    KEY idx_articles_publication (is_published, published_at),
    KEY idx_articles_category (category_id),
    CONSTRAINT fk_articles_category FOREIGN KEY (category_id) REFERENCES categories (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS portfolio_projects (
    id CHAR(32) NOT NULL,
    slug VARCHAR(180) NOT NULL,
    title VARCHAR(240) NOT NULL,
    technologies VARCHAR(500) NOT NULL DEFAULT '',
    icon VARCHAR(80) NOT NULL DEFAULT 'bi-briefcase',
    description MEDIUMTEXT NOT NULL,
    task_json JSON NULL,
    solution MEDIUMTEXT NULL,
    client VARCHAR(180) NOT NULL DEFAULT '',
    project_year SMALLINT UNSIGNED NULL,
    duration VARCHAR(120) NOT NULL DEFAULT '',
    stack VARCHAR(500) NOT NULL DEFAULT '',
    results_json JSON NULL,
    screenshots_json JSON NULL,
    is_published TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_portfolio_projects_slug (slug),
    KEY idx_portfolio_publication (is_published, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS orders (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    client_name VARCHAR(160) NOT NULL,
    email VARCHAR(254) NOT NULL,
    phone VARCHAR(40) NOT NULL DEFAULT '',
    project_type VARCHAR(120) NOT NULL DEFAULT '',
    budget DECIMAL(12,2) NOT NULL DEFAULT 0,
    deadline DATE NULL,
    order_date DATE NOT NULL,
    status ENUM('Новый','В работе','Завершён','Отменён') NOT NULL DEFAULT 'Новый',
    details_json JSON NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_orders_date (order_date),
    KEY idx_orders_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS messages (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    message_type ENUM('feedback','comment') NOT NULL,
    name VARCHAR(160) NOT NULL,
    email VARCHAR(254) NOT NULL,
    phone VARCHAR(40) NOT NULL DEFAULT '',
    subject VARCHAR(240) NOT NULL DEFAULT '',
    body TEXT NOT NULL,
    article_slug VARCHAR(180) NOT NULL DEFAULT '',
    is_read TINYINT(1) NOT NULL DEFAULT 0,
    is_approved TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_messages_inbox (message_type, is_read, created_at),
    KEY idx_messages_article (article_slug, is_approved)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS site_settings (
    setting_key VARCHAR(120) NOT NULL,
    setting_value MEDIUMTEXT NOT NULL,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (setting_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
