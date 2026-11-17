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

        $navigation = [
            ['slug' => 'index', 'url' => '/admin/index.html', 'icon' => 'bi-speedometer2', 'label' => 'Дашборд'],
            ['slug' => 'categories', 'url' => '/admin/categories.html', 'icon' => 'bi-tags', 'label' => 'Рубрики', 'divider' => true],
            ['slug' => 'blog', 'url' => '/admin/blog.html', 'icon' => 'bi-journal-text', 'label' => 'Блог'],
            ['slug' => 'portfolio', 'url' => '/admin/portfolio.html', 'icon' => 'bi-briefcase', 'label' => 'Портфолио'],
            ['slug' => 'messages', 'url' => '/admin/messages.html', 'icon' => 'bi-chat-left-text', 'label' => 'Комментарии'],
            ['slug' => 'consultant', 'url' => '/admin/consultant.html', 'icon' => 'bi-headset', 'label' => 'Консультант', 'divider' => true],
            ['slug' => 'orders', 'url' => '/admin/orders.html', 'icon' => 'bi-cart-check', 'label' => 'Заказы'],
            ['slug' => 'current-orders', 'url' => '/admin/current-orders.html', 'icon' => 'bi-hourglass-split', 'label' => 'Текущие'],
            ['slug' => 'new-orders', 'url' => '/admin/new-orders.html', 'icon' => 'bi-bell', 'label' => 'Новые', 'divider' => true],
            ['slug' => 'calculator', 'url' => '/admin/calculator.html', 'icon' => 'bi-calculator', 'label' => 'Калькулятор'],
            ['slug' => 'files', 'url' => '/admin/files.html', 'icon' => 'bi-folder2-open', 'label' => 'Файлы'],
            ['slug' => 'templates', 'url' => '/admin/templates.html', 'icon' => 'bi-code-slash', 'label' => 'Шаблоны'],
            ['slug' => 'settings', 'url' => '/admin/settings.html', 'icon' => 'bi-gear', 'label' => 'Настройки сайта', 'divider' => true],
            ['slug' => 'landing-pages', 'url' => '/admin/landing-pages.html', 'icon' => 'bi-window-desktop', 'label' => 'Страницы'],
            ['slug' => 'users', 'url' => '/admin/users.html', 'icon' => 'bi-people', 'label' => 'Пользователи'],
        ];
        $activeSection = [
            'article-edit' => 'blog',
            'portfolio-edit' => 'portfolio',
            'order-edit' => 'orders',
            'user-edit' => 'users',
        ][$slug] ?? $slug;
        foreach ($navigation as &$item) {
            $item['active'] = $item['slug'] === $activeSection;
            // Фильтруем навигацию по ролям
            $item['allowed'] = \admin_can_access($item['slug']);
            unset($item['slug']);
        }
        unset($item);

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
