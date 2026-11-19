<?php

define('APP_ROOT', 'E:/OSPanel/www-html/myblog.loc');
require APP_ROOT . '/app/bootstrap.php';

$db = \App\Core\Database::connection();

echo "=== Creating site_settings table ===\n";

// Drop table if exists
try {
    $db->exec("DROP TABLE IF EXISTS " . db_table('site_settings') . "");
    echo "  Old table dropped\n";
} catch (\Exception $e) {
    // Ignore
}

$sql = file_get_contents(APP_ROOT . '/database/migrations/006_site_settings.sql');
if ($sql) {
    $db->exec($sql);
    echo "  Table created\n";
} else {
    echo "  Error: SQL file not found\n";
    exit(1);
}

// Verify table exists
try {
    $stmt = $db->query("SHOW COLUMNS FROM " . db_table('site_settings') . "");
    $cols = $stmt->fetchAll();
    echo "  Columns: " . count($cols) . "\n";
    foreach ($cols as $col) {
        echo "    - " . $col['Field'] . "\n";
    }
} catch (\Exception $e) {
    echo "  Error checking table: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n=== Populating with default settings ===\n";

$defaults = [
    // Основные
    ['siteName', 'МойБлог', 'text', 'general', 1, 'Название сайта', ''],
    ['siteTitle', 'МойБлог — Портфолио и блог', 'text', 'general', 2, 'Заголовок вкладки (SEO)', ''],
    ['siteDescription', 'Портфолио веб-разработчика. Блог о верстке, Bootstrap 5 и современных технологиях.', 'textarea', 'general', 3, 'Описание сайта (SEO description)', ''],
    ['adminEmail', 'admin@moyblog.ru', 'email', 'general', 4, 'Email администратора', ''],
    ['siteLanguage', 'Русский', 'select', 'general', 5, 'Язык сайта', '["Русский","English"]'],

    // Внешний вид
    ['primaryColor', '#ff8c1a', 'color', 'appearance', 1, 'Основной цвет', ''],
    ['headingFont', 'Montserrat', 'select', 'appearance', 2, 'Шрифт заголовков', '["Montserrat","Roboto","Open Sans"]'],
    ['darkMode', '1', 'checkbox', 'appearance', 3, 'Тёмная тема админки', ''],
    ['sidebarCollapse', '1', 'checkbox', 'appearance', 4, 'Свёрнутый сайдбар по умолчанию', ''],

    // SEO
    ['keywords', 'блог, портфолио, верстка, bootstrap, html, css, javascript', 'text', 'seo', 1, 'Ключевые слова (через запятую)', ''],
    ['sitemap', '1', 'checkbox', 'seo', 2, 'Генерировать sitemap.xml', ''],
    ['robots', '1', 'checkbox', 'seo', 3, 'Разрешить индексацию', ''],

    // Уведомления
    ['notifyOrders', '1', 'checkbox', 'notifications', 1, 'Новые заказы', ''],
    ['notifyMessages', '1', 'checkbox', 'notifications', 2, 'Новые сообщения', ''],
    ['notifyComments', '0', 'checkbox', 'notifications', 3, 'Новые комментарии', ''],
    ['notifyUsers', '0', 'checkbox', 'notifications', 4, 'Регистрация пользователей', ''],

    // Консультант (уже есть в consultant API, но дублируем здесь)
    ['consultantEnabled', '1', 'checkbox', 'consultant', 1, 'Включить консультанта', ''],
    ['consultantWorkingHours', '09:00-20:00', 'text', 'consultant', 2, 'Время работы', ''],
    ['consultantButtonColor', '#ff8c1a', 'color', 'consultant', 3, 'Цвет кнопки', ''],
    ['consultantButtonTextColor', '#ffffff', 'color', 'consultant', 4, 'Цвет текста кнопки', ''],
    ['consultantButtonPosition', 'right', 'select', 'consultant', 5, 'Расположение кнопки', '["right","left"]'],
    ['consultantWelcomeMessage', 'Здравствуйте! Чем могу помочь?', 'text', 'consultant', 6, 'Приветствие', ''],
    ['consultantNotificationEmail', '', 'email', 'consultant', 7, 'Email для уведомлений', ''],
    ['consultantCensoredWords', 'дурак, идиот, тупица', 'textarea', 'consultant', 8, 'Слова для цензуры', ''],

    // Контакты
    ['contactAddress', 'г. Самара, ул. Примерная, 1', 'text', 'contacts', 1, 'Адрес', ''],
    ['contactHours', 'Пн–Пт, 10:00–18:00', 'text', 'contacts', 2, 'Часы работы', ''],
    ['contactEmail', 'hello@moyblog.ru', 'email', 'contacts', 3, 'Публичный email', ''],
    ['contactPhone', '+7 (900) 123-45-67', 'tel', 'contacts', 4, 'Телефон', ''],
    ['socialTelegram', '', 'url', 'contacts', 5, 'Ссылка на Telegram', ''],
    ['socialVk', '', 'url', 'contacts', 6, 'Ссылка на VK', ''],
    ['socialGithub', '', 'url', 'contacts', 7, 'Ссылка на GitHub', ''],
    ['socialYoutube', '', 'url', 'contacts', 8, 'Ссылка на YouTube', ''],

    // Безопасность
    ['siteStatus', 'open', 'select', 'security', 1, 'Доступность сайта', '["open","maintenance"]'],
    ['twoFactorMethod', 'off', 'select', 'security', 2, 'Двухфакторная аутентификация', '["off","authenticator","sms"]'],
    ['maintenanceMessage', 'Сайт временно закрыт на техническое обслуживание. Скоро вернёмся!', 'textarea', 'security', 3, 'Сообщение на время обслуживания', ''],
    ['adminIpAllowlist', '', 'textarea', 'security', 4, 'Разрешённые IP для входа в админку', ''],
    ['securityContactEmail', '', 'email', 'security', 5, 'Email для уведомлений о безопасности', ''],
    ['twoFactorPhone', '', 'tel', 'security', 6, 'Телефон для SMS-кодов', ''],
    ['maxLoginAttempts', '5', 'number', 'security', 7, 'Попытки входа до блокировки', ''],
    ['lockoutMinutes', '15', 'number', 'security', 8, 'Время блокировки (минуты)', ''],
    ['adminSessionHours', '8', 'number', 'security', 9, 'Срок сессии админки (часы)', ''],
    ['loginCaptcha', '0', 'checkbox', 'security', 10, 'Проверка CAPTCHA при входе', ''],
    ['securityLoginAlerts', '1', 'checkbox', 'security', 11, 'Уведомлять о новых входах', ''],

    // Email (SMTP)
    ['smtpHost', '', 'text', 'email', 1, 'SMTP-сервер', ''],
    ['smtpPort', '587', 'number', 'email', 2, 'Порт', ''],
    ['smtpEncryption', 'tls', 'select', 'email', 3, 'Шифрование', '["tls","ssl","none"]'],
    ['smtpUsername', '', 'text', 'email', 4, 'Имя пользователя', ''],
    ['smtpPassword', '', 'text', 'email', 5, 'Пароль приложения', ''],
    ['smtpFromName', 'МойБлог', 'text', 'email', 6, 'Имя отправителя', ''],
    ['smtpFromEmail', '', 'email', 'email', 7, 'Email отправителя', ''],
    ['smtpRecipient', '', 'email', 'email', 8, 'Email получателя заявок', ''],
    ['smtpReplyTo', '', 'email', 'email', 9, 'Ответы направлять на', ''],
];

$stmt = $db->prepare(
    "INSERT INTO " . db_table('site_settings') . " (setting_key, setting_value, setting_type, tab_group, display_order, placeholder, options)
     VALUES (:key, :value, :type, :group, :order, :placeholder, :options)
     ON DUPLICATE KEY UPDATE
         setting_value = VALUES(setting_value),
         setting_type = VALUES(setting_type),
         tab_group = VALUES(tab_group),
         display_order = VALUES(display_order),
         placeholder = VALUES(placeholder),
         options = VALUES(options)"
);

$count = 0;
foreach ($defaults as $default) {
    $stmt->execute([
        ':key' => $default[0],
        ':value' => $default[1],
        ':type' => $default[2],
        ':group' => $default[3],
        ':order' => $default[4],
        ':placeholder' => $default[5],
        ':options' => $default[6] ?: null,
    ]);
    $count++;
}

echo "  Inserted/updated $count settings\n";
echo "\nDone.\n";
