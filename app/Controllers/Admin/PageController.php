<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\Controller;
use App\Core\Response;
use App\Core\View;
use App\Models\Role;
use App\Models\UserRepository;

final class PageController extends Controller
{
    private $users;
    private $role;

    public function __construct(
        UserRepository $users,
        Role $role
    ) {
        $this->users = $users;
        $this->role = $role;
    }

    public function show($request, $slug)
    {
        $configPath = APP_ROOT . '/config/admin-pages.php';
        $pages = is_file($configPath) ? require $configPath : [];
        if (!isset($pages[$slug])) {
            return \App\Core\ErrorPages::page(404, 'Страница не найдена', 'Такой страницы админ-панели нет или она была перемещена.', '/admin/index.html');
        }

        if (!empty($pages[$slug]['standalone'])) {
            return View::renderStandaloneAdminPage($slug, [
                'title' => $slug === 'login' ? 'Авторизация — Админ-панель' : 'Администрирование',
            ]);
        }

        if (!\admin_is_authenticated()) {
            return Response::redirect('/admin/login.html');
        }

        if (!\admin_can_access($slug)) {
            \App\Core\Logger::write('security', 'Отказ в доступе к разделу админки «' . $slug . '»', \admin_get_user_id());
            return \App\Core\ErrorPages::page(403, 'Доступ запрещён', 'У вашей роли нет прав на этот раздел админ-панели.', '/admin/index.html');
        }

        if (!empty($pages[$slug]['standalone'])) {
            return View::renderStandaloneAdminPage($slug, [
                'title' => $slug === 'login' ? 'Авторизация — Админ-панель' : 'Администрирование',
            ]);
        }

        // Разделы сайдбара: выпадающие группы; активный пункт раскрывает свою группу
        $navigation = require APP_ROOT . '/config/admin-navigation.php';
        $activeSection = [
            'article-edit' => 'blog',
            'portfolio-edit' => 'portfolio',
            'order-edit' => 'orders',
            'user-edit' => 'users',
            'role-edit' => 'roles',
        ][$slug] ?? $slug;
        foreach ($navigation as &$entry) {
            if (($entry['type'] ?? 'link') === 'group') {
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
        $userRoleName = '';
        if (\admin_is_authenticated()) {
            $adminUserId = \admin_get_user_id();
            if ($adminUserId > 0) {
                $adminUser = $this->users->findById($adminUserId);
                $roleRow = $this->role->byKey((string) ($adminUser['role'] ?? ''));
                $userRoleName = (string) ($roleRow['name'] ?? '');
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
            'userRoleName' => $userRoleName,
        ]);
    }

    /**
     * Обновить данные о последнем входе администратора (last_visit/last_ip).
     * Посещения админ-панели в статистику сайта (user_activities) не записываются —
     * статистика ведётся только по публичной части сайта.
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

        try {
            $db = \App\Core\Database::connection();

            // Обновляем last_visit и last_ip (используется в списке пользователей и профиле)
            $stmt = $db->prepare(
                'UPDATE ' . db_table('users') . ' SET last_visit = NOW(), last_ip = :ip WHERE id = :id'
            );
            $stmt->execute([':ip' => self::getIpAddress(), ':id' => $userId]);
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
