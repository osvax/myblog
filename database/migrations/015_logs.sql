-- ============================================================
-- Миграция 015: журнал событий сайта (настройки — «Логи» в админке)
--
-- Типы событий: auth, forms, security, errors.
-- Для уже развёрнутой БД достаточно CREATE TABLE IF NOT EXISTS.
-- ============================================================

CREATE TABLE IF NOT EXISTS `va_logs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `log_type` varchar(20) NOT NULL,
  `message` varchar(500) NOT NULL DEFAULT '',
  `user_id` int(10) unsigned DEFAULT NULL,
  `ip` varchar(45) NOT NULL DEFAULT '',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_logs_type_created` (`log_type`,`created_at`),
  KEY `idx_logs_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
