<?php
/**
 * Единый список разделов админки для бокового меню и прав доступа.
 * slug — это и ключ права, и имя страницы /admin/<slug>.html.
 * Пункт без 'type' => 'group' — плоская ссылка (не выпадающая группа).
 */
return [
    ['slug' => 'index', 'url' => '/admin/index.html', 'icon' => 'bi-speedometer2', 'label' => 'Дашборд'],
    ['slug' => 'statistics', 'url' => '/admin/statistics.html', 'icon' => 'bi-graph-up', 'label' => 'Статистика'],
    ['type' => 'group', 'id' => 'group-content', 'icon' => 'bi-collection', 'label' => 'Контент', 'items' => [
        ['slug' => 'blog', 'url' => '/admin/blog.html', 'icon' => 'bi-journal-text', 'label' => 'Блог'],
        ['slug' => 'categories', 'url' => '/admin/categories.html', 'icon' => 'bi-tags', 'label' => 'Рубрики'],
        ['slug' => 'portfolio', 'url' => '/admin/portfolio.html', 'icon' => 'bi-briefcase', 'label' => 'Портфолио'],
        ['slug' => 'landing-pages', 'url' => '/admin/landing-pages.html', 'icon' => 'bi-window-desktop', 'label' => 'Страницы'],
        ['slug' => 'custom-fields', 'url' => '/admin/custom-fields.html', 'icon' => 'bi-input-cursor-text', 'label' => 'Пользовательские поля'],
        ['slug' => 'menus', 'url' => '/admin/menus.html', 'icon' => 'bi-menu-button-wide', 'label' => 'Меню'],
        ['slug' => 'templates', 'url' => '/admin/templates.html', 'icon' => 'bi-code-slash', 'label' => 'Шаблоны'],
    ]],
    ['type' => 'group', 'id' => 'group-orders', 'icon' => 'bi-cart3', 'label' => 'Заказы', 'items' => [
        ['slug' => 'new-orders', 'url' => '/admin/new-orders.html', 'icon' => 'bi-bell', 'label' => 'Новые'],
        ['slug' => 'current-orders', 'url' => '/admin/current-orders.html', 'icon' => 'bi-hourglass-split', 'label' => 'Текущие'],
        ['slug' => 'orders', 'url' => '/admin/orders.html', 'icon' => 'bi-list-ul', 'label' => 'Все заказы'],
        ['slug' => 'calculator', 'url' => '/admin/calculator.html', 'icon' => 'bi-calculator', 'label' => 'Калькулятор'],
    ]],
    ['type' => 'group', 'id' => 'group-people', 'icon' => 'bi-chat-dots', 'label' => 'Общение и клиенты', 'items' => [
        ['slug' => 'messages', 'url' => '/admin/messages.html', 'icon' => 'bi-chat-left-text', 'label' => 'Сообщения'],
        ['slug' => 'consultant', 'url' => '/admin/consultant.html', 'icon' => 'bi-headset', 'label' => 'Консультант'],
    ]],
    ['type' => 'group', 'id' => 'group-system', 'icon' => 'bi-sliders2', 'label' => 'Система', 'items' => [
        ['slug' => 'settings', 'url' => '/admin/settings.html', 'icon' => 'bi-gear', 'label' => 'Настройки сайта'],
        ['slug' => 'users', 'url' => '/admin/users.html', 'icon' => 'bi-people', 'label' => 'Пользователи'],
        ['slug' => 'roles', 'url' => '/admin/roles.html', 'icon' => 'bi-person-gear', 'label' => 'Роли и права'],
        ['slug' => 'files', 'url' => '/admin/files.html', 'icon' => 'bi-folder2-open', 'label' => 'Файлы'],
    ]],
];
