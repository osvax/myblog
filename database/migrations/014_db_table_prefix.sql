-- ============================================================
-- Миграция 014: префикс таблиц (DB_PREFIX=va_ из .env) +
-- новые таблицы проектов портфолио (галерея, доп. поля)
--
-- Применяется ПОСЛЕ 001–013. Для уже развёрнутой БД выполнять
-- database/migrations/run_014.php (идемпотентный, переименовывает
-- существующие таблицы вместо повторного создания).
-- ============================================================

-- Снимки проектов с описаниями
CREATE TABLE IF NOT EXISTS `va_project_gallery` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `project_id` char(32) NOT NULL,
  `path` varchar(255) NOT NULL,
  `description` varchar(500) NOT NULL DEFAULT '',
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_project_gallery_project` (`project_id`,`sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Дополнительные поля проекта: ссылки, документы (ТЗ), сертификаты
CREATE TABLE IF NOT EXISTS `va_project_info_items` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `project_id` char(32) NOT NULL,
  `kind` varchar(20) NOT NULL,
  `name` varchar(160) NOT NULL,
  `value` varchar(500) NOT NULL DEFAULT '',
  `file_path` varchar(255) NOT NULL DEFAULT '',
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_project_info_items_project` (`project_id`,`sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Применяем префикс ко всем таблицам, созданным миграциями 001–013
RENAME TABLE
  `articles` TO `va_articles`,
  `categories` TO `va_categories`,
  `consultant_message_reads` TO `va_consultant_message_reads`,
  `consultant_messages` TO `va_consultant_messages`,
  `consultant_reads` TO `va_consultant_reads`,
  `consultant_settings` TO `va_consultant_settings`,
  `consultant_typing` TO `va_consultant_typing`,
  `consultant_users` TO `va_consultant_users`,
  `custom_fields` TO `va_custom_fields`,
  `landing_page_settings` TO `va_landing_page_settings`,
  `landing_pages` TO `va_landing_pages`,
  `menu_items` TO `va_menu_items`,
  `menus` TO `va_menus`,
  `messages` TO `va_messages`,
  `orders` TO `va_orders`,
  `portfolio_projects` TO `va_portfolio_projects`,
  `role_permissions` TO `va_role_permissions`,
  `roles` TO `va_roles`,
  `site_settings` TO `va_site_settings`,
  `static_pages` TO `va_static_pages`,
  `user_activities` TO `va_user_activities`,
  `users` TO `va_users`;
