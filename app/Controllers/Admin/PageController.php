<?php

namespace App\Controllers\Admin;

use App\Controllers\Controller;
use App\Core\Response;
use App\Core\View;

class PageController extends Controller
{
    public function show($request, $slug)
    {
        $configPath = APP_ROOT . '/config/admin-pages.php';
        $pages = is_file($configPath) ? require $configPath : [];
        if (!isset($pages[$slug])) {
            return new Response('Not found', 404, ['Content-Type' => 'text/plain; charset=utf-8']);
        }

        if (!empty($pages[$slug]['standalone'])) {
            return View::renderStandaloneAdminPage($slug, [
                'title' => $slug === 'login' ? 'Авторизация — Админ-панель' : 'Администрирование',
            ]);
        }

        if (!\admin_is_authenticated()) {
            return Response::redirect('/admin/login.html');
        }

        // Проверка прав доступа
        if (!\admin_can_access($slug)) {
            return new Response('Доступ запрещён', 403, ['Content-Type' => 'text/plain; charset=utf-8']);
        }

        if (!empty($pages[$slug]['standalone'])) {
            return View::renderStandaloneAdminPage($slug, [
                'title' => $slug === 'login' ? 'Авторизация — Админ-панель' : 'Администрирование',
            ]);
        }

        // Разделы сайдбара: выпадающие группы; активный пункт раскрывает свою группу
        $navigation = [
            ['type' => 'link', 'slug' => 'index', 'url' => '/admin/index.html', 'icon' => 'bi-speedometer2', 'label' => 'Дашборд'],
            ['type' => 'group', 'id' => 'group-content', 'icon' => 'bi-collection', 'label' => 'Контент', 'items' => [
                ['slug' => 'blog', 'url' => '/admin/blog.html', 'icon' => 'bi-journal-text', 'label' => 'Блог'],
                ['slug' => 'categories', 'url' => '/admin/categories.html', 'icon' => 'bi-tags', 'label' => 'Рубрики'],
                ['slug' => 'portfolio', 'url' => '/admin/portfolio.html', 'icon' => 'bi-briefcase', 'label' => 'Портфолио'],
                ['slug' => 'landing-pages', 'url' => '/admin/landing-pages.html', 'icon' => 'bi-window-desktop', 'label' => 'Страницы'],
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
                ['slug' => 'users', 'url' => '/admin/users.html', 'icon' => 'bi-people', 'label' => 'Пользователи'],
            ]],
            ['type' => 'group', 'id' => 'group-system', 'icon' => 'bi-sliders2', 'label' => 'Система', 'items' => [
                ['slug' => 'settings', 'url' => '/admin/settings.html', 'icon' => 'bi-gear', 'label' => 'Настройки сайта'],
                ['slug' => 'custom-fields', 'url' => '/admin/custom-fields.html', 'icon' => 'bi-input-cursor-text', 'label' => 'Пользовательские поля'],
                ['slug' => 'files', 'url' => '/admin/files.html', 'icon' => 'bi-folder2-open', 'label' => 'Файлы'],
            ]],
        ];
        $activeSection = [
            'article-edit' => 'blog',
            'portfolio-edit' => 'portfolio',
            'order-edit' => 'orders',
            'user-edit' => 'users',
        ][$slug] ?? $slug;
        foreach ($navigation as &$entry) {
            if ($entry['type'] === 'group') {
                $groupOpen = false;
                foreach ($entry['items'] as &$item) {
                    $item['active'] = $item['slug'] === $activeSection;
                    $item['allowed'] = \admin_can_access($item['slug']);
                    if ($item['active'] && $item['allowed']) {
                        $groupOpen = true;
                    }
                    unset($item['slug']);
                }
                unset($item);
                $entry['open'] = $groupOpen;
            } else {
                $entry['active'] = $entry['slug'] === $activeSection;
                $entry['allowed'] = \admin_can_access($entry['slug']);
                unset($entry['slug']);
            }
        }
        unset($entry);

        $scripts = [];
        foreach ($pages[$slug]['scripts'] ?? [] as $script) {
            if (strpos($script, '://') !== false || strpos($script, '/') === 0) {
                $scripts[] = $script;
            } elseif (strpos($script, '../') === 0) {
                $scripts[] = '/' . ltrim(substr($script, 3), '/');
            } else {
                $scripts[] = '/admin/' . ltrim($script, '/');
            }
        }

        // Стили страницы (например, темы CodeMirror) — тот же формат, что и scripts
        $styles = [];
        foreach ($pages[$slug]['styles'] ?? [] as $style) {
            if (strpos($style, '://') !== false || strpos($style, '/') === 0) {
                $styles[] = $style;
            } elseif (strpos($style, '../') === 0) {
                $styles[] = '/' . ltrim(substr($style, 3), '/');
            } else {
                $styles[] = '/admin/' . ltrim($style, '/');
            }
        }

        // Логируем посещение админ-страницы
        self::logVisit();

        // Получаем данные текущего пользователя для topbar
        $adminUser = null;
        if (\admin_is_authenticated()) {
            $adminUserId = \admin_get_user_id();
            if ($adminUserId > 0) {
                $adminUser = (new \App\Models\User())->findById($adminUserId);
            }
        }

        return $this->page('admin', $slug, [
            'title' => ($pages[$slug]['title'] ?? 'Администрирование') . ' — Админ-панель',
            'pageTitle' => $pages[$slug]['title'] ?? 'Панель управления',
            'navigation' => $navigation,
            'pageScripts' => $scripts,
            'pageStyles' => $styles,
            'adminUser' => $adminUser,
            'currentUserId' => \admin_get_user_id(),
            'userRole' => $adminUser['role'] ?? '',
        ]);
    }

    /**
     * Записать посещение админ-страницы.
     */
    public static function logVisit(): void
    {
        if (!\admin_is_authenticated()) {
            return;
        }

        $userId = \admin_get_user_id();
        if (!$userId) {
            return;
        }

        $pageUrl = $_SERVER['REQUEST_URI'] ?? '/admin';
        $pageTitle = $_SERVER['QUERY_STRING'] ?? 'Админ-панель';

        try {
            $ipAddress = self::getIpAddress();
            $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';

            $db = \App\Core\Database::connection();

            // Обновляем last_visit и last_ip
            try {
                $stmt = $db->prepare(
                    "UPDATE users SET last_visit = NOW(), last_ip = :ip WHERE id = :id"
                );
                $stmt->execute([':ip' => $ipAddress, ':id' => $userId]);
            } catch (\Exception $e) {
                // Поле может не существовать
            }

            // Записываем активность
            try {
                $stmt = $db->prepare(
                    "INSERT INTO user_activities (user_id, page_url, page_title, ip_address, user_agent, visited_at)
                     VALUES (:user_id, :page_url, :page_title, :ip_address, :user_agent, NOW())"
                );
                $stmt->execute([
                    ':user_id' => $userId,
                    ':page_url' => $pageUrl,
                    ':page_title' => $pageTitle,
                    ':ip_address' => $ipAddress,
                    ':user_agent' => $userAgent,
                ]);
            } catch (\Exception $e) {
                // Таблица может не существовать
            }
        } catch (\Exception $e) {
            // Ничего не делаем при ошибке
        }
    }

    private static function getIpAddress(): string
    {
        $headers = [
            'HTTP_CF_CONNECTING_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_REAL_IP',
        ];

        foreach ($headers as $header) {
            if (!empty($_SERVER[$header])) {
                $ips = explode(',', $_SERVER[$header]);
                $ip = trim($ips[0]);
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }

        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }
}
