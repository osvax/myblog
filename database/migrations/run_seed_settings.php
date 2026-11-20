<?php
/**
 * Заполняет таблицу site_settings дефолтными настройками.
 * Запуск: php database/migrations/run_seed_settings.php
 */

require dirname(__DIR__, 2) . '/app/bootstrap.php';

$db = \App\Core\Database::connection();

$count = (int) $db->query('SELECT COUNT(*) FROM ' . db_table('site_settings') . '')->fetchColumn();
echo $count > 0
    ? "В таблице site_settings уже {$count} записей. Обновляем...\n"
    : "Заполняем site_settings...\n";

$defaults = [
    // === general ===
    ['siteName', 'МойБлог', 'text', 'general', 1, 'Название сайта'],
    ['siteTitle', 'МойБлог — Портфолио и блог', 'text', 'general', 2, 'Заголовок вкладки (SEO)'],
    ['siteDescription', 'Портфолио веб-разработчика. Блог о верстке, Bootstrap 5 и современных технологиях.', 'text', 'general', 3, 'SEO description'],
    ['adminEmail', 'admin@moyblog.ru', 'email', 'general', 4, 'Email администратора'],
    ['siteLanguage', 'Русский', 'text', 'general', 5, 'Язык сайта'],
    ['footerText', '© {year} МойБлог. Все права защищены.', 'text', 'general', 6, 'Текст футера'],

    // === home ===
    ['homePageMode', 'main', 'select', 'home', 1, 'Режим главной'],
    ['heroTitle', 'Личный блог о веб-разработке и дизайне', 'text', 'home', 2, 'Заголовок героя'],
    ['heroSubtitle', 'Здесь я делюсь опытом, статьями и проектами.', 'text', 'home', 3, 'Подзаголовок героя'],
    ['homeFeaturedCount', '3', 'number', 'home', 4, 'Статей в разделе «Свежие статьи»'],
    ['heroPrimaryText', 'Читать блог', 'text', 'home', 5, 'Текст главной кнопки'],
    ['heroPrimaryUrl', '/blog.html', 'text', 'home', 6, 'Ссылка главной кнопки'],
    ['heroSecondaryText', 'Заказать проект', 'text', 'home', 7, 'Текст второй кнопки'],
    ['heroSecondaryUrl', '/order.html', 'text', 'home', 8, 'Ссылка второй кнопки'],
    ['step1Title', 'Оставляете заявку', 'text', 'home', 9, 'Шаг 1 — заголовок'],
    ['step1Text', 'Заполняете форму на странице заказа — 2 минуты', 'text', 'home', 10, 'Шаг 1 — описание'],
    ['step2Title', 'Обсуждаем детали', 'text', 'home', 11, 'Шаг 2 — заголовок'],
    ['step2Text', 'Связываюсь с вами в течение дня и уточняем задачу', 'text', 'home', 12, 'Шаг 2 — описание'],
    ['step3Title', 'Получаете результат', 'text', 'home', 13, 'Шаг 3 — заголовок'],
    ['step3Text', 'Готовый сайт точно в срок и с гарантией', 'text', 'home', 14, 'Шаг 3 — описание'],

    // === headings ===
    ['blogTitle', 'Блог', 'text', 'headings', 1, 'Блог — заголовок'],
    ['blogLead', 'Последние записи и полезные материалы', 'text', 'headings', 2, 'Блог — подзаголовок'],
    ['portfolioTitle', 'Портфолио', 'text', 'headings', 3, 'Портфолио — заголовок'],
    ['portfolioLead', 'Мои лучшие работы и проекты', 'text', 'headings', 4, 'Портфолио — подзаголовок'],
    ['contactsTitle', 'Контакты', 'text', 'headings', 5, 'Контакты — заголовок'],
    ['contactsLead', 'Буду рад связи и сотрудничеству', 'text', 'headings', 6, 'Контакты — подзаголовок'],
    ['orderTitle', 'Заказать', 'text', 'headings', 7, 'Заказать — заголовок'],
    ['orderLead', 'Расскажите о своей задаче — я свяжусь с вами', 'text', 'headings', 8, 'Заказать — подзаголовок'],

    // === mail ===
    ['mailOrderSubject', 'Новая заявка с сайта', 'text', 'mail', 1, 'Тема письма заявки'],
    ['mailOrderBody', 'Имя: {name}\nEmail: {email}\nТелефон: {phone}\nТип: {type}\nБюджет: {budget}\nСообщение: {message}', 'text', 'mail', 2, 'Текст письма заявки'],
    ['mailFeedbackSubject', 'Обратная связь с сайта', 'text', 'mail', 3, 'Тема письма обратной связи'],
    ['mailFeedbackBody', 'Имя: {name}\nEmail: {email}\nТема: {subject}\nСообщение: {message}', 'text', 'mail', 4, 'Текст письма обратной связи'],
    ['mailCommentSubject', 'Новый комментарий к статье', 'text', 'mail', 5, 'Тема письма комментария'],
    ['mailCommentBody', 'Имя: {name}\nEmail: {email}\nСтатья: {article}\nКомментарий: {message}', 'text', 'mail', 6, 'Текст письма комментария'],

    // === seo ===
    ['keywords', 'блог, портфолио, верстка, bootstrap, html, css, javascript', 'text', 'seo', 1, 'Ключевые слова'],
    ['sitemap', '1', 'checkbox', 'seo', 2, 'Генерировать sitemap.xml'],
    ['robots', '1', 'checkbox', 'seo', 3, 'Разрешить индексацию'],

    // === notifications ===
    ['notifyOrders', '1', 'checkbox', 'notifications', 1, 'Новые заказы'],
    ['notifyMessages', '1', 'checkbox', 'notifications', 2, 'Новые сообщения'],
    ['notifyComments', '0', 'checkbox', 'notifications', 3, 'Новые комментарии'],
    ['notifyUsers', '0', 'checkbox', 'notifications', 4, 'Регистрация пользователей'],

    // === consultant ===
    ['consultantEnabled', '1', 'checkbox', 'consultant', 1, 'Включить консультанта'],
    ['consultantWorkingHours', '09:00-20:00', 'text', 'consultant', 2, 'Время работы'],
    ['consultantButtonColor', '#ff8c1a', 'color', 'consultant', 3, 'Цвет кнопки'],
    ['consultantButtonTextColor', '#ffffff', 'color', 'consultant', 4, 'Цвет текста кнопки'],
    ['consultantButtonPosition', 'right', 'select', 'consultant', 5, 'Расположение кнопки'],
    ['consultantWelcomeMessage', 'Здравствуйте! Чем могу помочь?', 'text', 'consultant', 6, 'Приветствие'],
    ['consultantNotificationEmail', '', 'email', 'consultant', 7, 'Email для уведомлений'],
    ['consultantCensoredWords', 'дурак, идиот, тупица', 'text', 'consultant', 8, 'Слова для цензуры'],

    // === contacts ===
    ['contactAddress', 'г. Самара, ул. Примерная, 1', 'text', 'contacts', 1, 'Адрес'],
    ['contactHours', 'Пн-Пт: 9:00 — 18:00', 'text', 'contacts', 2, 'Часы работы'],
    ['contactEmail', 'hello@moyblog.ru', 'email', 'contacts', 3, 'Публичный email'],
    ['contactPhone', '+7 (900) 123-45-67', 'tel', 'contacts', 4, 'Телефон'],
    ['socialTelegram', 'https://t.me/moyblog', 'url', 'contacts', 5, 'Telegram'],
    ['socialVk', 'https://vk.com/moyblog', 'url', 'contacts', 6, 'VK'],
    ['socialGithub', 'https://github.com/moyblog', 'url', 'contacts', 7, 'GitHub'],
    ['socialYoutube', 'https://youtube.com/@moyblog', 'url', 'contacts', 8, 'YouTube'],

    // === security ===
    ['siteStatus', 'online', 'select', 'security', 1, 'Режим сайта'],
    ['maintenanceMessage', 'Сайт временно закрыт на техническое обслуживание. Скоро вернёмся!', 'text', 'security', 2, 'Сообщение на время обслуживания'],
    ['maintenanceStart', '', 'text', 'security', 3, 'Обслуживание с (от), Y-m-d H:i'],
    ['maintenanceEnd', '', 'text', 'security', 4, 'Обслуживание до (по), Y-m-d H:i'],
    ['maintenanceAllowedIps', '', 'text', 'security', 5, 'IP-адреса с доступом при обслуживании'],
    ['blockedIps', '', 'text', 'security', 6, 'IP-адреса без доступа (блокировка)'],
    ['maxLoginAttempts', '5', 'number', 'security', 7, 'Попытки входа до блокировки'],
    ['lockoutMinutes', '15', 'number', 'security', 8, 'Время блокировки (минуты)'],
    ['adminSessionHours', '8', 'number', 'security', 9, 'Срок сессии админки (часы)'],

    // === logs (журнал событий; таблица logs) ===
    ['logsEnabled', '1', 'checkbox', 'logs', 1, 'Вести журнал событий'],
    ['logsAutoClean', '1', 'checkbox', 'logs', 2, 'Автоочистка журнала по сроку хранения'],
    ['logsRetentionDays', '30', 'number', 'logs', 3, 'Срок хранения записей журнала (дней)'],
    ['logsAuth', '1', 'checkbox', 'logs', 4, 'Журнал: входы и регистрации'],
    ['logsForms', '1', 'checkbox', 'logs', 5, 'Журнал: отправка форм'],
    ['logsSecurity', '1', 'checkbox', 'logs', 6, 'Журнал: блокировки и отказы в доступе'],
    ['logsErrors', '1', 'checkbox', 'logs', 7, 'Журнал: ошибки сервера 500'],

    // === email ===
    ['smtpEnabled', '1', 'checkbox', 'email', 1, 'Отправлять письма через SMTP (иначе PHP mail())'],
    ['smtpHost', '', 'text', 'email', 2, 'SMTP-сервер'],
    ['smtpPort', '587', 'number', 'email', 3, 'Порт'],
    ['smtpEncryption', 'tls', 'select', 'email', 4, 'Шифрование'],
    ['smtpUsername', '', 'text', 'email', 5, 'Имя пользователя'],
    ['smtpFromName', 'МойБлог', 'text', 'email', 6, 'Имя отправителя'],
    ['smtpFromEmail', 'no-reply@example.com', 'email', 'email', 7, 'Email отправителя'],
    ['smtpRecipient', 'admin@example.com', 'email', 'email', 8, 'Email получателя заявок'],
    ['smtpReplyTo', '', 'email', 'email', 9, 'Ответы направлять на'],

    // === articles ===
    ['articlePerPage', '12', 'number', 'articles', 1, 'Статей на странице'],
    ['articleSortOrder', 'newest', 'select', 'articles', 2, 'Сортировка статей'],
    ['articleExcerptLength', '300', 'number', 'articles', 3, 'Длина анонса (символов)'],
    ['articleCommentsEnabled', '1', 'checkbox', 'articles', 4, 'Разрешить комментарии'],
    ['commentsAccess', 'users', 'select', 'articles', 5, 'Кто видит комментарии'],
    ['articleTagsEnabled', '1', 'checkbox', 'articles', 6, 'Показывать теги'],
    ['articleCategoriesEnabled', '1', 'checkbox', 'articles', 7, 'Показывать рубрики'],
    ['articleReadTime', '1', 'checkbox', 'articles', 8, 'Показывать время чтения'],
    ['articleRelatedEnabled', '1', 'checkbox', 'articles', 9, 'Показывать похожие статьи'],
    ['articleShareButtons', '1', 'checkbox', 'articles', 10, 'Кнопки «Поделиться»'],

    // === portfolio ===
    ['portfolioPerPage', '12', 'number', 'portfolio', 1, 'Работ на странице'],
    ['portfolioLayout', 'grid', 'select', 'portfolio', 2, 'Тип отображения'],
    ['portfolioShowTags', '1', 'checkbox', 'portfolio', 3, 'Показывать технологии'],
    ['portfolioShowDate', '1', 'checkbox', 'portfolio', 4, 'Показывать год'],
    ['portfolioShowClient', '1', 'checkbox', 'portfolio', 5, 'Показывать клиента'],
];

