<?php

define('APP_ROOT', 'E:/OSPanel/www-html/myblog.loc');
require APP_ROOT . '/app/bootstrap.php';

$db = \App\Core\Database::connection();

echo "=== Creating landing_page_settings table ===\n";

try {
    $db->exec("DROP TABLE IF EXISTS landing_page_settings");
    echo "  Old table dropped\n";
} catch (\Exception $e) {
    // Ignore
}

$sql = file_get_contents(APP_ROOT . '/database/migrations/008_landing_page_settings.sql');
if ($sql) {
    $db->exec($sql);
    echo "  Table created\n";
} else {
    echo "  Error: SQL file not found\n";
    exit(1);
}

// Verify table exists
try {
    $stmt = $db->query("SHOW COLUMNS FROM landing_page_settings");
    $cols = $stmt->fetchAll();
    echo "  Columns: " . count($cols) . "\n";
} catch (\Exception $e) {
    echo "  Error checking table: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n=== Populating with default landing page sections ===\n";

$defaults = [
    // Hero секция
    ['hero', 'hero', 'title', 'Добро пожаловать на наш сайт', 'text', 'hero', 1, 'Заголовок', ''],
    ['hero', 'hero', 'subtitle', 'Создаём цифровые решения для вашего бизнеса', 'textarea', 'hero', 2, 'Подзаголовок', ''],
    ['hero', 'hero', 'cta_text', 'Начать', 'text', 'hero', 3, 'Текст кнопки', ''],
    ['hero', 'hero', 'cta_url', '#contact', 'url', 'hero', 4, 'Ссылка кнопки', ''],
    ['hero', 'hero', 'background_image', '', 'image', 'hero', 5, 'Фоновое изображение', ''],
    ['hero', 'hero', 'background_color', '#ffffff', 'color', 'hero', 6, 'Цвет фона', ''],
    ['hero', 'hero', 'background_video', '', 'video', 'hero', 7, 'Видео фон (URL)', ''],
    ['hero', 'hero', 'display_order', '1', 'number', 'hero', 8, 'Порядок отображения', ''],

    // Features секция
    ['features', 'features', 'title', 'Наши преимущества', 'text', 'features', 1, 'Заголовок секции', ''],
    ['features', 'features', 'subtitle', 'Почему выбирают нас', 'textarea', 'features', 2, 'Подзаголовок', ''],
    ['features', 'features', 'layout', 'grid', 'select', 'features', 3, 'Макет', '["grid","list"]'],
    ['features', 'features', 'display_order', '2', 'number', 'features', 4, 'Порядок отображения', ''],

    // About секция
    ['about', 'about', 'title', 'О нас', 'text', 'about', 1, 'Заголовок', ''],
    ['about', 'about', 'content', 'Мы — команда профессионалов...', 'rich_text', 'about', 2, 'Содержание', ''],
    ['about', 'about', 'image', '', 'image', 'about', 3, 'Изображение', ''],
    ['about', 'about', 'display_order', '3', 'number', 'about', 4, 'Порядок отображения', ''],

    // Services секция
    ['services', 'services', 'title', 'Услуги', 'text', 'services', 1, 'Заголовок', ''],
    ['services', 'services', 'subtitle', 'Что мы предлагаем', 'textarea', 'services', 2, 'Подзаголовок', ''],
    ['services', 'services', 'display_order', '4', 'number', 'services', 3, 'Порядок отображения', ''],

    // Portfolio секция
    ['portfolio', 'portfolio', 'title', 'Портфолио', 'text', 'portfolio', 1, 'Заголовок', ''],
    ['portfolio', 'portfolio', 'subtitle', 'Наши работы', 'textarea', 'portfolio', 2, 'Подзаголовок', ''],
    ['portfolio', 'portfolio', 'display_order', '5', 'number', 'portfolio', 3, 'Порядок отображения', ''],

    // Testimonials секция
    ['testimonials', 'testimonials', 'title', 'Отзывы', 'text', 'testimonials', 1, 'Заголовок', ''],
    ['testimonials', 'testimonials', 'subtitle', 'Что говорят наши клиенты', 'textarea', 'testimonials', 2, 'Подзаголовок', ''],
    ['testimonials', 'testimonials', 'display_order', '6', 'number', 'testimonials', 3, 'Порядок отображения', ''],

    // Contact секция
    ['contact', 'contact', 'title', 'Контакты', 'text', 'contact', 1, 'Заголовок', ''],
    ['contact', 'contact', 'address', 'г. Москва, ул. Примерная, 1', 'text', 'contact', 2, 'Адрес', ''],
    ['contact', 'contact', 'phone', '+7 (900) 123-45-67', 'tel', 'contact', 3, 'Телефон', ''],
    ['contact', 'contact', 'email', 'info@example.com', 'email', 'contact', 4, 'Email', ''],
    ['contact', 'contact', 'map_embed', '', 'textarea', 'contact', 5, 'Код вставки карты', ''],
    ['contact', 'contact', 'display_order', '7', 'number', 'contact', 6, 'Порядок отображения', ''],

    // Footer секция
    ['footer', 'footer', 'copyright', '© 2024 Название компании', 'text', 'footer', 1, 'Копирайт', ''],
    ['footer', 'footer', 'background_color', '#333333', 'color', 'footer', 2, 'Цвет фона', ''],
    ['footer', 'footer', 'text_color', '#ffffff', 'color', 'footer', 3, 'Цвет текста', ''],
    ['footer', 'footer', 'display_order', '8', 'number', 'footer', 4, 'Порядок отображения', ''],

    // Global секция
    ['global', 'global', 'favicon', '', 'image', 'global', 1, 'Favicon', ''],
    ['global', 'global', 'logo', '', 'image', 'global', 2, 'Логотип', ''],
    ['global', 'global', 'logo_dark', '', 'image', 'global', 3, 'Логотип (тёмная тема)', ''],
    ['global', 'global', 'primary_color', '#ff8c1a', 'color', 'global', 4, 'Основной цвет', ''],
    ['global', 'global', 'secondary_color', '#333333', 'color', 'global', 5, 'Вторичный цвет', ''],
    ['global', 'global', 'google_fonts', '', 'textarea', 'global', 6, 'Google Fonts (код)', ''],
    ['global', 'global', 'google_analytics', '', 'text', 'global', 7, 'Google Analytics ID', ''],
    ['global', 'global', 'custom_css', '', 'textarea', 'global', 8, 'Пользовательский CSS', ''],
    ['global', 'global', 'custom_js', '', 'textarea', 'global', 9, 'Пользовательский JS', ''],
];

$stmt = $db->prepare(
    "INSERT INTO landing_page_settings (section, section_key, field_name, field_value, field_type, field_group, display_order, placeholder, options)
     VALUES (:section, :section_key, :name, :value, :type, :group, :order, :placeholder, :options)
     ON DUPLICATE KEY UPDATE
         field_value = VALUES(field_value),
         field_type = VALUES(field_type),
         field_group = VALUES(field_group),
         display_order = VALUES(display_order),
         placeholder = VALUES(placeholder),
         options = VALUES(options)"
);

$count = 0;
foreach ($defaults as $default) {
    $stmt->execute([
        ':section' => $default[0],
        ':section_key' => $default[1],
        ':name' => $default[2],
        ':value' => $default[3],
        ':type' => $default[4],
        ':group' => $default[5],
        ':order' => $default[6],
        ':placeholder' => $default[7],
        ':options' => $default[8] ?: null,
    ]);
    $count++;
}

echo "  Inserted/updated $count settings\n";
echo "\nDone.\n";
