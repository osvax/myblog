INSERT IGNORE INTO site_settings (setting_key, setting_value, setting_type, tab_group, display_order, placeholder)
VALUES
 ('adminLoginEmail', 'osvax@yandex.ru', 'email', 'authentication', 1, 'Email для входа в админ-панель'),
 ('frontendTemplate', 'default', 'select', 'templates', 1, 'Шаблон публичной части'),
 ('adminTemplate', 'default', 'select', 'templates', 2, 'Шаблон админ-панели');