// Ключи, которые добавляются только при отсутствии в БД (INSERT IGNORE):
// пользовательские предпочтения и секреты нельзя перезаписывать сидом.
$defaultsIgnore = [
    // === appearance ===
    ['primaryColor', '#f11b04', 'color', 'appearance', 1, 'Основной цвет'],
    ['headingFont', 'Montserrat', 'select', 'appearance', 2, 'Шрифт заголовков'],
    ['darkMode', '0', 'checkbox', 'appearance', 3, 'Тёмная тема админки'],
    ['sidebarCollapse', '0', 'checkbox', 'appearance', 4, 'Свёрнутый сайдбар по умолчанию'],

    // === calculator ===
    ['calculatorConfig', json_encode(calculator_default_config(), JSON_UNESCAPED_UNICODE), 'json', 'calculator', 1, 'Конфигурация калькулятора заказа'],

    // === statistics (раздел «Статистика сайта»; управление — на самой странице статистики) ===
    ['statisticsRetentionDays', '90', 'number', 'statistics', 1, 'Срок хранения посещений (дней), 0 — без ограничения'],
    ['statisticsAutoClean', '1', 'checkbox', 'statistics', 2, 'Автоочистка записей по сроку хранения'],
    ['statisticsSections', json_encode(['home', 'blog', 'portfolio', 'auth', 'other'], JSON_UNESCAPED_SLASHES), 'json', 'statistics', 3, 'Разделы, по которым ведётся статистика (JSON-массив)'],
    ['statisticsUpdateInterval', '900', 'number', 'statistics', 4, 'Интервал обновления статистики (секунды), дашборд'],

    // === email (секреты — только пустой дефолт, значение пользователь вводит сам) ===
    ['smtpPassword', '', 'text', 'email', 10, 'Пароль приложения'],
];

