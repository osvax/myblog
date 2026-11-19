-- ============================================================
-- Миграция 015: колонка referrer в таблице user_activities
-- (страница входа — для блока «Трафик сайта» на дашборде)
--
-- Применяется ПОСЛЕ 014. Для уже развёрнутой БД выполнять
-- database/migrations/run_015.php (идемпотентная).
-- ============================================================

ALTER TABLE `va_user_activities`
    ADD COLUMN `referrer` varchar(500) NULL DEFAULT NULL COMMENT 'Страница входа (HTTP_REFERER)' AFTER `user_agent`;
