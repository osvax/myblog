ALTER TABLE users
    ADD COLUMN IF NOT EXISTS yandex_id VARCHAR(128) NOT NULL DEFAULT '' AFTER email;

INSERT INTO site_settings (setting_key, setting_value, setting_type, tab_group, display_order, placeholder)
VALUES
 ('adminLoginEmail', 'osvax@yandex.ru', 'email', 'authentication', 1, 'Email для входа в админ-панель'),
 ('auth2faEnabled', '0', 'checkbox', 'authentication', 10, ''),
 ('yandexAuthEnabled', '0', 'checkbox', 'authentication', 20, ''),
 ('yandexClientId', '', 'text', 'authentication', 30, 'ID приложения Яндекс'),
 ('yandexClientSecret', '', 'text', 'authentication', 40, 'Секрет приложения Яндекс'),
 ('yandexRedirectUri', '', 'url', 'authentication', 50, 'https://example.com/api/auth/yandex/callback')
ON DUPLICATE KEY UPDATE setting_key = VALUES(setting_key);