$stmt = $db->prepare(
    "INSERT INTO " . db_table('site_settings') . " (setting_key, setting_value, setting_type, tab_group, display_order, placeholder)
     VALUES (:key, :value, :type, :group, :order, :placeholder)
     ON DUPLICATE KEY UPDATE
         setting_value = VALUES(setting_value),
         setting_type = VALUES(setting_type),
         tab_group = VALUES(tab_group),
         display_order = VALUES(display_order),
         placeholder = VALUES(placeholder)"
);

$inserted  = 0;
$updated   = 0;
$unchanged = 0;
$skipped   = 0;

$stmtIgnore = $db->prepare(
    "INSERT IGNORE INTO " . db_table('site_settings') . " (setting_key, setting_value, setting_type, tab_group, display_order, placeholder)
     VALUES (:key, :value, :type, :group, :order, :placeholder)"
);

$db->beginTransaction();
try {
    foreach ($defaults as $d) {
        $stmt->execute([
            ':key'         => $d[0],
            ':value'       => $d[1],
            ':type'        => $d[2],
            ':group'       => $d[3],
            ':order'       => $d[4],
            ':placeholder' => $d[5],
        ]);
        $affected = $stmt->rowCount();
        if ($affected === 1) {
            $inserted++;
        } elseif ($affected === 2) {
            $updated++;
        } else {
            $unchanged++;
        }
    }

    foreach ($defaultsIgnore as $d) {
        $stmtIgnore->execute([
            ':key'         => $d[0],
            ':value'       => $d[1],
            ':type'        => $d[2],
            ':group'       => $d[3],
            ':order'       => $d[4],
            ':placeholder' => $d[5],
        ]);
        if ($stmtIgnore->rowCount() === 1) {
            $inserted++;
        } else {
            $skipped++;
        }
    }

    $db->commit();
} catch (\Throwable $e) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    echo "Ошибка сида: " . $e->getMessage() . "\n";
    exit(1);
}

echo "Готово: {$inserted} добавлено, {$updated} обновлено, {$unchanged} без изменений, {$skipped} пропущено (уже есть).\n";
